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
     * @return array
     */
    public function getAvailableSlots($doctorId, $date, $serviceId = null)
    {
        $doctor = Doctor::findOrFail($doctorId);
        $dateObj = Carbon::parse($date);

        // Check if date is blocked by doctor
        if ($this->isDateBlocked($doctorId, $dateObj)) {
            return []; // Doctor has blocked this date
        }

        // Get service duration (default 30 minutes if no service)
        $duration = 30;
        if ($serviceId) {
            $service = BookingService::find($serviceId);
            if ($service) {
                $duration = $service->getDurationForDoctor($doctorId);
            }
        }

        // Get doctor's working hours for this day
        $dayName = strtolower($dateObj->format('l')); // monday, tuesday, etc.
        $workingHours = $this->getWorkingHours($doctor, $dayName);

        if (!$workingHours || !$workingHours['available']) {
            return []; // Doctor not available on this day
        }

        // Parse working hours
        $startTime = Carbon::parse($workingHours['start']);
        $endTime = Carbon::parse($workingHours['end']);

        // Get existing appointments for this date
        $existingAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'rescheduled'])
            ->get();

        // Get blocked times (breaks + partial day blocks)
        $blockedTimes = $this->getBlockedTimes($doctor, $dateObj);

        // Generate time slots
        $slots = [];
        $currentTime = $dateObj->copy()->setTimeFromTimeString($startTime->format('H:i'));

        while ($currentTime->copy()->addMinutes($duration)->lte($dateObj->copy()->setTimeFromTimeString($endTime->format('H:i')))) {
            $slotStart = $currentTime->copy();
            $slotEnd = $currentTime->copy()->addMinutes($duration);

            // Check if slot is available
            if ($this->isSlotAvailable($slotStart, $slotEnd, $existingAppointments, $blockedTimes)) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'display' => $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A')
                ];
            }

            // Move to next slot (15-minute intervals)
            $currentTime->addMinutes(15);
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
     * Get working hours for a doctor on a specific day.
     *
     * @param Doctor $doctor
     * @param string $dayName
     * @return array|null
     */
    private function getWorkingHours($doctor, $dayName)
    {
        // Check availability from doctor model
        if ($doctor->availability && isset($doctor->availability[$dayName])) {
            $dayAvailability = $doctor->availability[$dayName];
            if (isset($dayAvailability['available']) && $dayAvailability['available']) {
                return [
                    'available' => true,
                    'start' => $dayAvailability['start'] ?? '09:00',
                    'end' => $dayAvailability['end'] ?? '17:00'
                ];
            }
            // If explicitly set to not available
            if (isset($dayAvailability['available']) && !$dayAvailability['available']) {
                return [
                    'available' => false,
                    'start' => null,
                    'end' => null
                ];
            }
        }

        // Default working hours if not set (Monday-Friday)
        $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        if (in_array($dayName, $weekdays)) {
            return [
                'available' => true,
                'start' => '09:00',
                'end' => '17:00'
            ];
        }

        // Weekend - not available by default
        return [
            'available' => false,
            'start' => null,
            'end' => null
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
        // Check if slot is in the past
        if ($slotStart->isPast()) {
            return false;
        }

        // Check if slot conflicts with existing appointments
        foreach ($existingAppointments as $appointment) {
            $apptStart = Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time->format('H:i:s'));
            $apptEnd = $apptStart->copy()->addMinutes(30); // Default appointment duration

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
