<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Patient;
use App\Models\BookingService;
use App\Services\SlotAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicCalendarSyncTest extends TestCase
{
    use RefreshDatabase;

    protected SlotAvailabilityService $slotService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->slotService = app(SlotAvailabilityService::class);
    }

    /** @test */
    public function slot_booked_for_doctor_a_is_blocked_for_doctor_b_in_same_department(): void
    {
        $department = Department::create([
            'name' => 'Test Department',
            'slug' => 'test-dept-sync-' . uniqid(),
            'description' => 'Test department for clinic sync',
        ]);

        $doctorA = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Alice',
            'last_name' => 'Sync',
            'slug' => 'alice-sync-' . uniqid(),
            'specialization' => 'General',
            'department_id' => $department->id,
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
        ]);

        $doctorB = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Bob',
            'last_name' => 'Sync',
            'slug' => 'bob-sync-' . uniqid(),
            'specialization' => 'General',
            'department_id' => $department->id,
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
        ]);

        $department->doctors()->attach([$doctorA->id => ['is_primary' => true], $doctorB->id => ['is_primary' => false]]);

        $patient = Patient::create([
            'patient_id' => 'P-SYNC-' . uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'email' => 'sync-test-' . uniqid() . '@example.com',
            'phone' => '07123456789',
            'password' => bcrypt('password'),
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
        ]);

        $service = BookingService::create([
            'name' => 'Consultation',
            'default_duration_minutes' => 30,
        ]);

        $date = now()->addDays(2)->format('Y-m-d');

        $slotsBefore = $this->slotService->getAvailableSlots($doctorB->id, $date, $service->id);
        $slot10am = collect($slotsBefore)->firstWhere('start', '10:00');
        $this->assertNotNull($slot10am, '10:00 slot should be available for Doctor B initially');

        Appointment::create([
            'appointment_number' => 'APT-' . uniqid(),
            'patient_id' => $patient->id,
            'doctor_id' => $doctorA->id,
            'department_id' => $department->id,
            'service_id' => $service->id,
            'appointment_date' => $date,
            'appointment_time' => '10:00:00',
            'type' => 'consultation',
            'status' => 'confirmed',
        ]);

        $slotsAfter = $this->slotService->getAvailableSlots($doctorB->id, $date, $service->id);
        $slot10amAfter = collect($slotsAfter)->firstWhere('start', '10:00');
        $this->assertNull($slot10amAfter, '10:00 slot should be blocked for Doctor B when Doctor A has appointment');
    }

    /** @test */
    public function department_slots_exclude_appointments_from_any_doctor(): void
    {
        $department = Department::create([
            'name' => 'Test Department 2',
            'slug' => 'test-dept-sync2-' . uniqid(),
            'description' => 'Test department for clinic sync',
        ]);

        $doctorA = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Carol',
            'last_name' => 'Sync',
            'slug' => 'carol-sync-' . uniqid(),
            'specialization' => 'General',
            'department_id' => $department->id,
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
        ]);

        $doctorB = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Dave',
            'last_name' => 'Sync',
            'slug' => 'dave-sync-' . uniqid(),
            'specialization' => 'General',
            'department_id' => $department->id,
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
        ]);

        $department->doctors()->attach([$doctorA->id => ['is_primary' => true], $doctorB->id => ['is_primary' => false]]);

        $patient = Patient::create([
            'patient_id' => 'P-SYNC2-' . uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'email' => 'sync-test2-' . uniqid() . '@example.com',
            'phone' => '07123456788',
            'password' => bcrypt('password'),
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
        ]);

        $service = BookingService::create([
            'name' => 'Consultation',
            'default_duration_minutes' => 30,
        ]);

        $date = now()->addDays(2)->format('Y-m-d');

        $deptSlotsBefore = $this->slotService->getAvailableSlotsForDepartment($department->id, $date, $service->id);
        $slot10am = collect($deptSlotsBefore)->firstWhere('start', '10:00');
        $this->assertNotNull($slot10am, '10:00 should be in department slots initially');

        Appointment::create([
            'appointment_number' => 'APT-' . uniqid(),
            'patient_id' => $patient->id,
            'doctor_id' => $doctorA->id,
            'department_id' => $department->id,
            'service_id' => $service->id,
            'appointment_date' => $date,
            'appointment_time' => '10:00:00',
            'type' => 'consultation',
            'status' => 'confirmed',
        ]);

        $deptSlotsAfter = $this->slotService->getAvailableSlotsForDepartment($department->id, $date, $service->id);
        $slot10amAfter = collect($deptSlotsAfter)->firstWhere('start', '10:00');
        $this->assertNull($slot10amAfter, '10:00 should be blocked in department slots when any doctor has appointment');
    }
}
