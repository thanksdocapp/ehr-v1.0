<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\EmailLog;
use App\Models\User;
use App\Models\SiteSetting;
use App\Events\EmailSent;
use App\Events\EmailFailed;
use App\Traits\ConfiguresSmtp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Exception;

class EmailNotificationService
{
    use ConfiguresSmtp;
    /**
     * Send an email using a template.
     *
     * @param string $templateName
     * @param array $to [email => name]
     * @param array $variables
     * @param array $options
     * @return EmailLog|null
     */
    public function sendTemplateEmail(string $templateName, array $to, array $variables = [], array $options = [])
    {
        try {
            // Find the template - first try active, then try any status
            $template = EmailTemplate::where('name', $templateName)->active()->first();
            
            // If not found as active, try to find any template with this name
            if (!$template) {
                $template = EmailTemplate::where('name', $templateName)->first();
                if ($template && $template->status !== 'active') {
                    Log::warning('Email template found but not active, activating it', [
                        'template_id' => $template->id,
                        'template_name' => $templateName,
                        'current_status' => $template->status
                    ]);
                    $template->update(['status' => 'active']);
                }
            }
            
            if (!$template) {
                Log::error('Email template not found', [
                    'template_name' => $templateName,
                    'recipient' => array_key_first($to)
                ]);
                throw new Exception("Email template '{$templateName}' not found. Please create it in Admin > Email Templates.");
            }
            
            // Create email log entry
            $log = EmailLog::create([
                'email_template_id' => $template->id,
                'recipient_email' => array_key_first($to),
                'recipient_name' => array_values($to)[0] ?? null,
                'subject' => $this->parseContent($template->subject, $variables),
                'body' => $this->parseContent($template->body, $variables),
                'variables' => $variables,
                'cc_emails' => $options['cc'] ?? null,
                'bcc_emails' => $options['bcc'] ?? null,
                'attachments' => $options['attachments'] ?? null,
                'metadata' => $options['metadata'] ?? null,
                'status' => 'pending'
            ]);

            // Send email immediately for shared hosting compatibility
            // (Queue workers often don't work on shared hosting)
            $this->sendImmediateEmail($log);

            return $log;
        } catch (Exception $e) {
            Log::error('Failed to queue email: ' . $e->getMessage(), [
                'template' => $templateName,
                'to' => $to,
                'variables' => $variables,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }


    /**
     * Send an immediate email without queuing.
     *
     * @param EmailLog $log
     * @return bool
     */
    public function sendImmediateEmail(EmailLog $log)
    {
        try {
            // Configure mail settings from database
            $this->configureMailFromDatabase();
            
            // Prepare email data to avoid closure serialization issues
            $to = $log->recipient_email;
            $toName = $log->recipient_name;
            $subject = $log->subject;
            $body = $log->body;
            $ccEmails = $log->cc_emails;
            $bccEmails = $log->bcc_emails;
            $attachments = $log->attachments;
            $fromEmail = $log->template && $log->template->sender_email 
                ? $log->template->sender_email 
                : null;
            $fromName = $log->template && $log->template->sender_name 
                ? $log->template->sender_name 
                : null;
            
            // Build the email - send synchronously to avoid closure serialization
            // Force synchronous sending by temporarily setting queue connection to sync
            $originalQueueConnection = config('queue.default');
            Config::set('queue.default', 'sync');
            
            try {
                // Log email attempt
                Log::info('Attempting to send email', [
                    'log_id' => $log->id,
                    'recipient' => $to,
                    'subject' => $subject,
                    'from_email' => $fromEmail ?? config('mail.from.address'),
                    'smtp_host' => config('mail.mailers.smtp.host'),
                    'smtp_port' => config('mail.mailers.smtp.port')
                ]);
                
                // Use Mail::send() but ensure it's not queued
                // Extract all variables to avoid closure serialization issues
                Mail::send([], [], function ($message) use ($to, $toName, $subject, $body, $ccEmails, $bccEmails, $attachments, $fromEmail, $fromName) {
                    $message->to($to, $toName)
                            ->subject($subject)
                            ->html($body);

                    // Add CC recipients if any
                    if (!empty($ccEmails)) {
                        if (is_array($ccEmails)) {
                            foreach ($ccEmails as $cc) {
                                $message->cc($cc);
                            }
                        } else {
                            $message->cc($ccEmails);
                        }
                    }

                    // Add BCC recipients if any
                    if (!empty($bccEmails)) {
                        if (is_array($bccEmails)) {
                            foreach ($bccEmails as $bcc) {
                                $message->bcc($bcc);
                            }
                        } else {
                            $message->bcc($bccEmails);
                        }
                    }

                    // Add attachments if any
                    if (!empty($attachments) && is_array($attachments)) {
                        foreach ($attachments as $attachment) {
                            if (isset($attachment['path']) && file_exists($attachment['path'])) {
                                $message->attach($attachment['path'], [
                                    'as' => $attachment['name'] ?? basename($attachment['path']),
                                    'mime' => $attachment['type'] ?? null
                                ]);
                            }
                        }
                    }

                    // Set from address if specified in template
                    if ($fromEmail) {
                        $message->from($fromEmail, $fromName);
                    }
                });
                
                // Update log status to sent
                $log->update([
                    'status' => 'sent',
                    'sent_at' => now()
                ]);
                
                Log::info('Email sent successfully (Mail::send completed)', [
                    'log_id' => $log->id,
                    'recipient' => $log->recipient_email,
                    'template' => $log->template?->name,
                    'note' => 'Email marked as sent. Check spam folder if not received. Verify SMTP settings if emails consistently not received.'
                ]);

                // Update template last used timestamp
                if ($log->template) {
                    $log->template->update(['last_used_at' => now()]);
                }
                
                // Dispatch success event
                event(new EmailSent($log));
                
            } catch (\Exception $e) {
                // Other email sending errors
                $errorMessage = $e->getMessage();
                $log->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage
                ]);
                
                Log::error('Failed to send email', [
                    'log_id' => $log->id,
                    'recipient' => $to,
                    'error' => $errorMessage,
                    'trace' => $e->getTraceAsString()
                ]);
                
                throw $e;
            } finally {
                // Restore original queue connection
                Config::set('queue.default', $originalQueueConnection);
            }

            return true;
        } catch (Exception $e) {
            // Check if it's an SMTP/transport error
            $isSmtpError = str_contains($e->getMessage(), 'SMTP') || 
                          str_contains($e->getMessage(), 'Connection') ||
                          str_contains($e->getMessage(), 'stream_socket_client') ||
                          str_contains($e->getMessage(), 'Could not connect');
            
            if ($isSmtpError) {
                // SMTP connection errors
                $errorMessage = 'SMTP connection failed: ' . $e->getMessage();
                Log::error('SMTP connection error', [
                    'log_id' => $log->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $log->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage . '. Please check SMTP settings in Admin > Settings > Email Configuration.'
                ]);

                event(new EmailFailed($log, $e));
                return false;
            }
            
            // Log the error with full details
            $errorMessage = $e->getMessage();
            Log::error('Failed to send email', [
                'log_id' => $log->id,
                'error' => $errorMessage,
                'trace' => $e->getTraceAsString(),
                'recipient' => $log->recipient_email,
                'template' => $log->template?->name
            ]);

            // Update log status with helpful error message
            $log->update([
                'status' => 'failed',
                'error_message' => $errorMessage
            ]);

            // Dispatch failure event
            event(new EmailFailed($log, $e));

            return false;
        }
    }

    /**
     * Parse template content with variables.
     *
     * @param string $content
     * @param array $variables
     * @return string
     */
    protected function parseContent(string $content, array $variables)
    {
        try {
            // Ensure content is a string
            if (!is_string($content)) {
                $content = (string) $content;
            }

            // Convert all variable values to strings safely
            $safeVariables = [];
            foreach ($variables as $key => $value) {
                if (is_array($value)) {
                    $safeVariables[$key] = json_encode($value);
                } elseif (is_object($value)) {
                    $safeVariables[$key] = method_exists($value, '__toString') ? (string) $value : json_encode($value);
                } elseif (is_null($value)) {
                    $safeVariables[$key] = '';
                } else {
                    $safeVariables[$key] = (string) $value;
                }
            }

            // Replace variables in content (handle both {{var}} and {{ var }} formats)
            foreach ($safeVariables as $key => $value) {
                // Escape special regex characters in the key
                $escapedKey = preg_quote($key, '/');
                
                // Replace both formats: {{key}} and {{ key }}
                $patterns = [
                    '/\{\{\s*' . $escapedKey . '\s*\}\}/',
                    '/\{\{' . $escapedKey . '\}\}/',
                ];
                
                foreach ($patterns as $pattern) {
                    $content = preg_replace($pattern, $value, $content);
                }
            }

            // Add any global variables
            $globalVars = [
                'app_name' => config('app.name', 'Hospital'),
                'hospital_name' => config('app.name', 'Hospital'), // Same as app_name for compatibility
                'app_url' => config('app.url', url('/')),
                'current_year' => date('Y'),
                'current_date' => date('F d, Y'),
                'current_time' => date('H:i:s'),
                'site_name' => config('app.name', 'Hospital'), // Alternative variable name
            ];

            foreach ($globalVars as $key => $value) {
                $escapedKey = preg_quote($key, '/');
                $patterns = [
                    '/\{\{\s*' . $escapedKey . '\s*\}\}/',
                    '/\{\{' . $escapedKey . '\}\}/',
                ];
                
                foreach ($patterns as $pattern) {
                    $content = preg_replace($pattern, (string) $value, $content);
                }
            }

            return $content;
        } catch (\Exception $e) {
            Log::error('Failed to parse email template content', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return original content if parsing fails
            return $content;
        }
    }
    

    /**
     * Send a welcome email to a user.
     *
     * @param User $user
     * @return EmailLog|null
     */
    public function sendWelcomeEmail(User $user)
    {
        return $this->sendTemplateEmail(
            'welcome_email',
            [$user->email => $user->name],
            [
                'name' => $user->name,
                'account_number' => $user->account_number,
                'username' => $user->username
            ]
        );
    }

    /**
     * Send a password reset email.
     *
     * @param User $user
     * @param string $token
     * @return EmailLog|null
     */
    public function sendPasswordResetEmail(User $user, string $token)
    {
        $resetUrl = config('app.url') . '/password/reset/' . $token . '?email=' . urlencode($user->email);

        return $this->sendTemplateEmail(
            'password_reset',
            [$user->email => $user->name],
            [
                'name' => $user->name,
                'reset_link' => $resetUrl
            ]
        );
    }

    /**
     * Send a transaction notification email.
     *
     * @param User $user
     * @param array $transaction
     * @return EmailLog|null
     */
    public function sendTransactionAlert(User $user, array $transaction)
    {
        return $this->sendTemplateEmail(
            'transaction_alert',
            [$user->email => $user->name],
            [
                'name' => $user->name,
                'transaction_type' => $transaction['type'],
                'amount' => $transaction['amount'],
                'date' => $transaction['date'],
                'reference' => $transaction['reference']
            ]
        );
    }

    /**
     * Send OTP code email for transfer verification.
     *
     * @param User $user
     * @param string $otpCode
     * @param array $transferDetails
     * @return EmailLog|null
     */
    public function sendOtpCode(User $user, string $otpCode, array $transferDetails)
    {
        return $this->sendTemplateEmail(
            'otp_code',
            [$user->email => $user->name],
            array_merge(
                [
                    'name' => $user->name,
                    'otp_code' => $otpCode
                ],
                $transferDetails
            )
        );
    }

    /**
     * Send IMF code email for international transfer verification.
     *
     * @param User $user
     * @param string $imfCode
     * @param array $transferDetails
     * @return EmailLog|null
     */
    public function sendImfCode(User $user, string $imfCode, array $transferDetails)
    {
        return $this->sendTemplateEmail(
            'imf_code',
            [$user->email => $user->name],
            array_merge(
                [
                    'name' => $user->name,
                    'imf_code' => $imfCode
                ],
                $transferDetails
            )
        );
    }

    /**
     * Send COT code email for transfer commission verification.
     *
     * @param User $user
     * @param string $cotCode
     * @param array $transferDetails
     * @return EmailLog|null
     */
    public function sendCotCode(User $user, string $cotCode, array $transferDetails)
    {
        return $this->sendTemplateEmail(
            'cot_code',
            [$user->email => $user->name],
            array_merge(
                [
                    'name' => $user->name,
                    'cot_code' => $cotCode
                ],
                $transferDetails
            )
        );
    }
}
