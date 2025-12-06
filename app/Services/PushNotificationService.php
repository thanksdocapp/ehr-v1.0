<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\PatientNotification;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected ?string $provider;
    protected ?string $apiKey;
    protected ?string $appId;
    protected bool $enabled;

    public function __construct()
    {
        $this->loadSettings();
    }

    /**
     * Load Push notification settings from database
     */
    protected function loadSettings(): void
    {
        $settings = SiteSetting::getSettings();

        $this->provider = $settings['push_provider'] ?? 'onesignal';
        $this->apiKey = $settings['push_api_key'] ?? null;
        $this->appId = $settings['push_app_id'] ?? null;
        $this->enabled = (bool) ($settings['push_enabled'] ?? false);
    }

    /**
     * Check if Push service is configured and enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled && $this->provider && $this->apiKey && $this->appId;
    }

    /**
     * Send push notification to a user
     */
    public function sendToUser(User $user, string $title, string $message, array $data = []): array
    {
        if (!$this->isEnabled()) {
            Log::warning('Push notification service is not enabled or configured');
            return [
                'success' => false,
                'message' => 'Push notification service is not enabled or configured'
            ];
        }

        // Get user's push token(s)
        $pushTokens = $this->getUserPushTokens($user);

        if (empty($pushTokens)) {
            return [
                'success' => false,
                'message' => 'User does not have any registered push tokens'
            ];
        }

        return $this->sendToTokens($pushTokens, $title, $message, $data);
    }

    /**
     * Send push notification to a patient
     */
    public function sendToPatient(Patient $patient, string $title, string $message, array $data = []): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Push notification service is not enabled or configured'
            ];
        }

        // Get patient's push token(s)
        $pushTokens = $this->getPatientPushTokens($patient);

        if (empty($pushTokens)) {
            return [
                'success' => false,
                'message' => 'Patient does not have any registered push tokens'
            ];
        }

        return $this->sendToTokens($pushTokens, $title, $message, $data);
    }

    /**
     * Send push notification to multiple tokens
     */
    public function sendToTokens(array $tokens, string $title, string $message, array $data = []): array
    {
        try {
            $result = match ($this->provider) {
                'onesignal' => $this->sendViaOneSignal($tokens, $title, $message, $data),
                'firebase', 'fcm' => $this->sendViaFirebase($tokens, $title, $message, $data),
                'pusher' => $this->sendViaPusher($tokens, $title, $message, $data),
                default => throw new \Exception("Unsupported push provider: {$this->provider}")
            };

            $this->logPushAttempt($tokens, $title, $result);

            return $result;
        } catch (\Exception $e) {
            Log::error('Push notification failed', [
                'tokens_count' => count($tokens),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Send to all users with a specific role
     */
    public function sendToRole(string $role, string $title, string $message, array $data = []): array
    {
        $users = User::where('role', $role)->get();
        $results = [];
        $successCount = 0;

        foreach ($users as $user) {
            $result = $this->sendToUser($user, $title, $message, $data);
            $results[$user->id] = $result;
            if ($result['success']) {
                $successCount++;
            }
        }

        return [
            'success' => $successCount > 0,
            'total' => $users->count(),
            'sent' => $successCount,
            'details' => $results
        ];
    }

    /**
     * Send broadcast to all users
     */
    public function broadcast(string $title, string $message, array $data = []): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Push notification service is not enabled or configured'
            ];
        }

        try {
            return match ($this->provider) {
                'onesignal' => $this->broadcastViaOneSignal($title, $message, $data),
                'firebase', 'fcm' => $this->broadcastViaFirebase($title, $message, $data),
                default => throw new \Exception("Broadcast not supported for provider: {$this->provider}")
            };
        } catch (\Exception $e) {
            Log::error('Push broadcast failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Send notification for a new appointment
     */
    public function notifyNewAppointment(User $doctor, $appointment): array
    {
        $patient = $appointment->patient;
        $title = 'New Appointment';
        $message = "New appointment scheduled with {$patient->full_name} on " .
                   $appointment->appointment_date->format('M d, Y') . " at " .
                   $appointment->appointment_time->format('h:i A');

        return $this->sendToUser($doctor, $title, $message, [
            'type' => 'appointment',
            'appointment_id' => $appointment->id,
            'action_url' => route('staff.appointments.show', $appointment)
        ]);
    }

    /**
     * Send notification to patient about appointment status change
     */
    public function notifyAppointmentStatus(Patient $patient, $appointment, string $status): array
    {
        $statusMessages = [
            'confirmed' => 'Your appointment has been confirmed',
            'cancelled' => 'Your appointment has been cancelled',
            'completed' => 'Your appointment has been marked as completed',
            'rescheduled' => 'Your appointment has been rescheduled'
        ];

        $title = 'Appointment Update';
        $message = ($statusMessages[$status] ?? 'Your appointment status has changed') .
                   " for " . $appointment->appointment_date->format('M d, Y');

        return $this->sendToPatient($patient, $title, $message, [
            'type' => 'appointment',
            'appointment_id' => $appointment->id,
            'status' => $status
        ]);
    }

    /**
     * Send via OneSignal
     */
    protected function sendViaOneSignal(array $tokens, string $title, string $message, array $data = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id' => $this->appId,
            'include_player_ids' => $tokens,
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'data' => $data,
            'android_channel_id' => config('app.name') . '_channel',
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => 1
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            return [
                'success' => true,
                'notification_id' => $responseData['id'] ?? null,
                'recipients' => $responseData['recipients'] ?? count($tokens),
                'message' => 'Push notification sent successfully'
            ];
        }

        throw new \Exception($response->json('errors')[0] ?? 'OneSignal API error');
    }

    /**
     * Broadcast via OneSignal to all users
     */
    protected function broadcastViaOneSignal(string $title, string $message, array $data = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id' => $this->appId,
            'included_segments' => ['All'],
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'data' => $data
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            return [
                'success' => true,
                'notification_id' => $responseData['id'] ?? null,
                'recipients' => $responseData['recipients'] ?? 'all',
                'message' => 'Broadcast sent successfully'
            ];
        }

        throw new \Exception($response->json('errors')[0] ?? 'OneSignal broadcast error');
    }

    /**
     * Send via Firebase Cloud Messaging
     */
    protected function sendViaFirebase(array $tokens, string $title, string $message, array $data = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $message,
                'sound' => 'default',
                'badge' => 1
            ],
            'data' => $data,
            'priority' => 'high'
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            return [
                'success' => ($responseData['success'] ?? 0) > 0,
                'notification_id' => $responseData['multicast_id'] ?? null,
                'recipients' => $responseData['success'] ?? 0,
                'failures' => $responseData['failure'] ?? 0,
                'message' => 'FCM notification sent'
            ];
        }

        throw new \Exception($response->json('error') ?? 'FCM API error');
    }

    /**
     * Broadcast via Firebase to topic
     */
    protected function broadcastViaFirebase(string $title, string $message, array $data = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => '/topics/all_users',
            'notification' => [
                'title' => $title,
                'body' => $message,
                'sound' => 'default'
            ],
            'data' => $data,
            'priority' => 'high'
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'notification_id' => $response->json('message_id'),
                'message' => 'FCM broadcast sent'
            ];
        }

        throw new \Exception($response->json('error') ?? 'FCM broadcast error');
    }

    /**
     * Send via Pusher Beams
     */
    protected function sendViaPusher(array $tokens, string $title, string $message, array $data = []): array
    {
        $settings = SiteSetting::getSettings();
        $instanceId = $settings['pusher_instance_id'] ?? null;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post("https://{$instanceId}.pushnotifications.pusher.com/publish_api/v1/instances/{$instanceId}/publishes/users", [
            'users' => $tokens,
            'web' => [
                'notification' => [
                    'title' => $title,
                    'body' => $message,
                    'deep_link' => $data['action_url'] ?? null
                ],
                'data' => $data
            ]
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'notification_id' => $response->json('publishId'),
                'message' => 'Pusher notification sent'
            ];
        }

        throw new \Exception($response->json('description') ?? 'Pusher API error');
    }

    /**
     * Get user's push tokens from database
     */
    protected function getUserPushTokens(User $user): array
    {
        // Tokens are stored in user's push_tokens column (JSON array)
        $tokens = $user->push_tokens ?? [];

        if (is_string($tokens)) {
            $tokens = json_decode($tokens, true) ?? [];
        }

        return array_filter($tokens);
    }

    /**
     * Get patient's push tokens from database
     */
    protected function getPatientPushTokens(Patient $patient): array
    {
        // Tokens are stored in patient's push_tokens column (JSON array)
        $tokens = $patient->push_tokens ?? [];

        if (is_string($tokens)) {
            $tokens = json_decode($tokens, true) ?? [];
        }

        return array_filter($tokens);
    }

    /**
     * Register a push token for a user
     */
    public function registerUserToken(User $user, string $token, string $platform = 'web'): bool
    {
        $tokens = $this->getUserPushTokens($user);

        // Add token if not already registered
        if (!in_array($token, $tokens)) {
            $tokens[] = $token;
            $user->update(['push_tokens' => json_encode($tokens)]);

            Log::info('Push token registered', [
                'user_id' => $user->id,
                'platform' => $platform
            ]);
        }

        return true;
    }

    /**
     * Register a push token for a patient
     */
    public function registerPatientToken(Patient $patient, string $token, string $platform = 'web'): bool
    {
        $tokens = $this->getPatientPushTokens($patient);

        if (!in_array($token, $tokens)) {
            $tokens[] = $token;
            $patient->update(['push_tokens' => json_encode($tokens)]);

            Log::info('Push token registered for patient', [
                'patient_id' => $patient->id,
                'platform' => $platform
            ]);
        }

        return true;
    }

    /**
     * Unregister a push token
     */
    public function unregisterToken(User $user, string $token): bool
    {
        $tokens = $this->getUserPushTokens($user);
        $tokens = array_filter($tokens, fn($t) => $t !== $token);
        $user->update(['push_tokens' => json_encode(array_values($tokens))]);

        return true;
    }

    /**
     * Log push notification attempt
     */
    protected function logPushAttempt(array $tokens, string $title, array $result): void
    {
        Log::channel('push')->info('Push notification attempt', [
            'tokens_count' => count($tokens),
            'title' => $title,
            'provider' => $this->provider,
            'success' => $result['success'],
            'notification_id' => $result['notification_id'] ?? null,
            'recipients' => $result['recipients'] ?? null,
            'error' => $result['success'] ? null : ($result['message'] ?? 'Unknown error')
        ]);
    }

    /**
     * Get available push notification providers
     */
    public static function getProviders(): array
    {
        return [
            'onesignal' => 'OneSignal',
            'firebase' => 'Firebase Cloud Messaging (FCM)',
            'pusher' => 'Pusher Beams'
        ];
    }

    /**
     * Test push configuration
     */
    public function testConfiguration(User $user): array
    {
        return $this->sendToUser($user, 'Test Notification', 'Push notification configuration is working correctly!', [
            'type' => 'test'
        ]);
    }
}
