<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\SiteSetting;
use App\Traits\ConfiguresSmtp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class EmailService
{
    use ConfiguresSmtp;
    /**
     * Send email using template
     */
    public function sendTemplate($templateName, $recipient, $data = [])
    {
        try {
            // Get the template
            $template = EmailTemplate::where('name', $templateName)
                ->where('status', 'active')
                ->first();

            if (!$template) {
                throw new \Exception("Email template '{$templateName}' not found or inactive");
            }

            // Get hospital settings for default variables
            $hospitalSettings = $this->getHospitalSettings();
            
            // Merge hospital settings with provided data
            $mergedData = array_merge($hospitalSettings, $data);

            // Parse template content
            $subject = $this->parseContent($template->subject, $mergedData);
            $body = $this->parseContent($template->body, $mergedData);

            // Prepare email data
            $emailData = [
                'subject' => $subject,
                'body' => $body,
                'sender_name' => $template->sender_name ?: $hospitalSettings['hospital_name'],
                'sender_email' => $template->sender_email ?: $hospitalSettings['hospital_email'],
                'template_name' => $templateName
            ];

            // Configure SMTP settings from database before sending
            $this->configureMailFromDatabase();
            
            // Force synchronous sending
            $originalQueueConnection = config('queue.default');
            Config::set('queue.default', 'sync');

            try {
                // Send email
                Mail::send([], [], function ($message) use ($emailData, $recipient) {
                $message->to($recipient['email'], $recipient['name'] ?? '')
                    ->subject($emailData['subject'])
                    ->from($emailData['sender_email'], $emailData['sender_name'])
                    ->html($this->formatEmailBody($emailData['body']));
                });

                // Mark template as used
                $template->markAsUsed();

                Log::info("Email sent successfully using template: {$templateName}", [
                    'recipient' => $recipient['email'],
                    'subject' => $subject
                ]);

                return true;
            } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
                // SMTP connection error
                Log::error("SMTP connection error when sending email: {$templateName}", [
                    'recipient' => $recipient['email'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new \Exception('SMTP connection failed: ' . $e->getMessage() . '. Please check SMTP settings in Admin > Settings > Email Configuration.');
            } catch (\Exception $e) {
                Log::error("Failed to send email using template: {$templateName}", [
                    'recipient' => $recipient['email'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            } finally {
                // Restore original queue connection
                Config::set('queue.default', $originalQueueConnection);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send email using template: {$templateName}", [
                'recipient' => $recipient['email'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Parse content and replace variables
     */
    private function parseContent($content, $data)
    {
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
            $content = str_replace('{{ ' . $key . ' }}', $value, $content);
        }
        
        return $content;
    }

    /**
     * Format email body for HTML display
     */
    private function formatEmailBody($body)
    {
        // Convert line breaks to HTML
        $body = nl2br($body);
        
        // Basic HTML wrapper
        return '
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .email-container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #667eea; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="content">
                    ' . $body . '
                </div>
                <div class="footer">
                    <p>This is an automated message from ' . config('app.name') . '</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Get hospital settings for template variables
     */
    private function getHospitalSettings()
    {
        $settings = SiteSetting::whereIn('key', [
            'hospital_name',
            'hospital_email',
            'hospital_phone',
            'hospital_address'
        ])->pluck('value', 'key')->toArray();

        return [
            'hospital_name' => $settings['hospital_name'] ?? 'ThankDoc EHR',
            'hospital_email' => $settings['hospital_email'] ?? 'info@newwaveshospital.com',
            'hospital_phone' => $settings['hospital_phone'] ?? '+233 123 456 789',
            'hospital_address' => $settings['hospital_address'] ?? '123 Healthcare Avenue, Accra, Ghana',
            'emergency_phone' => $settings['hospital_phone'] ?? '+233 123 456 789',
            'portal_url' => url('/patient-portal'),
            'site_url' => url('/'),
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s')
        ];
    }

    /**
     * Get available templates
     */
    public function getAvailableTemplates()
    {
        return EmailTemplate::where('status', 'active')
            ->select('name', 'subject', 'description', 'category')
            ->get();
    }

    /**
     * Preview template with sample data
     */
    public function previewTemplate($templateName, $sampleData = [])
    {
        $template = EmailTemplate::where('name', $templateName)->first();
        
        if (!$template) {
            throw new \Exception("Template not found");
        }

        // Get hospital settings
        $hospitalSettings = $this->getHospitalSettings();
        
        // Merge with sample data
        $mergedData = array_merge($hospitalSettings, $sampleData);

        return [
            'subject' => $this->parseContent($template->subject, $mergedData),
            'body' => $this->parseContent($template->body, $mergedData),
            'formatted_body' => $this->formatEmailBody($this->parseContent($template->body, $mergedData))
        ];
    }
}
