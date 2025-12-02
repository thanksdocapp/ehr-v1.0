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
            // Find the template - first try active (including soft-deleted), then try any status
            $template = EmailTemplate::withTrashed()
                ->where('name', $templateName)
                ->where('status', 'active')
                ->first();
            
            // If not found as active, try to find any template with this name (including soft-deleted)
            if (!$template) {
                $template = EmailTemplate::withTrashed()
                    ->where('name', $templateName)
                    ->first();
                    
                if ($template) {
                    if ($template->trashed()) {
                        Log::warning('Email template found but is soft-deleted, restoring it', [
                            'template_id' => $template->id,
                            'template_name' => $templateName,
                            'current_status' => $template->status
                        ]);
                        $template->restore();
                    }
                    
                    if ($template->status !== 'active') {
                        Log::warning('Email template found but not active, activating it', [
                            'template_id' => $template->id,
                            'template_name' => $templateName,
                            'current_status' => $template->status
                        ]);
                        $template->update(['status' => 'active']);
                    }
                }
            }
            
            if (!$template) {
                Log::error('Email template not found', [
                    'template_name' => $templateName,
                    'recipient' => array_key_first($to),
                    'all_templates_with_name' => EmailTemplate::withTrashed()
                        ->where('name', $templateName)
                        ->get(['id', 'name', 'status', 'deleted_at'])
                        ->toArray()
                ]);
                throw new Exception("Email template '{$templateName}' not found. Please create it in Admin > Email Templates.");
            }
            
            // Validate template has required fields
            if (empty($template->subject)) {
                throw new Exception("Email template '{$templateName}' has an empty subject. Please update the template.");
            }
            
            if (empty($template->body)) {
                throw new Exception("Email template '{$templateName}' has an empty body. Please update the template.");
            }

            // Parse subject and body with error handling
            try {
                Log::info('Parsing email template content', [
                    'template_id' => $template->id,
                    'template_name' => $templateName,
                    'variables_count' => count($variables),
                    'variables_keys' => array_keys($variables),
                    'template_subject_preview' => substr($template->subject, 0, 100),
                    'template_body_preview' => substr(strip_tags($template->body), 0, 100)
                ]);
                
                $parsedSubject = $this->parseContent($template->subject, $variables);
                $parsedBody = $this->parseContent($template->body, $variables);
                
                Log::info('Email template parsed successfully', [
                    'template_id' => $template->id,
                    'subject_length' => strlen($parsedSubject),
                    'body_length' => strlen($parsedBody),
                    'parsed_subject_preview' => substr($parsedSubject, 0, 100),
                    'parsed_body_preview' => substr(strip_tags($parsedBody), 0, 100)
                ]);
                
                // Validate parsed content is not empty
                if (empty(trim($parsedSubject))) {
                    Log::warning('Parsed email subject is empty', [
                        'template_id' => $template->id,
                        'template_name' => $templateName,
                        'original_subject' => $template->subject
                    ]);
                }
                
                if (empty(trim(strip_tags($parsedBody)))) {
                    Log::warning('Parsed email body is empty', [
                        'template_id' => $template->id,
                        'template_name' => $templateName
                    ]);
                }
            } catch (\Exception $parseException) {
                Log::error('Failed to parse email template', [
                    'template_id' => $template->id,
                    'template_name' => $templateName,
                    'error' => $parseException->getMessage(),
                    'trace' => $parseException->getTraceAsString()
                ]);
                throw new Exception("Failed to parse email template '{$templateName}': " . $parseException->getMessage());
            }

            // Create email log entry
            try {
                Log::info('Creating email log entry', [
                    'template_id' => $template->id,
                    'recipient' => array_key_first($to),
                    'email_type' => $options['email_type'] ?? 'general',
                    'subject_length' => strlen($parsedSubject),
                    'body_length' => strlen($parsedBody)
                ]);
                
                // Check column existence safely (cache results to avoid multiple queries)
                $columnChecks = [];
                $columnsToCheck = ['email_type', 'event', 'patient_id', 'billing_id', 'invoice_id', 'payment_id'];
                
                try {
                    foreach ($columnsToCheck as $column) {
                        $columnChecks[$column] = \Illuminate\Support\Facades\Schema::hasColumn('email_logs', $column);
                    }
                } catch (\Exception $schemaException) {
                    // If schema check fails, assume columns don't exist (safer fallback)
                    Log::warning('Schema check failed, assuming optional columns do not exist', [
                        'error' => $schemaException->getMessage()
                    ]);
                    foreach ($columnsToCheck as $column) {
                        $columnChecks[$column] = false;
                    }
                }
                
                // Prepare log data
                $logData = [
                    'email_template_id' => $template->id,
                    'recipient_email' => array_key_first($to),
                    'recipient_name' => array_values($to)[0] ?? null,
                    'subject' => $parsedSubject,
                    'body' => $parsedBody,
                    'variables' => $variables,
                    'cc_emails' => $options['cc'] ?? null,
                    'bcc_emails' => $options['bcc'] ?? null,
                    'attachments' => $options['attachments'] ?? null,
                    'metadata' => $options['metadata'] ?? null,
                    'status' => 'pending'
                ];
                
                // Only add optional columns if they exist in the database
                if ($columnChecks['email_type']) {
                    $logData['email_type'] = $options['email_type'] ?? 'general';
                }
                if ($columnChecks['event']) {
                    $logData['event'] = $options['event'] ?? null;
                }
                if ($columnChecks['patient_id']) {
                    $logData['patient_id'] = $options['patient_id'] ?? null;
                }
                if ($columnChecks['billing_id']) {
                    $logData['billing_id'] = $options['billing_id'] ?? null;
                }
                if ($columnChecks['invoice_id']) {
                    $logData['invoice_id'] = $options['invoice_id'] ?? null;
                }
                if ($columnChecks['payment_id']) {
                    $logData['payment_id'] = $options['payment_id'] ?? null;
                }
                
                // Log the data being used (without sensitive info)
                Log::info('Email log data prepared', [
                    'template_id' => $logData['email_template_id'],
                    'recipient' => $logData['recipient_email'],
                    'has_subject' => !empty($logData['subject']),
                    'has_body' => !empty($logData['body']),
                    'status' => $logData['status'],
                    'has_email_type_column' => $columnChecks['email_type'] ?? false
                ]);
                
                $log = EmailLog::create($logData);
                
                Log::info('Email log created successfully', [
                    'log_id' => $log->id,
                    'template_id' => $template->id,
                    'status' => $log->status
                ]);
            } catch (\Exception $createException) {
                Log::error('Failed to create email log - DETAILED ERROR', [
                    'template_id' => $template->id ?? null,
                    'template_name' => $templateName,
                    'recipient' => array_key_first($to),
                    'error' => $createException->getMessage(),
                    'error_class' => get_class($createException),
                    'trace' => $createException->getTraceAsString(),
                    'file' => $createException->getFile(),
                    'line' => $createException->getLine(),
                    'error_info' => $createException instanceof \Illuminate\Database\QueryException ? $createException->errorInfo : null,
                    'sql_state' => $createException instanceof \Illuminate\Database\QueryException ? ($createException->errorInfo[0] ?? null) : null,
                    'error_code' => $createException instanceof \Illuminate\Database\QueryException ? ($createException->errorInfo[1] ?? null) : null,
                    'error_message_db' => $createException instanceof \Illuminate\Database\QueryException ? ($createException->errorInfo[2] ?? null) : null,
                ]);
                throw new Exception("Failed to create email log: " . $createException->getMessage());
            }

            // Send email immediately for shared hosting compatibility
            // (Queue workers often don't work on shared hosting)
            try {
                Log::info('Sending email immediately', ['log_id' => $log->id]);
                $this->sendImmediateEmail($log);
                Log::info('Email sent successfully', ['log_id' => $log->id]);
            } catch (\Exception $sendException) {
                // Don't fail the whole operation if sending fails - log is already created
                Log::error('Failed to send email immediately', [
                    'log_id' => $log->id,
                    'error' => $sendException->getMessage(),
                    'trace' => $sendException->getTraceAsString()
                ]);
                // Update log status to failed
                $log->update([
                    'status' => 'failed',
                    'error_message' => $sendException->getMessage()
                ]);
            }

            return $log;
        } catch (\Exception $e) {
            // Log the full exception details with maximum detail
            Log::error('CRITICAL: Failed to queue email - Exception caught in sendTemplateEmail', [
                'template' => $templateName,
                'to' => $to,
                'variables_keys' => array_keys($variables),
                'variables_count' => count($variables),
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'previous' => $e->getPrevious() ? [
                    'message' => $e->getPrevious()->getMessage(),
                    'class' => get_class($e->getPrevious()),
                    'file' => $e->getPrevious()->getFile(),
                    'line' => $e->getPrevious()->getLine()
                ] : null,
                'error_info' => $e instanceof \Illuminate\Database\QueryException ? $e->errorInfo : null,
                'sql_state' => $e instanceof \Illuminate\Database\QueryException ? ($e->errorInfo[0] ?? null) : null,
                'error_code' => $e instanceof \Illuminate\Database\QueryException ? ($e->errorInfo[1] ?? null) : null,
                'error_message_db' => $e instanceof \Illuminate\Database\QueryException ? ($e->errorInfo[2] ?? null) : null,
            ]);

            // Try to create a minimal email log entry for tracking purposes
            try {
                $template = EmailTemplate::withTrashed()->where('name', $templateName)->first();
                if ($template) {
                    // Check if email_type column exists (safely)
                    $hasEmailTypeColumn = false;
                    try {
                        $hasEmailTypeColumn = \Illuminate\Support\Facades\Schema::hasColumn('email_logs', 'email_type');
                    } catch (\Exception $schemaEx) {
                        // If check fails, assume column doesn't exist
                        Log::warning('Could not check for email_type column', ['error' => $schemaEx->getMessage()]);
                    }
                    
                    $errorLogData = [
                        'email_template_id' => $template->id,
                        'recipient_email' => array_key_first($to),
                        'recipient_name' => array_values($to)[0] ?? null,
                        'subject' => 'Email Send Failed: ' . $templateName,
                        'body' => 'Email sending failed: ' . $e->getMessage(),
                        'status' => 'failed',
                        'error_message' => $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine(),
                    ];
                    
                    // Only add email_type if column exists
                    if ($hasEmailTypeColumn) {
                        $errorLogData['email_type'] = $options['email_type'] ?? 'general';
                    }
                    
                    $errorLog = EmailLog::create($errorLogData);
                    
                    Log::info('Created error email log entry', [
                        'error_log_id' => $errorLog->id,
                        'original_error' => $e->getMessage(),
                        'original_error_class' => get_class($e)
                    ]);
                    
                    return $errorLog;
                } else {
                    Log::error('Cannot create error log - template not found', [
                        'template_name' => $templateName
                    ]);
                }
            } catch (\Exception $logException) {
                Log::error('Failed to create error email log entry', [
                    'original_error' => $e->getMessage(),
                    'log_error' => $logException->getMessage(),
                    'log_error_class' => get_class($logException),
                    'log_trace' => $logException->getTraceAsString()
                ]);
            }

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
        // Add default variables if not provided
        if (!isset($variables['hospital_name'])) {
            try {
                $hospitalSetting = \App\Models\SiteSetting::where('key', 'hospital_name')->first();
                $variables['hospital_name'] = $hospitalSetting && $hospitalSetting->value 
                    ? $hospitalSetting->value 
                    : config('app.name', 'Hospital');
            } catch (\Exception $e) {
                $variables['hospital_name'] = config('app.name', 'Hospital');
            }
        }
        
        // Add other common defaults
        if (!isset($variables['site_url'])) {
            $variables['site_url'] = config('app.url', url('/'));
        }
        
        if (!isset($variables['date'])) {
            $variables['date'] = now()->format('Y-m-d');
        }
        
        if (!isset($variables['time'])) {
            $variables['time'] = now()->format('H:i:s');
        }
        
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
