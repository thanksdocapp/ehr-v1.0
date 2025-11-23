<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\EmailLog;
use App\Models\User;
use App\Models\SiteSetting;
use App\Events\EmailSent;
use App\Events\EmailFailed;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Exception;

class EmailNotificationService
{
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
     * Configure mail settings from database.
     *
     * @return void
     */
    protected function configureMailFromDatabase()
    {
        try {
            // Get mail settings from database
            $settings = SiteSetting::getSettings();
            
            if (isset($settings['smtp_host']) && $settings['smtp_host']) {
                // Set default mailer to smtp
                Config::set('mail.default', 'smtp');
                
                // Configure SMTP settings
                Config::set('mail.mailers.smtp.host', $settings['smtp_host']);
                Config::set('mail.mailers.smtp.port', $settings['smtp_port'] ?? 587);
                Config::set('mail.mailers.smtp.username', $settings['smtp_username'] ?? '');
                Config::set('mail.mailers.smtp.password', $settings['smtp_password'] ?? '');
                
                // Handle encryption - 'none' means no encryption
                $encryption = $settings['smtp_encryption'] ?? 'tls';
                if ($encryption === 'none') {
                    Config::set('mail.mailers.smtp.encryption', null);
                } else {
                    Config::set('mail.mailers.smtp.encryption', $encryption);
                }
                
                // Set from email address
                if (isset($settings['from_email']) && $settings['from_email']) {
                    Config::set('mail.from.address', $settings['from_email']);
                    Config::set('mail.from.name', $settings['from_name'] ?? $settings['hospital_name'] ?? config('app.name'));
                }
                
                Log::info('Mail configuration updated from database', [
                    'host' => $settings['smtp_host'],
                    'port' => $settings['smtp_port'] ?? 587,
                    'encryption' => $encryption,
                    'username' => $settings['smtp_username'] ?? '',
                    'from_email' => $settings['from_email'] ?? ''
                ]);
            } else {
                Log::warning('SMTP host not configured in database settings');
            }
        } catch (Exception $e) {
            Log::error('Failed to configure mail from database', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
                
                Log::info('Email sent successfully', [
                    'log_id' => $log->id,
                    'recipient' => $log->recipient_email,
                    'template' => $log->template?->name
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
        // Replace variables in content
        foreach ($variables as $key => $value) {
            $content = str_replace(['{{'.$key.'}}', '{{ '.$key.' }}'], $value, $content);
        }

        // Add any global variables
        $globalVars = [
            'app_name' => config('app.name'),
            'hospital_name' => config('app.name'), // Same as app_name for compatibility
            'app_url' => config('app.url'),
            'current_year' => date('Y'),
            'site_name' => config('app.name'), // Alternative variable name
        ];

        foreach ($globalVars as $key => $value) {
            $content = str_replace(['{{'.$key.'}}', '{{ '.$key.' }}'], $value, $content);
        }

        return $content;
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
