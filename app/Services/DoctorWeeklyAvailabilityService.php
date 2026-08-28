<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorAvailabilityRule;

class DoctorWeeklyAvailabilityService
{
    /** @var array<int, string> */
    private array $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

    /** @var array<int, string> */
    private array $allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    /**
     * Normalized weekly schedule for admin display (supports multi-window sessions).
     *
     * @return array<string, array{available: bool, sessions: array<int, array{start: string, end: string}>, breaks: array<int, array{start: string, end: string}>}>
     */
    public function displayDaysForDoctor(Doctor $doctor): array
    {
        $availability = $doctor->availability;
        if (! is_array($availability) || count($availability) === 0) {
            return [];
        }

        $hasSavedWeeklySchedule = true;
        $days = [];

        foreach ($this->allDays as $day) {
            $dayData = is_array($availability) ? ($availability[$day] ?? []) : [];
            $sessions = $this->workingSessionsForDay($availability, $hasSavedWeeklySchedule, $day);
            $breaks = is_array($dayData['breaks'] ?? null) ? $dayData['breaks'] : [];

            $days[$day] = [
                'available' => $sessions !== [],
                'sessions' => $sessions,
                'breaks' => $breaks,
            ];
        }

        return $days;
    }

    /**
     * Normalize availability so each day has sessions for schedule/edit forms.
     * Converts legacy start/end/breaks into sessions when needed.
     *
     * @return array<string, array<string, mixed>>
     */
    public function normalizeAvailabilityForForm(?array $availability): array
    {
        $availability = is_array($availability) ? $availability : [];

        foreach ($this->allDays as $day) {
            $dayData = $availability[$day] ?? ['available' => false, 'start' => '09:00', 'end' => '17:00', 'breaks' => []];
            if (! empty($dayData['sessions']) && is_array($dayData['sessions'])) {
                $availability[$day] = $dayData;

                continue;
            }

            if (empty($dayData['available'])) {
                $availability[$day] = array_merge($dayData, ['sessions' => []]);

                continue;
            }

            $start = $dayData['start'] ?? $dayData['from'] ?? '09:00';
            $end = $dayData['end'] ?? $dayData['to'] ?? '17:00';
            $breaks = is_array($dayData['breaks'] ?? null) ? $dayData['breaks'] : [];
            $availability[$day] = array_merge($dayData, [
                'sessions' => $this->splitRangeByBreaks($start, $end, $breaks),
            ]);
        }

        return $availability;
    }

    /**
     * Parse availability submitted from a schedule form (sessions or legacy start/end).
     *
     * @param  array<string, mixed>  $requestAvailability
     * @return array<string, array<string, mixed>>
     */
    public function buildAvailabilityFromRequest(array $requestAvailability): array
    {
        $availability = [];

        foreach ($this->allDays as $day) {
            $dayInput = is_array($requestAvailability[$day] ?? null) ? $requestAvailability[$day] : [];
            $sessionsInput = $dayInput['sessions'] ?? [];
            $isAvailable = filter_var($dayInput['available'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if ($isAvailable && is_array($sessionsInput) && ! empty($sessionsInput)) {
                $sessions = $this->parseSessions($sessionsInput);
                if ($sessions === []) {
                    throw new \InvalidArgumentException(ucfirst($day).': at least one valid time window (start before end) is required.');
                }

                $availability[$day] = [
                    'available' => true,
                    'sessions' => $sessions,
                ];

                continue;
            }

            if ($isAvailable && is_array($sessionsInput) && empty($sessionsInput)) {
                throw new \InvalidArgumentException(ucfirst($day).': at least one valid time window (start before end) is required.');
            }

            $startTime = $dayInput['start'] ?? '09:00';
            $endTime = $dayInput['end'] ?? '17:00';
            if ($isAvailable && $startTime >= $endTime) {
                throw new \InvalidArgumentException('Invalid time range for '.ucfirst($day).'. Start time must be before end time.');
            }

            $availability[$day] = [
                'available' => $isAvailable,
                'start' => $startTime,
                'end' => $endTime,
                'breaks' => $this->parseBreaks($dayInput['breaks'] ?? []),
            ];
        }

        return $availability;
    }

    /**
     * One-time heal: doctors who saved weekly JSON before rule sync existed.
     */
    public function syncRulesFromWeeklyScheduleIfStale(Doctor $doctor): void
    {
        if (! is_array($doctor->availability) || count($doctor->availability) === 0) {
            return;
        }

        if (! config('booking.modality_rules_enabled', true)) {
            return;
        }

        if ($doctor->availabilityRules()->where('source', DoctorAvailabilityRule::SOURCE_MANUAL)->exists()) {
            return;
        }

        if ($doctor->availabilityRules()->where('source', DoctorAvailabilityRule::SOURCE_WEEKLY_SCHEDULE)->exists()) {
            return;
        }

        $this->syncRulesFromWeeklySchedule($doctor);
    }

    /**
     * Keep modality rules aligned with the doctor's saved weekly schedule JSON.
     * Replaces auto-managed rules only; manual per-modality rules are preserved.
     */
    public function syncRulesFromWeeklySchedule(Doctor $doctor): void
    {
        if (! config('booking.modality_rules_enabled', true)) {
            return;
        }

        $availability = $doctor->availability;
        if (! is_array($availability)) {
            return;
        }

        $doctor->availabilityRules()
            ->whereIn('source', DoctorAvailabilityRule::AUTO_MANAGED_SOURCES)
            ->delete();

        $hasSavedWeeklySchedule = count($availability) > 0;

        foreach ($this->allDays as $day) {
            foreach ($this->workingSessionsForDay($availability, $hasSavedWeeklySchedule, $day) as $session) {
                $doctor->availabilityRules()->create([
                    'day_of_week' => $day,
                    'start_time' => $this->normalizeTime($session['start']),
                    'end_time' => $this->normalizeTime($session['end']),
                    'modality' => DoctorAvailabilityRule::MODALITY_ALL,
                    'is_active' => true,
                    'needs_review' => false,
                    'source' => DoctorAvailabilityRule::SOURCE_WEEKLY_SCHEDULE,
                ]);
            }
        }
    }

    /**
     * Mirror SlotAvailabilityService::getWorkingSessions() for a raw availability array.
     *
     * @return array<int, array{start: string, end: string}>
     */
    public function workingSessionsForDay(?array $availability, bool $hasSavedWeeklySchedule, string $dayName): array
    {
        if (! $availability || ! isset($availability[$dayName])) {
            if (! $hasSavedWeeklySchedule) {
                if (in_array($dayName, $this->weekdays, true)) {
                    return [['start' => '09:00', 'end' => '17:00']];
                }

                return [];
            }

            return [];
        }

        $dayAvailability = $availability[$dayName];
        if (! is_array($dayAvailability)) {
            return [];
        }

        if (array_key_exists('available', $dayAvailability)) {
            $flag = $dayAvailability['available'];
            if ($flag === false || $flag === 0 || $flag === '0' || $flag === '' || $flag === null) {
                return [];
            }
        }

        if (! empty($dayAvailability['sessions']) && is_array($dayAvailability['sessions'])) {
            $sessions = [];
            foreach ($dayAvailability['sessions'] as $session) {
                if (! empty($session['start']) && ! empty($session['end']) && $session['start'] < $session['end']) {
                    $sessions[] = ['start' => $session['start'], 'end' => $session['end']];
                }
            }

            return $sessions;
        }

        $start = $dayAvailability['start'] ?? $dayAvailability['from'] ?? '09:00';
        $end = $dayAvailability['end'] ?? $dayAvailability['to'] ?? '17:00';
        $breaks = $dayAvailability['breaks'] ?? [];

        return $this->splitRangeByBreaks($start, $end, $breaks);
    }

    /**
     * @param  array<int, array{start?: string, end?: string}>  $breaks
     * @return array<int, array{start: string, end: string}>
     */
    private function splitRangeByBreaks(string $start, string $end, array $breaks): array
    {
        if (empty($breaks)) {
            return $start < $end ? [['start' => $start, 'end' => $end]] : [];
        }

        $ranges = [['start' => $start, 'end' => $end]];
        foreach ($breaks as $break) {
            if (empty($break['start']) || empty($break['end'])) {
                continue;
            }

            $newRanges = [];
            foreach ($ranges as $range) {
                $breakStart = $break['start'];
                $breakEnd = $break['end'];
                if ($breakEnd <= $range['start'] || $breakStart >= $range['end']) {
                    $newRanges[] = $range;
                    continue;
                }
                if ($range['start'] < $breakStart) {
                    $newRanges[] = ['start' => $range['start'], 'end' => $breakStart];
                }
                if ($breakEnd < $range['end']) {
                    $newRanges[] = ['start' => $breakEnd, 'end' => $range['end']];
                }
            }
            $ranges = $newRanges;
        }

        return array_values(array_filter($ranges, fn (array $range) => $range['start'] < $range['end']));
    }

    private function normalizeTime(string $time): string
    {
        $time = trim($time);
        if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
            return $time.':00';
        }

        if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }

        return '00:00:00';
    }

    /**
     * @param  array<int, array<string, mixed>>  $sessions
     * @return array<int, array{start: string, end: string}>
     */
    private function parseSessions(array $sessions): array
    {
        $parsed = [];
        foreach ($sessions as $session) {
            if (! is_array($session)) {
                continue;
            }

            $start = $session['start'] ?? $session['from'] ?? null;
            $end = $session['end'] ?? $session['to'] ?? null;
            if (! empty($start) && ! empty($end) && $start < $end) {
                $parsed[] = ['start' => $start, 'end' => $end];
            }
        }

        return $parsed;
    }

    /**
     * @param  mixed  $breaks
     * @return array<int, array{start: string, end: string}>
     */
    private function parseBreaks($breaks): array
    {
        if (! is_array($breaks)) {
            return [];
        }

        $parsedBreaks = [];
        foreach ($breaks as $break) {
            if (! is_array($break)) {
                continue;
            }

            if (! empty($break['start']) && ! empty($break['end'])) {
                $parsedBreaks[] = [
                    'start' => $break['start'],
                    'end' => $break['end'],
                ];
            }
        }

        return $parsedBreaks;
    }
}
