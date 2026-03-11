<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Patient;
use App\Models\BookingService;
use App\Services\SlotAvailabilityService;
use Illuminate\Console\Command;

class VerifyClinicCalendarSync extends Command
{
    protected $signature = 'clinic:verify-sync {--dry-run : Do not create test data, use existing}';

    protected $description = 'Verify clinic calendar sync: slot booked for one doctor is blocked for others in same department';

    public function handle(SlotAvailabilityService $slotService): int
    {
        $this->info('Verifying clinic calendar synchronization...');

        if ($this->option('dry-run')) {
            return $this->verifyWithExistingData($slotService);
        }

        return $this->verifyWithTestData($slotService);
    }

    private function verifyWithTestData(SlotAvailabilityService $slotService): int
    {
        $department = Department::first();
        if (!$department) {
            $this->error('No department found. Run seeders first (e.g. HospitalSampleDataSeeder).');
            return 1;
        }

        $doctors = Doctor::byDepartment($department->id)->active()->take(2)->get();
        if ($doctors->count() < 2) {
            $this->error("Department '{$department->name}' needs at least 2 doctors. Run seeders first.");
            return 1;
        }

        $patient = Patient::first();
        if (!$patient) {
            $this->error('No patient found. Run seeders first.');
            return 1;
        }

        $service = BookingService::first();
        if (!$service) {
            $this->error('No booking service found. Run seeders first.');
            return 1;
        }

        $date = now()->addDays(2)->format('Y-m-d');
        $doctorA = $doctors[0];
        $doctorB = $doctors[1];

        $this->line("Using: Department={$department->name}, Doctor A={$doctorA->full_name}, Doctor B={$doctorB->full_name}");
        $this->line("Date: {$date}, Service: {$service->name}");

        $slotsBefore = $slotService->getAvailableSlots($doctorB->id, $date, $service->id);
        $slot10am = collect($slotsBefore)->firstWhere('start', '10:00');

        if (!$slot10am) {
            $this->warn('10:00 slot not available for Doctor B before booking (may be past or blocked). Trying 11:00...');
            $slotToUse = collect($slotsBefore)->firstWhere('start', '11:00')
                ?? collect($slotsBefore)->first();
            if (!$slotToUse) {
                $this->error('No available slots for Doctor B. Doctor may have no availability on this day.');
                return 1;
            }
            $targetTime = $slotToUse['start'];
        } else {
            $targetTime = '10:00';
        }

        $this->line("Creating appointment for Doctor A at {$targetTime}...");

        $appointment = Appointment::create([
            'appointment_number' => 'APT-VERIFY-' . uniqid(),
            'patient_id' => $patient->id,
            'doctor_id' => $doctorA->id,
            'department_id' => $department->id,
            'service_id' => $service->id,
            'appointment_date' => $date,
            'appointment_time' => $targetTime . ':00',
            'type' => 'consultation',
            'status' => 'confirmed',
        ]);

        $slotsAfter = $slotService->getAvailableSlots($doctorB->id, $date, $service->id);
        $slotAfter = collect($slotsAfter)->firstWhere('start', $targetTime);

        $deptSlots = $slotService->getAvailableSlotsForDepartment($department->id, $date, $service->id);
        $deptSlotAfter = collect($deptSlots)->firstWhere('start', $targetTime);

        $appointment->delete();

        $doctorBlocked = $slotAfter === null;
        $deptBlocked = $deptSlotAfter === null;

        if ($doctorBlocked && $deptBlocked) {
            $this->info('PASS: Clinic calendar sync is working correctly.');
            $this->line("  - Slot {$targetTime} blocked for Doctor B when Doctor A has appointment.");
            $this->line("  - Slot {$targetTime} blocked in department slots.");
            return 0;
        }

        $this->error('FAIL: Clinic calendar sync may not be working correctly.');
        if (!$doctorBlocked) {
            $this->line("  - Slot {$targetTime} was NOT blocked for Doctor B (expected: blocked).");
        }
        if (!$deptBlocked) {
            $this->line("  - Slot {$targetTime} was NOT blocked in department slots (expected: blocked).");
        }
        return 1;
    }

    private function verifyWithExistingData(SlotAvailabilityService $slotService): int
    {
        $department = Department::first();
        if (!$department) {
            $this->error('No department found.');
            return 1;
        }

        $appointments = Appointment::with('doctor')
            ->whereHas('doctor', fn($q) => $q->byDepartment($department->id))
            ->whereDate('appointment_date', '>=', now())
            ->whereIn('status', ['pending', 'confirmed', 'rescheduled'])
            ->take(1)
            ->get();

        if ($appointments->isEmpty()) {
            $this->warn('No upcoming appointments in this department. Run without --dry-run to create test data.');
            return 0;
        }

        $apt = $appointments->first();
        $date = $apt->appointment_date->format('Y-m-d');
        $time = $apt->appointment_time instanceof \DateTimeInterface
            ? $apt->appointment_time->format('H:i')
            : substr((string) $apt->appointment_time, 0, 5);

        $otherDoctors = Doctor::byDepartment($department->id)
            ->where('id', '!=', $apt->doctor_id)
            ->active()
            ->get();

        if ($otherDoctors->isEmpty()) {
            $this->warn('No other doctors in department to verify against.');
            return 0;
        }

        $service = $apt->service_id ? BookingService::find($apt->service_id) : BookingService::first();
        $serviceId = $service?->id;

        $allBlocked = true;
        foreach ($otherDoctors as $other) {
            $slots = $slotService->getAvailableSlots($other->id, $date, $serviceId);
            $slot = collect($slots)->firstWhere('start', $time);
            if ($slot !== null) {
                $this->line("  - Slot {$time} NOT blocked for {$other->full_name} (expected: blocked)");
                $allBlocked = false;
            }
        }

        $deptSlots = $slotService->getAvailableSlotsForDepartment($department->id, $date, $serviceId);
        $deptSlot = collect($deptSlots)->firstWhere('start', $time);
        if ($deptSlot !== null) {
            $this->line("  - Slot {$time} NOT blocked in department slots (expected: blocked)");
            $allBlocked = false;
        }

        if ($allBlocked) {
            $this->info('PASS: Existing appointment at ' . $time . ' is correctly blocking slots for other doctors.');
            return 0;
        }

        $this->error('FAIL: Some doctors or department still show the slot as available.');
        return 1;
    }
}
