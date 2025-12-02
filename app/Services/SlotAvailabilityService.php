<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\BookingService;
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

        // Get blocked times
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
        }

        // Default working hours if not set
        return [
            'available' => true,
            'start' => '09:00',
            'end' => '17:00'
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
                    $blocked[] = [
                        'start' => $break['start'] ?? null,
                        'end' => $break['end'] ?? null
                    ];
                }
            }
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
}

