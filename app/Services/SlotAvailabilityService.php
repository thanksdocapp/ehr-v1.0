<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\BookingService;
use App\Models\DoctorAvailabilityException;
use Carbon\Carbon;

class SlotAvailabilityService
{
    /**
     * Get available time slots for a doctor on a specific date.
     *
     * @param int $doctorId
     * @param string $date (YYYY-MM-DD)
     * @param int|null $serviceId
     * @param int|null $durationMinutes Override duration (minutes) if provided
     * @return array
     */
    public function getAvailableSlots($doctorId, $date, $serviceId = null, $durationMinutes = null)
    {
        $doctor = Doctor::findOrFail($doctorId);
        $dateObj = Carbon::parse($date);

        // Check if date is blocked by doctor
        if ($this->isDateBlocked($doctorId, $dateObj)) {
            return []; // Doctor has blocked this date
        }

        // Get duration (default 30 minutes). Priority:
        // 1) explicit override (durationMinutes)
        // 2) service duration (if serviceId provided)
        // 3) default 30
        $duration = 30;
        if (is_numeric($durationMinutes) && (int) $durationMinutes > 0) {
            $duration = (int) $durationMinutes;
        } elseif ($serviceId) {
            $service = BookingService::find($serviceId);
            if ($service) {
                $duration = $service->getDurationForDoctor($doctorId);
            }
        }

        // Get doctor's working sessions (one or more time windows) for this day
        $dayName = strtolower($dateObj->format('l')); // monday, tuesday, etc.
        $sessions = $this->getWorkingSessions($doctor, $dayName);

        if (empty($sessions)) {
            return []; // Doctor not available on this day
        }

        // Get existing appointments for this date
        $existingAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'rescheduled'])
            ->get();

        // Get blocked times (breaks within a session + partial day blocks)
        $blockedTimes = $this->getBlockedTimes($doctor, $dateObj);

        // Generate time slots for each session
        $slots = [];
        foreach ($sessions as $session) {
            $startTime = Carbon::parse($session['start']);
            $endTime = Carbon::parse($session['end']);
            $currentTime = $dateObj->copy()->setTimeFromTimeString($startTime->format('H:i'));
            $dayEnd = $dateObj->copy()->setTimeFromTimeString($endTime->format('H:i'));

            while ($currentTime->copy()->addMinutes($duration)->lte($dayEnd)) {
                $slotStart = $currentTime->copy();
                $slotEnd = $currentTime->copy()->addMinutes($duration);

                if ($this->isSlotAvailable($slotStart, $slotEnd, $existingAppointments, $blockedTimes)) {
                    $slots[] = [
                        'start' => $slotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'display' => $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A')
                    ];
                }

                $currentTime->addMinutes(15);
            }
        }

        return $slots;
    }

    /**
     * Check if a specific date is blocked by the doctor.
     *
     * @param int $doctorId
     * @param Carbon $date
     * @return bool
     */
    public function isDateBlocked($doctorId, $date)
    {
        return DoctorAvailabilityException::where('doctor_id', $doctorId)
            ->whereDate('exception_date', $date)
            ->where('type', 'blocked')
            ->where('is_all_day', true)
            ->exists();
    }

    /**
     * Get blocked date exception for a specific date (if any).
     *
     * @param int $doctorId
     * @param Carbon $date
     * @return DoctorAvailabilityException|null
     */
    public function getBlockedException($doctorId, $date)
    {
        return DoctorAvailabilityException::where('doctor_id', $doctorId)
            ->whereDate('exception_date', $date)
            ->first();
    }

    /**
     * Get working sessions (time windows) for a doctor on a specific day.
     * Supports multiple sessions per day (e.g. morning 09-12, afternoon 14-17, evening 18-21).
     * Returns array of ['start' => 'HH:MM', 'end' => 'HH:MM'].
     *
     * @param Doctor $doctor
     * @param string $dayName
     * @return array
     */
    private function getWorkingSessions($doctor, $dayName): array
    {
        if (!$doctor->availability || !isset($doctor->availability[$dayName])) {
            $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
            if (in_array($dayName, $weekdays)) {
                return [['start' => '09:00', 'end' => '17:00']];
            }
            return [];
        }

        $dayAvailability = $doctor->availability[$dayName];

        // New format: multiple sessions per day
        if (!empty($dayAvailability['sessions']) && is_array($dayAvailability['sessions'])) {
            $sessions = [];
            foreach ($dayAvailability['sessions'] as $s) {
                if (!empty($s['start']) && !empty($s['end']) && $s['start'] < $s['end']) {
                    $sessions[] = ['start' => $s['start'], 'end' => $s['end']];
                }
            }
            return $sessions;
        }

        // Legacy format: single start/end with optional breaks
        if (empty($dayAvailability['available'])) {
            return [];
        }
        $start = $dayAvailability['start'] ?? $dayAvailability['from'] ?? '09:00';
        $end = $dayAvailability['end'] ?? $dayAvailability['to'] ?? '17:00';
        $breaks = $dayAvailability['breaks'] ?? [];
        return $this->splitRangeByBreaks($start, $end, $breaks);
    }

    /**
     * Split a time range into sessions by subtracting break periods.
     *
     * @param string $start
     * @param string $end
     * @param array $breaks
     * @return array
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

    /**
     * Get working hours for a doctor on a specific day (single window; for backward compatibility).
     *
     * @param Doctor $doctor
     * @param string $dayName
     * @return array|null
     */
    private function getWorkingHours($doctor, $dayName)
    {
        $sessions = $this->getWorkingSessions($doctor, $dayName);
        if (empty($sessions)) {
            return ['available' => false, 'start' => null, 'end' => null];
        }
        $starts = array_column($sessions, 'start');
        $ends = array_column($sessions, 'end');
        return [
            'available' => true,
            'start' => min($starts),
            'end' => max($ends)
        ];
    }

    /**
     * Get blocked times (breaks, blocked days, etc.)
     *
     * @param Doctor $doctor
     * @param Carbon $date
     * @return array
     */
    private function getBlockedTimes($doctor, $date)
    {
        $blocked = [];

        // Check for breaks in availability
        $dayName = strtolower($date->format('l'));
        if ($doctor->availability && isset($doctor->availability[$dayName])) {
            $dayAvailability = $doctor->availability[$dayName];
            if (isset($dayAvailability['breaks']) && is_array($dayAvailability['breaks'])) {
                foreach ($dayAvailability['breaks'] as $break) {
                    if (!empty($break['start']) && !empty($break['end'])) {
                        $blocked[] = [
                            'start' => $break['start'],
                            'end' => $break['end']
                        ];
                    }
                }
            }
        }

        // Check for partial day blocks from exceptions
        $exception = DoctorAvailabilityException::where('doctor_id', $doctor->id)
            ->whereDate('exception_date', $date)
            ->where('is_all_day', false)
            ->first();

        if ($exception && $exception->start_time && $exception->end_time) {
            $blocked[] = [
                'start' => $exception->start_time->format('H:i'),
                'end' => $exception->end_time->format('H:i')
            ];
        }

        return $blocked;
    }

    /**
     * Check if a time slot is available.
     *
     * @param Carbon $slotStart
     * @param Carbon $slotEnd
     * @param \Illuminate\Database\Eloquent\Collection $existingAppointments
     * @param array $blockedTimes
     * @return bool
     */
    private function isSlotAvailable($slotStart, $slotEnd, $existingAppointments, $blockedTimes)
    {
        // Check if slot is at least 5 minutes in the future
        // Doctors can book appointments with only 5 minutes advance notice
        $minimumAdvanceTime = Carbon::now()->addMinutes(5);
        if ($slotStart->lte($minimumAdvanceTime)) {
            return false;
        }

        // Check if slot conflicts with existing appointments
        foreach ($existingAppointments as $appointment) {
            $apptStart = Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time->format('H:i:s'));
            $apptDuration = (int) ($appointment->estimated_duration ?? 30);
            if ($apptDuration <= 0) {
                $apptDuration = 30;
            }
            $apptEnd = $apptStart->copy()->addMinutes($apptDuration);

            // Check for overlap
            if ($slotStart->lt($apptEnd) && $slotEnd->gt($apptStart)) {
                return false;
            }
        }

        // Check if slot is in a blocked time (break)
        foreach ($blockedTimes as $blocked) {
            if ($blocked['start'] && $blocked['end']) {
                $blockStart = Carbon::parse($slotStart->format('Y-m-d') . ' ' . $blocked['start']);
                $blockEnd = Carbon::parse($slotStart->format('Y-m-d') . ' ' . $blocked['end']);

                if ($slotStart->lt($blockEnd) && $slotEnd->gt($blockStart)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get blocked dates for a doctor within a date range.
     *
     * @param int $doctorId
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Support\Collection
     */
    public function getBlockedDatesInRange($doctorId, $startDate, $endDate)
    {
        return DoctorAvailabilityException::where('doctor_id', $doctorId)
            ->whereBetween('exception_date', [$startDate, $endDate])
            ->where('type', 'blocked')
            ->get();
    }
}
