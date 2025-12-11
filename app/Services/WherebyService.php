<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WherebyService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.whereby.dev/v1';
    protected $enabled = false;

    public function __construct()
    {
        $this->apiKey = Setting::get('whereby_api_key');
        $this->enabled = Setting::get('whereby_enabled', '0') === '1';
    }

    /**
     * Check if Whereby integration is enabled and configured.
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->apiKey);
    }

    /**
     * Create a Whereby meeting room for an appointment.
     *
     * @param Appointment $appointment
     * @return array|null Returns meeting data or null on failure
     */
    public function createMeeting(Appointment $appointment): ?array
    {
        if (!$this->isEnabled()) {
            Log::warning('Whereby integration is not enabled or configured');
            return null;
        }

        try {
            // Calculate end time (appointment time + duration + 30 minutes buffer)
            $appointmentDateTime = \Carbon\Carbon::parse(
                $appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time
            );

            // Get appointment duration (default 60 minutes if not set)
            $duration = $appointment->service?->default_duration_minutes ?? 60;

            // End date is appointment time + duration + 30 minutes buffer
            $endDate = $appointmentDateTime->copy()->addMinutes($duration + 30);

            // Prepare room name prefix
            $roomNamePrefix = Setting::get('whereby_room_prefix', 'consultation');
            $roomNamePrefix = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $roomNamePrefix));

            // Limit to 39 characters
            if (strlen($roomNamePrefix) > 39) {
                $roomNamePrefix = substr($roomNamePrefix, 0, 39);
            }

            // Get room mode from settings (normal = up to 4 participants, group = more)
            $roomMode = Setting::get('whereby_room_mode', 'normal');

            $requestBody = [
                'endDate' => $endDate->toIso8601String(),
                'isLocked' => Setting::get('whereby_rooms_locked', '1') === '1',
                'roomMode' => $roomMode,
                'roomNamePrefix' => $roomNamePrefix,
                'roomNamePattern' => 'uuid',
                'fields' => ['hostRoomUrl'],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/meetings', $requestBody);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Whereby meeting created successfully', [
                    'appointment_id' => $appointment->id,
                    'meeting_id' => $data['meetingId'] ?? null,
                    'room_url' => $data['roomUrl'] ?? null,
                ]);

                return [
                    'meeting_id' => $data['meetingId'] ?? null,
                    'room_url' => $data['roomUrl'] ?? null,
                    'host_room_url' => $data['hostRoomUrl'] ?? $data['roomUrl'] ?? null,
                    'start_date' => $data['startDate'] ?? null,
                    'end_date' => $data['endDate'] ?? null,
                ];
            }

            Log::error('Failed to create Whereby meeting', [
                'appointment_id' => $appointment->id,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Whereby API error', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get meeting details from Whereby.
     *
     * @param string $meetingId
     * @return array|null
     */
    public function getMeeting(string $meetingId): ?array
    {
        if (!$this->isEnabled()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/meetings/' . $meetingId, [
                'fields' => ['hostRoomUrl'],
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Failed to get Whereby meeting', [
                'meeting_id' => $meetingId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Whereby API error getting meeting', [
                'meeting_id' => $meetingId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Delete a Whereby meeting.
     *
     * @param string $meetingId
     * @return bool
     */
    public function deleteMeeting(string $meetingId): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->delete($this->baseUrl . '/meetings/' . $meetingId);

            // 204 No Content means success, also 404 means already deleted (idempotent)
            if ($response->status() === 204 || $response->status() === 404) {
                Log::info('Whereby meeting deleted', ['meeting_id' => $meetingId]);
                return true;
            }

            Log::warning('Failed to delete Whereby meeting', [
                'meeting_id' => $meetingId,
                'status' => $response->status(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('Whereby API error deleting meeting', [
                'meeting_id' => $meetingId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Create meeting and update appointment with meeting link.
     *
     * @param Appointment $appointment
     * @return bool
     */
    public function createMeetingForAppointment(Appointment $appointment): bool
    {
        // Only create if appointment is online and doesn't already have a meeting link
        if (!$appointment->is_online) {
            return false;
        }

        // Skip if already has a meeting link (unless it's empty)
        if (!empty($appointment->meeting_link)) {
            return true;
        }

        $meetingData = $this->createMeeting($appointment);

        if ($meetingData && !empty($meetingData['room_url'])) {
            $appointment->update([
                'meeting_link' => $meetingData['room_url'],
                'meeting_platform' => 'whereby',
                'whereby_meeting_id' => $meetingData['meeting_id'],
                'whereby_host_url' => $meetingData['host_room_url'],
            ]);

            return true;
        }

        return false;
    }

    /**
     * Test the API connection.
     *
     * @param string|null $apiKey Optional API key to test (uses configured key if not provided)
     * @return array
     */
    public function testConnection(?string $apiKey = null): array
    {
        $keyToTest = $apiKey ?? $this->apiKey;

        if (empty($keyToTest)) {
            return [
                'success' => false,
                'message' => 'API key is not configured',
            ];
        }

        try {
            // Create a test meeting that ends in 5 minutes
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $keyToTest,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/meetings', [
                'endDate' => now()->addMinutes(5)->toIso8601String(),
                'roomNamePrefix' => 'test',
                'roomMode' => 'normal',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Delete the test meeting immediately using the same key
                if (!empty($data['meetingId'])) {
                    Http::withHeaders([
                        'Authorization' => 'Bearer ' . $keyToTest,
                    ])->delete($this->baseUrl . '/meetings/' . $data['meetingId']);
                }

                return [
                    'success' => true,
                    'message' => 'Connection successful! Whereby API is working.',
                ];
            }

            $errorData = $response->json();
            return [
                'success' => false,
                'message' => $errorData['error'] ?? 'API request failed with status ' . $response->status(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ];
        }
    }
}
