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
     * Default meeting platform for online appointments when none is selected.
     */
    public function resolvedOnlineMeetingPlatform(?string $requested): ?string
    {
        if ($requested !== null && $requested !== '') {
            return $requested;
        }

        return $this->isEnabled() ? 'whereby' : null;
    }

    /**
     * Whether staff must paste a meeting URL (Whereby auto-generates when enabled).
     */
    public function requiresManualMeetingLink(?string $platform): bool
    {
        if (($platform ?? '') === 'whereby' && $this->isEnabled()) {
            return false;
        }

        return true;
    }

    /**
     * Create a Whereby room on an online appointment if one is not already set.
     */
    public function ensureMeetingForAppointment(Appointment $appointment): Appointment
    {
        if (! $appointment->is_online
            || ($appointment->meeting_platform ?? '') !== 'whereby'
            || ! empty($appointment->meeting_link)
            || ! $this->isEnabled()) {
            return $appointment;
        }

        $this->createMeetingForAppointment($appointment);

        return $appointment->refresh();
    }

    /**
     * Create a Whereby meeting room for an appointment.
     *
     * @param Appointment $appointment
     * @return array|null Returns meeting data or null on failure
     */
    public function createMeeting(Appointment $appointment): ?array
    {
        Log::info('WherebyService::createMeeting called', [
            'appointment_id' => $appointment->id,
            'is_online' => $appointment->is_online,
            'meeting_platform' => $appointment->meeting_platform,
            'existing_meeting_link' => $appointment->meeting_link,
        ]);

        if (!$this->isEnabled()) {
            Log::warning('Whereby integration is not enabled or configured', [
                'enabled_setting' => $this->enabled,
                'has_api_key' => !empty($this->apiKey),
                'api_key_length' => $this->apiKey ? strlen($this->apiKey) : 0,
            ]);
            return null;
        }

        try {
            // Calculate end time (appointment time + duration + 30 minutes buffer)
            // Handle different formats: appointment_time might be just time (H:i:s) or full datetime
            $timeValue = $appointment->appointment_time;

            /**
             * IMPORTANT:
             * In this codebase, `Appointment::appointment_time` is cast as `datetime:H:i`.
             * That means it often comes through as a Carbon instance that represents a TIME,
             * but its DATE portion may be unrelated to `appointment_date`.
             *
             * Always combine `appointment_date` + `appointment_time` to build a real datetime.
             */
            if (!empty($appointment->appointment_date) && !empty($appointment->appointment_time)) {
                // Uses Appointment accessor which safely combines date + time.
                $appointmentDateTime = $appointment->appointment_date_time;
            } elseif ($timeValue instanceof \Carbon\Carbon) {
                // Fallback (should be rare): use whatever we have.
                $appointmentDateTime = $timeValue;
            } elseif (is_string($timeValue) && preg_match('/^\d{4}-\d{2}-\d{2}/', $timeValue)) {
                // Already contains a date (e.g., "2025-12-12 11:00:00"), parse directly
                $appointmentDateTime = \Carbon\Carbon::parse($timeValue);
            } else {
                // Last-resort: parse whatever time/string is provided.
                $appointmentDateTime = \Carbon\Carbon::parse((string) $timeValue);
            }

            Log::info('Whereby: Parsed appointment datetime', [
                'original_time_value' => $timeValue,
                'appointment_date' => $appointment->appointment_date instanceof \Carbon\Carbon
                    ? $appointment->appointment_date->toDateString()
                    : $appointment->appointment_date,
                'appointment_time_his' => $appointment->appointment_time instanceof \Carbon\Carbon
                    ? $appointment->appointment_time->format('H:i:s')
                    : $appointment->appointment_time,
                'parsed_datetime' => $appointmentDateTime->toIso8601String(),
            ]);

            // Get appointment duration (default 60 minutes if not set)
            $duration = $appointment->service?->default_duration_minutes ?? 60;

            // End date is appointment time + duration + 30 minutes buffer
            $endDate = $appointmentDateTime->copy()->addMinutes($duration + 30);

            // IMPORTANT: Whereby requires endDate to be in the future
            // If endDate is in the past, set it to 24 hours from now
            $now = \Carbon\Carbon::now();
            if ($endDate->lte($now)) {
                Log::warning('Whereby: Calculated endDate is in the past, adjusting to 24 hours from now', [
                    'original_end_date' => $endDate->toIso8601String(),
                    'appointment_datetime' => $appointmentDateTime->toIso8601String(),
                ]);
                $endDate = $now->copy()->addHours(24);
            }

            Log::info('Whereby: End date calculated', [
                'appointment_datetime' => $appointmentDateTime->toIso8601String(),
                'end_date' => $endDate->toIso8601String(),
                'duration_minutes' => $duration,
            ]);

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

            Log::info('Whereby: Making API request', [
                'url' => $this->baseUrl . '/meetings',
                'request_body' => $requestBody,
            ]);

            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/meetings', $requestBody);

            Log::info('Whereby: API response received', [
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

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
                'request_body' => $requestBody,
                'api_key_prefix' => substr($this->apiKey, 0, 20) . '...',
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
        Log::info('WherebyService::createMeetingForAppointment called', [
            'appointment_id' => $appointment->id,
            'is_online' => $appointment->is_online,
            'existing_meeting_link' => $appointment->meeting_link,
        ]);

        // Only create if appointment is online and doesn't already have a meeting link
        if (!$appointment->is_online) {
            Log::info('Whereby: Skipping - appointment is not online');
            return false;
        }

        // Skip if already has a meeting link (unless it's empty)
        if (!empty($appointment->meeting_link)) {
            Log::info('Whereby: Skipping - appointment already has meeting link', [
                'meeting_link' => $appointment->meeting_link,
            ]);
            return true;
        }

        $meetingData = $this->createMeeting($appointment);

        if ($meetingData && !empty($meetingData['room_url'])) {
            Log::info('Whereby: Meeting created, saving to appointment', [
                'appointment_id' => $appointment->id,
                'room_url' => $meetingData['room_url'],
                'host_room_url' => $meetingData['host_room_url'],
                'meeting_id' => $meetingData['meeting_id'],
            ]);

            $appointment->update([
                'meeting_link' => $meetingData['room_url'],
                'meeting_platform' => 'whereby',
                'whereby_meeting_id' => $meetingData['meeting_id'],
                'whereby_host_url' => $meetingData['host_room_url'],
            ]);

            // Verify the save
            $appointment->refresh();
            Log::info('Whereby: Appointment updated', [
                'appointment_id' => $appointment->id,
                'saved_meeting_link' => $appointment->meeting_link,
                'saved_host_url' => $appointment->whereby_host_url,
            ]);

            return true;
        }

        Log::warning('Whereby: createMeeting returned no data or empty room_url', [
            'appointment_id' => $appointment->id,
            'meeting_data' => $meetingData,
        ]);

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