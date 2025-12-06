<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsNotificationService
{
    protected ?string $provider;
    protected ?string $apiKey;
    protected ?string $apiSecret;
    protected ?string $senderId;
    protected bool $enabled;

    public function __construct()
    {
        $this->loadSettings();
    }

    /**
     * Load SMS settings from database
     */
    protected function loadSettings(): void
    {
        $settings = SiteSetting::getSettings();

        $this->provider = $settings['sms_provider'] ?? null;
        $this->apiKey = $settings['sms_api_key'] ?? null;
        $this->apiSecret = $settings['sms_api_secret'] ?? null;
        $this->senderId = $settings['sms_sender_id'] ?? config('app.name');
        $this->enabled = (bool) ($settings['sms_enabled'] ?? false);
    }

    /**
     * Check if SMS service is configured and enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled && $this->provider && $this->apiKey;
    }

    /**
     * Send SMS to a phone number
     */
    public function send(string $phoneNumber, string $message): array
    {
        if (!$this->isEnabled()) {
            Log::warning('SMS service is not enabled or configured');
            return [
                'success' => false,
                'message' => 'SMS service is not enabled or configured'
            ];
        }

        // Normalize phone number
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);

        if (!$phoneNumber) {
            return [
                'success' => false,
                'message' => 'Invalid phone number'
            ];
        }

        try {
            $result = match ($this->provider) {
                'twilio' => $this->sendViaTwilio($phoneNumber, $message),
                'nexmo', 'vonage' => $this->sendViaVonage($phoneNumber, $message),
                'africas_talking' => $this->sendViaAfricasTalking($phoneNumber, $message),
                'termii' => $this->sendViaTermii($phoneNumber, $message),
                'custom' => $this->sendViaCustomProvider($phoneNumber, $message),
                default => throw new \Exception("Unsupported SMS provider: {$this->provider}")
            };

            // Log the SMS attempt
            $this->logSmsAttempt($phoneNumber, $message, $result);

            return $result;
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS to a patient
     */
    public function sendToPatient(Patient $patient, string $message): array
    {
        $phone = $patient->phone ?? $patient->mobile;

        if (!$phone) {
            return [
                'success' => false,
                'message' => 'Patient does not have a phone number'
            ];
        }

        return $this->send($phone, $message);
    }

    /**
     * Send SMS to a user (staff/doctor)
     */
    public function sendToUser(User $user, string $message): array
    {
        $phone = $user->phone ?? $user->mobile;

        if (!$phone) {
            return [
                'success' => false,
                'message' => 'User does not have a phone number'
            ];
        }

        return $this->send($phone, $message);
    }

    /**
     * Send bulk SMS to multiple recipients
     */
    public function sendBulk(array $phoneNumbers, string $message): array
    {
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($phoneNumbers as $phone) {
            $result = $this->send($phone, $message);
            $results[$phone] = $result;

            if ($result['success']) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }

        return [
            'success' => $failureCount === 0,
            'total' => count($phoneNumbers),
            'sent' => $successCount,
            'failed' => $failureCount,
            'details' => $results
        ];
    }

    /**
     * Send appointment reminder SMS
     */
    public function sendAppointmentReminder(Patient $patient, $appointment): array
    {
        $message = "Reminder: You have an appointment on " .
                   $appointment->appointment_date->format('M d, Y') . " at " .
                   $appointment->appointment_time->format('h:i A') . ". " .
                   "Please arrive 15 minutes early. - " . config('app.name');

        return $this->sendToPatient($patient, $message);
    }

    /**
     * Send appointment confirmation SMS
     */
    public function sendAppointmentConfirmation(Patient $patient, $appointment): array
    {
        $message = "Your appointment has been confirmed for " .
                   $appointment->appointment_date->format('M d, Y') . " at " .
                   $appointment->appointment_time->format('h:i A') . ". " .
                   "Thank you for choosing " . config('app.name');

        return $this->sendToPatient($patient, $message);
    }

    /**
     * Send lab result ready SMS
     */
    public function sendLabResultReady(Patient $patient, $labReport): array
    {
        $message = "Your lab results are ready. Please log in to your patient portal or visit the clinic to collect your results. - " . config('app.name');

        return $this->sendToPatient($patient, $message);
    }

    /**
     * Send prescription ready SMS
     */
    public function sendPrescriptionReady(Patient $patient): array
    {
        $message = "Your prescription is ready for pickup. Please visit the pharmacy with your ID. - " . config('app.name');

        return $this->sendToPatient($patient, $message);
    }

    /**
     * Send payment reminder SMS
     */
    public function sendPaymentReminder(Patient $patient, $billing): array
    {
        $amount = number_format($billing->amount, 2);
        $message = "Payment reminder: You have an outstanding balance of {$billing->currency} {$amount}. " .
                   "Please make payment at your earliest convenience. - " . config('app.name');

        return $this->sendToPatient($patient, $message);
    }

    /**
     * Normalize phone number to international format
     */
    protected function normalizePhoneNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If number starts with 0, assume local number and add country code
        if (str_starts_with($phone, '0')) {
            $settings = SiteSetting::getSettings();
            $countryCode = $settings['default_country_code'] ?? '234'; // Default to Nigeria
            $phone = '+' . $countryCode . substr($phone, 1);
        }

        // Add + if missing
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        // Validate minimum length (minimum 10 digits including country code)
        if (strlen(preg_replace('/[^0-9]/', '', $phone)) < 10) {
            return null;
        }

        return $phone;
    }

    /**
     * Send via Twilio
     */
    protected function sendViaTwilio(string $phone, string $message): array
    {
        $accountSid = $this->apiKey;
        $authToken = $this->apiSecret;

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => $this->senderId,
                'To' => $phone,
                'Body' => $message
            ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message_id' => $response->json('sid'),
                'message' => 'SMS sent successfully'
            ];
        }

        throw new \Exception($response->json('message') ?? 'Twilio API error');
    }

    /**
     * Send via Vonage (Nexmo)
     */
    protected function sendViaVonage(string $phone, string $message): array
    {
        $response = Http::post('https://rest.nexmo.com/sms/json', [
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'from' => $this->senderId,
            'to' => ltrim($phone, '+'),
            'text' => $message
        ]);

        $data = $response->json();

        if (isset($data['messages'][0]['status']) && $data['messages'][0]['status'] === '0') {
            return [
                'success' => true,
                'message_id' => $data['messages'][0]['message-id'] ?? null,
                'message' => 'SMS sent successfully'
            ];
        }

        throw new \Exception($data['messages'][0]['error-text'] ?? 'Vonage API error');
    }

    /**
     * Send via Africa's Talking
     */
    protected function sendViaAfricasTalking(string $phone, string $message): array
    {
        $settings = SiteSetting::getSettings();
        $username = $settings['sms_username'] ?? 'sandbox';

        $response = Http::withHeaders([
            'apiKey' => $this->apiKey,
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json'
        ])->asForm()->post('https://api.africastalking.com/version1/messaging', [
            'username' => $username,
            'to' => $phone,
            'message' => $message,
            'from' => $this->senderId
        ]);

        $data = $response->json();

        if (isset($data['SMSMessageData']['Recipients'][0]['status']) &&
            $data['SMSMessageData']['Recipients'][0]['status'] === 'Success') {
            return [
                'success' => true,
                'message_id' => $data['SMSMessageData']['Recipients'][0]['messageId'] ?? null,
                'message' => 'SMS sent successfully'
            ];
        }

        throw new \Exception($data['SMSMessageData']['Message'] ?? 'Africa\'s Talking API error');
    }

    /**
     * Send via Termii (Nigerian provider)
     */
    protected function sendViaTermii(string $phone, string $message): array
    {
        $response = Http::post('https://api.ng.termii.com/api/sms/send', [
            'api_key' => $this->apiKey,
            'to' => ltrim($phone, '+'),
            'from' => $this->senderId,
            'sms' => $message,
            'type' => 'plain',
            'channel' => 'generic'
        ]);

        $data = $response->json();

        if (isset($data['code']) && $data['code'] === 'ok') {
            return [
                'success' => true,
                'message_id' => $data['message_id'] ?? null,
                'message' => 'SMS sent successfully'
            ];
        }

        throw new \Exception($data['message'] ?? 'Termii API error');
    }

    /**
     * Send via custom webhook/API
     */
    protected function sendViaCustomProvider(string $phone, string $message): array
    {
        $settings = SiteSetting::getSettings();
        $webhookUrl = $settings['sms_webhook_url'] ?? null;

        if (!$webhookUrl) {
            throw new \Exception('Custom SMS webhook URL not configured');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($webhookUrl, [
            'phone' => $phone,
            'message' => $message,
            'sender_id' => $this->senderId
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message_id' => $response->json('message_id'),
                'message' => 'SMS sent successfully'
            ];
        }

        throw new \Exception($response->json('error') ?? 'Custom webhook error');
    }

    /**
     * Log SMS attempt
     */
    protected function logSmsAttempt(string $phone, string $message, array $result): void
    {
        Log::channel('sms')->info('SMS attempt', [
            'phone' => $phone,
            'message_length' => strlen($message),
            'provider' => $this->provider,
            'success' => $result['success'],
            'message_id' => $result['message_id'] ?? null,
            'error' => $result['success'] ? null : ($result['message'] ?? 'Unknown error')
        ]);
    }

    /**
     * Get available SMS providers
     */
    public static function getProviders(): array
    {
        return [
            'twilio' => 'Twilio',
            'vonage' => 'Vonage (Nexmo)',
            'africas_talking' => 'Africa\'s Talking',
            'termii' => 'Termii',
            'custom' => 'Custom Webhook'
        ];
    }

    /**
     * Test SMS configuration by sending a test message
     */
    public function testConfiguration(string $testPhone): array
    {
        $testMessage = "This is a test message from " . config('app.name') . ". SMS configuration is working correctly!";

        return $this->send($testPhone, $testMessage);
    }
}
