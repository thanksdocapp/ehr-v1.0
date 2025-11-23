<?php

namespace App\Services;

use App\Models\SmsTemplate;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send SMS using template
     */
    public function sendTemplate($templateName, $recipient, $data = [])
    {
        try {
            // Get the template
            $template = SmsTemplate::where('name', $templateName)
                ->where('status', 'active')
                ->first();

            if (!$template) {
                throw new \Exception("SMS template '{$templateName}' not found or inactive");
            }

            // Get hospital settings for default variables
            $hospitalSettings = $this->getHospitalSettings();
            
            // Merge hospital settings with provided data
            $mergedData = array_merge($hospitalSettings, $data);

            // Parse template content
            $message = $this->parseContent($template->message, $mergedData);

            // Prepare SMS data
            $smsData = [
                'message' => $message,
                'sender_id' => $template->sender_id ?: 'HOSPITAL',
                'template_name' => $templateName,
                'recipient' => $recipient
            ];

            // Send SMS (This is where you'd integrate with your SMS provider)
            $result = $this->sendSms($smsData);

            if ($result) {
                // Mark template as used
                $template->markAsUsed();

                Log::info("SMS sent successfully using template: {$templateName}", [
                    'recipient' => $recipient,
                    'message_length' => strlen($message)
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error("Failed to send SMS using template: {$templateName}", [
                'recipient' => $recipient,
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
     * Send SMS through provider (placeholder - integrate with your SMS provider)
     */
    private function sendSms($smsData)
    {
        // This is a placeholder - you would integrate with your SMS provider here
        // Examples: Twilio, Nexmo, TextLocal, etc.
        
        // For demonstration, we'll just log the SMS
        Log::info("SMS would be sent", [
            'recipient' => $smsData['recipient'],
            'message' => $smsData['message'],
            'sender_id' => $smsData['sender_id']
        ]);

        // Return true for successful sending (replace with actual SMS provider response)
        return true;
    }

    /**
     * Get hospital settings for template variables
     */
    private function getHospitalSettings()
    {
        $settings = SiteSetting::whereIn('key', [
            'hospital_name',
            'hospital_phone',
            'hospital_address'
        ])->pluck('value', 'key')->toArray();

        return [
            'hospital_name' => $settings['hospital_name'] ?? 'ThankDoc EHR',
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
        return SmsTemplate::where('status', 'active')
            ->select('name', 'message', 'description', 'category')
            ->get();
    }

    /**
     * Preview template with sample data
     */
    public function previewTemplate($templateName, $sampleData = [])
    {
        $template = SmsTemplate::where('name', $templateName)->first();
        
        if (!$template) {
            throw new \Exception("Template not found");
        }

        // Get hospital settings
        $hospitalSettings = $this->getHospitalSettings();
        
        // Merge with sample data
        $mergedData = array_merge($hospitalSettings, $sampleData);

        $message = $this->parseContent($template->message, $mergedData);

        return [
            'message' => $message,
            'character_count' => strlen($message),
            'sms_count' => $this->calculateSmsCount($message),
            'sender_id' => $template->sender_id ?: 'HOSPITAL'
        ];
    }

    /**
     * Calculate SMS count based on message length
     */
    private function calculateSmsCount($message)
    {
        $length = strlen($message);
        
        if ($length <= 160) {
            return 1;
        } elseif ($length <= 306) {
            return 2;
        } elseif ($length <= 459) {
            return 3;
        } else {
            return ceil($length / 153);
        }
    }

    /**
     * Validate phone number format
     */
    public function validatePhoneNumber($phone)
    {
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // Check if it's a valid Ghana phone number format
        if (preg_match('/^233[0-9]{9}$/', $phone)) {
            return $phone;
        }
        
        // If it starts with 0, replace with 233
        if (preg_match('/^0[0-9]{9}$/', $phone)) {
            return '233' . substr($phone, 1);
        }
        
        return false;
    }

    /**
     * Get SMS delivery status (placeholder)
     */
    public function getDeliveryStatus($messageId)
    {
        // This would integrate with your SMS provider's status API
        return [
            'message_id' => $messageId,
            'status' => 'delivered',
            'delivered_at' => now()
        ];
    }
}
