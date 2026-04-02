<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Department;
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

        // Offer start times on the same step as visit length (e.g. 30 min duration → 9:00, 9:30, 10:00…)
        $slotStartIncrementMinutes = max(5, min(120, $duration));
        $slotStartIncrementMinutes = (int) (round($slotStartIncrementMinutes / 5) * 5);

        // Get doctor's working sessions (one or more time windows) for this day
        $dayName = strtolower($dateObj->format('l')); // monday, tuesday, etc.
        $sessions = $this->getWorkingSessions($doctor, $dayName);

        if (empty($sessions)) {
            return []; // Doctor not available on this day
        }

        // Get existing appointments for this date (with service for duration fallback)
        // Include ALL doctors in the same clinic/department for clinic-wide slot blocking
        $departmentIds = $this->getDoctorDepartmentIds($doctor);
        $doctorIdsInClinic = [$doctorId];
        if (!empty($departmentIds)) {
            $clinicDoctorIds = Doctor::byDepartments($departmentIds)->pluck('id')->toArray();
            if (!empty($clinicDoctorIds)) {
                $doctorIdsInClinic = $clinicDoctorIds;
            }
        }

        $existingAppointments = Appointment::with('service')
            ->whereIn('doctor_id', $doctorIdsInClinic)
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

                $currentTime->addMinutes($slotStartIncrementMinutes);
            }
        }

        return $slots;
    }

    /**
     * Get available time slots for a clinic/department on a specific date.
     * Returns union of slots when ANY doctor in the department is free.
     * Excludes slots already taken by appointments or pending clinic booking requests.
     *
     * @param int $departmentId
     * @param string $date (YYYY-MM-DD)
     * @param int|null $serviceId
     * @param int|null $durationMinutes
     * @return array
     */
    public function getAvailableSlotsForDepartment($departmentId, $date, $serviceId = null, $durationMinutes = null)
    {
        $department = Department::findOrFail($departmentId);
        $doctors = Doctor::byDepartment($departmentId)->active()->get();

        if ($doctors->isEmpty()) {
            return [];
        }

        // Get duration from service if provided
        $duration = 30;
        if (is_numeric($durationMinutes) && (int) $durationMinutes > 0) {
            $duration = (int) $durationMinutes;
        } elseif ($serviceId) {
            $service = BookingService::find($serviceId);
            if ($service) {
                // Use first doctor's duration as default for display
                $firstDoctor = $doctors->first();
                $duration = $service->getDurationForDoctor($firstDoctor->id) ?? $service->default_duration_minutes ?? 30;
            }
        }

        // Collect all slots from all doctors (union)
        $allSlots = [];
        foreach ($doctors as $doctor) {
            if (!$serviceId || \App\Models\BookingService::find($serviceId)?->isAvailableForDoctor($doctor->id)) {
                $doctorSlots = $this->getAvailableSlots($doctor->id, $date, $serviceId, $duration);
                foreach ($doctorSlots as $slot) {
                    $key = $slot['start'];
                    if (!isset($allSlots[$key])) {
                        $allSlots[$key] = $slot;
                    }
                }
            }
        }

        // Exclude slots taken by pending clinic booking requests (same department, same date/time)
        $pendingRequests = \App\Models\ClinicBookingRequest::where('department_id', $departmentId)
            ->where('status', 'pending_acceptance')
            ->whereDate('appointment_date', $date)
            ->get();

        foreach ($pendingRequests as $req) {
            $reqTime = $req->appointment_time instanceof \DateTimeInterface
                ? $req->appointment_time->format('H:i')
                : substr((string) $req->appointment_time, 0, 5);
            unset($allSlots[$reqTime]);
        }

        // Exclude slots where ANY doctor in the department has an appointment (clinic-wide sync)
        $departmentAppointments = Appointment::with('service')
            ->whereHas('doctor', fn($q) => $q->byDepartment($departmentId))
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'rescheduled'])
            ->get();

        foreach ($allSlots as $slotKey => $slot) {
            if ($this->slotOverlapsAppointments($slot['start'], $duration, $date, $departmentAppointments)) {
                unset($allSlots[$slotKey]);
            }
        }

        $slots = array_values($allSlots);
        usort($slots, fn($a, $b) => strcmp($a['start'], $b['start']));

        return $slots;
    }

    /**
     * Check if a specific date is blocked by the doctor.
     *
     * Full-day blocks exclude all slots. Partial-day blocks (explicit start/end) are applied in
     * getBlockedTimes() only. Compare exception_date as a calendar Y-m-d to avoid whereDate/timezone
     * mismatches against the DATE column.
     *
     * Note: Clinic/department public booking unions slots across doctors. If several doctors work
     * the same clinic, a block on one doctor still leaves other doctors' slots visible unless each
     * doctor blocks that day or clinic-wide blocking is added separately.
     *
     * @param int $doctorId
     * @param \Carbon\Carbon|string $date
     * @return bool
     */
    public function isDateBlocked($doctorId, $date)
    {
        $dateStr = $this->exceptionDateString($date);

        return DoctorAvailabilityException::where('doctor_id', $doctorId)
            ->where('exception_date', $dateStr)
            ->where('type', 'blocked')
            ->where(function ($q) {
                // Partial-day: is_all_day false with both window times set → handled in getBlockedTimes().
                $q->whereNot(function ($q2) {
                    $q2->where('is_all_day', false)
                        ->whereNotNull('start_time')
                        ->whereNotNull('end_time');
                });
            })
            ->exists();
    }

    /**
     * Get blocked date exception for a specific date (if any).
     *
     * @param int $doctorId
     * @param \Carbon\Carbon|string $date
     * @return DoctorAvailabilityException|null
     */
    public function getBlockedException($doctorId, $date)
    {
        $dateStr = $this->exceptionDateString($date);

        return DoctorAvailabilityException::where('doctor_id', $doctorId)
            ->where('exception_date', $dateStr)
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
        $availability = $doctor->availability;
        $hasSavedWeeklySchedule = is_array($availability) && count($availability) > 0;

        if (!$availability || !isset($availability[$dayName])) {
            if (!$hasSavedWeeklySchedule) {
                $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                if (in_array($dayName, $weekdays, true)) {
                    return [['start' => '09:00', 'end' => '17:00']];
                }

                return [];
            }

            // Doctor has saved weekly availability but this weekday has no row — treat as closed
            // so clinic / public booking does not invent 9–17 when they turned this day off.
            return [];
        }

        $dayAvailability = $availability[$dayName];
        if (!is_array($dayAvailability)) {
            return [];
        }

        // Respect explicit "closed" before parsing sessions (avoids stale session rows when toggled off)
        if (array_key_exists('available', $dayAvailability)) {
            $flag = $dayAvailability['available'];
            if ($flag === false || $flag === 0 || $flag === '0' || $flag === '' || $flag === null) {
                return [];
            }
        }

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
        $dateStr = $this->exceptionDateString($date);
        $exception = DoctorAvailabilityException::where('doctor_id', $doctor->id)
            ->where('exception_date', $dateStr)
            ->where('is_all_day', false)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
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
            // Use estimated_duration if set; else service duration for doctor; else 30
            $apptDuration = (int) ($appointment->estimated_duration ?? null);
            if ($apptDuration <= 0 && $appointment->service_id && $appointment->doctor_id) {
                $apptDuration = (int) ($appointment->service->getDurationForDoctor($appointment->doctor_id) ?? 30);
            }
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
     * Get department IDs for a doctor (primary department and pivot departments).
     *
     * @param Doctor $doctor
     * @return array
     */
    private function getDoctorDepartmentIds(Doctor $doctor): array
    {
        $ids = [];
        if ($doctor->department_id) {
            $ids[] = $doctor->department_id;
        }
        foreach ($doctor->departments as $dept) {
            $ids[] = $dept->id;
        }
        return array_values(array_unique($ids));
    }

    /**
     * Check if a slot overlaps with any appointment in a collection.
     *
     * @param string $slotStart Time string (H:i)
     * @param int $slotDurationMinutes
     * @param string $date Date string (Y-m-d)
     * @param \Illuminate\Database\Eloquent\Collection $appointments
     * @return bool
     */
    private function slotOverlapsAppointments(string $slotStart, int $slotDurationMinutes, string $date, $appointments): bool
    {
        $slotStartCarbon = Carbon::parse($date . ' ' . $slotStart);
        $slotEndCarbon = $slotStartCarbon->copy()->addMinutes($slotDurationMinutes);

        foreach ($appointments as $appointment) {
            $apptStart = Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time->format('H:i:s'));
            $apptDuration = (int) ($appointment->estimated_duration ?? null);
            if ($apptDuration <= 0 && $appointment->service_id && $appointment->doctor_id) {
                $apptDuration = (int) ($appointment->service->getDurationForDoctor($appointment->doctor_id) ?? 30);
            }
            if ($apptDuration <= 0) {
                $apptDuration = 30;
            }
            $apptEnd = $apptStart->copy()->addMinutes($apptDuration);

            if ($slotStartCarbon->lt($apptEnd) && $slotEndCarbon->gt($apptStart)) {
                return true;
            }
        }
        return false;
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

    /**
     * Normalize to Y-m-d for querying the exception_date DATE column (avoids timezone drift with whereDate).
     */
    private function exceptionDateString(Carbon|string|\DateTimeInterface $date): string
    {
        if ($date instanceof Carbon) {
            return $date->toDateString();
        }

        return Carbon::parse($date)->toDateString();
    }
}
