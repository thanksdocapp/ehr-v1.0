<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Backfill modality-aware availability rules from the legacy doctors.availability JSON.
 *
 * Parity contract: this mirrors SlotAvailabilityService::getWorkingSessions() so the windows
 * produced here reproduce today's slot times exactly. Every backfilled window is tagged
 * modality = 'all' (serves every modality) and flagged needs_review = true so admins can
 * narrow them deliberately. Doctors with no saved weekly schedule get the same implicit
 * default the slot service uses today (Mon–Fri 09:00–17:00).
 */
return new class extends Migration
{
    private array $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    private array $allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public function up(): void
    {
        if (!Schema::hasTable('doctor_availability_rules') || !Schema::hasTable('doctors')) {
            return;
        }

        DB::table('doctors')->orderBy('id')->chunkById(100, function ($doctors) {
            foreach ($doctors as $doctor) {
                // Skip if this doctor already has rules (idempotent re-run).
                $already = DB::table('doctor_availability_rules')->where('doctor_id', $doctor->id)->exists();
                if ($already) {
                    continue;
                }

                $availability = $this->decodeAvailability($doctor->availability ?? null);
                $hasSavedWeeklySchedule = is_array($availability) && count($availability) > 0;

                $rows = [];
                $now = now();

                foreach ($this->allDays as $day) {
                    $sessions = $this->getWorkingSessions($availability, $hasSavedWeeklySchedule, $day);
                    foreach ($sessions as $session) {
                        $rows[] = [
                            'doctor_id' => $doctor->id,
                            'day_of_week' => $day,
                            'start_time' => $this->normalizeTime($session['start']),
                            'end_time' => $this->normalizeTime($session['end']),
                            'modality' => 'all',
                            'is_active' => true,
                            'needs_review' => true,
                            'source' => $hasSavedWeeklySchedule ? 'backfill' : 'default',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if (!empty($rows)) {
                    DB::table('doctor_availability_rules')->insert($rows);
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('doctor_availability_rules')) {
            return;
        }

        // Only remove rows created by this backfill, leaving any manually-authored rules intact.
        DB::table('doctor_availability_rules')->whereIn('source', ['backfill', 'default'])->delete();
    }

    private function decodeAvailability($raw): ?array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : null;
        }
        return null;
    }

    private function normalizeTime(string $time): string
    {
        $time = trim($time);
        // Accept H:i or H:i:s, store as H:i:s for the TIME column.
        if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
            return $time . ':00';
        }
        if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }
        return '00:00:00';
    }

    /**
     * Mirror of SlotAvailabilityService::getWorkingSessions() (legacy JSON parsing).
     */
    private function getWorkingSessions(?array $availability, bool $hasSavedWeeklySchedule, string $dayName): array
    {
        if (!$availability || !isset($availability[$dayName])) {
            if (!$hasSavedWeeklySchedule) {
                if (in_array($dayName, $this->weekdays, true)) {
                    return [['start' => '09:00', 'end' => '17:00']];
                }
                return [];
            }
            return [];
        }

        $dayAvailability = $availability[$dayName];
        if (!is_array($dayAvailability)) {
            return [];
        }

        if (array_key_exists('available', $dayAvailability)) {
            $flag = $dayAvailability['available'];
            if ($flag === false || $flag === 0 || $flag === '0' || $flag === '' || $flag === null) {
                return [];
            }
        }

        if (!empty($dayAvailability['sessions']) && is_array($dayAvailability['sessions'])) {
            $sessions = [];
            foreach ($dayAvailability['sessions'] as $s) {
                if (!empty($s['start']) && !empty($s['end']) && $s['start'] < $s['end']) {
                    $sessions[] = ['start' => $s['start'], 'end' => $s['end']];
                }
            }
            return $sessions;
        }

        $start = $dayAvailability['start'] ?? $dayAvailability['from'] ?? '09:00';
        $end = $dayAvailability['end'] ?? $dayAvailability['to'] ?? '17:00';
        $breaks = $dayAvailability['breaks'] ?? [];
        return $this->splitRangeByBreaks($start, $end, $breaks);
    }

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
            foreach ($ranges as $r) {
                $bStart = $break['start'];
                $bEnd = $break['end'];
                if ($bEnd <= $r['start'] || $bStart >= $r['end']) {
                    $newRanges[] = $r;
                    continue;
                }
                if ($r['start'] < $bStart) {
                    $newRanges[] = ['start' => $r['start'], 'end' => $bStart];
                }
                if ($bEnd < $r['end']) {
                    $newRanges[] = ['start' => $bEnd, 'end' => $r['end']];
                }
            }
            $ranges = $newRanges;
        }
        return array_values(array_filter($ranges, fn($r) => $r['start'] < $r['end']));
    }
};
