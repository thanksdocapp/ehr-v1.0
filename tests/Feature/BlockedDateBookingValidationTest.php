<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorAvailabilityException;
use App\Models\DoctorAvailabilityRule;
use App\Services\SlotAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BlockedDateBookingValidationTest extends TestCase
{
    use RefreshDatabase;

    protected SlotAvailabilityService $slotService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->slotService = app(SlotAvailabilityService::class);
        config(['booking.modality_rules_enabled' => true]);
    }

    /** @test */
    public function all_day_blocked_date_returns_no_slots(): void
    {
        $doctor = $this->makeDoctor('blocked');
        $date = $this->futureMonday();

        $this->makeRule($doctor, 'monday', DoctorAvailabilityRule::MODALITY_ALL);

        DoctorAvailabilityException::create([
            'doctor_id' => $doctor->id,
            'exception_date' => $date->toDateString(),
            'type' => 'blocked',
            'is_all_day' => true,
        ]);

        $slots = $this->slotService->getAvailableSlots($doctor->id, $date->toDateString());

        $this->assertSame([], $slots);
        $this->assertTrue($this->slotService->isDateBlocked($doctor->id, $date->toDateString()));
    }

    /** @test */
    public function assert_slot_bookable_rejects_all_day_blocked_date(): void
    {
        $doctor = $this->makeDoctor('reject');
        $date = $this->futureMonday();

        $this->makeRule($doctor, 'monday', DoctorAvailabilityRule::MODALITY_ALL);

        DoctorAvailabilityException::create([
            'doctor_id' => $doctor->id,
            'exception_date' => $date->toDateString(),
            'type' => 'blocked',
            'is_all_day' => true,
        ]);

        $this->expectException(ValidationException::class);

        $this->slotService->assertSlotBookable(
            $doctor->id,
            $date->toDateString(),
            '10:00'
        );
    }

    /** @test */
    public function partial_day_block_removes_only_blocked_interval(): void
    {
        $doctor = $this->makeDoctor('partial');
        $date = $this->futureMonday();

        $this->makeRule($doctor, 'monday', DoctorAvailabilityRule::MODALITY_ALL);

        DoctorAvailabilityException::create([
            'doctor_id' => $doctor->id,
            'exception_date' => $date->toDateString(),
            'type' => 'blocked',
            'is_all_day' => false,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        $slots = $this->slotService->getAvailableSlots($doctor->id, $date->toDateString());
        $starts = collect($slots)->pluck('start')->all();

        $this->assertNotContains('10:00', $starts);
        $this->assertContains('11:00', $starts);
        $this->assertFalse($this->slotService->isSlotBookable($doctor->id, $date->toDateString(), '10:00'));
        $this->assertTrue($this->slotService->isSlotBookable($doctor->id, $date->toDateString(), '11:00'));
    }

    /** @test */
    public function weekly_closed_day_overrides_stale_modality_rules(): void
    {
        $doctor = $this->makeDoctor('closed');
        $date = $this->futureMonday();

        $doctor->update([
            'availability' => [
                'monday' => [
                    'available' => false,
                    'sessions' => [
                        ['start' => '09:00', 'end' => '17:00'],
                    ],
                ],
            ],
        ]);

        $this->makeRule($doctor, 'monday', DoctorAvailabilityRule::MODALITY_ALL);

        $slots = $this->slotService->getAvailableSlots($doctor->id, $date->toDateString());

        $this->assertSame([], $slots);
    }

    /** @test */
    public function clinic_union_still_shows_other_doctor_when_one_is_blocked(): void
    {
        $blockedDoctor = $this->makeDoctor('blocked-clinic');
        $workingDoctor = $this->makeDoctor('working-clinic');
        $department = \App\Models\Department::create([
            'name' => 'Test Clinic',
            'slug' => 'test-clinic-block-' . uniqid(),
            'description' => 'Blocked date clinic test',
        ]);

        $blockedDoctor->update(['department_id' => $department->id]);
        $workingDoctor->update(['department_id' => $department->id]);

        $date = $this->futureMonday();

        $this->makeRule($blockedDoctor, 'monday', DoctorAvailabilityRule::MODALITY_ALL);
        $this->makeRule($workingDoctor, 'monday', DoctorAvailabilityRule::MODALITY_ALL);

        DoctorAvailabilityException::create([
            'doctor_id' => $blockedDoctor->id,
            'exception_date' => $date->toDateString(),
            'type' => 'blocked',
            'is_all_day' => true,
        ]);

        $blockedSlots = $this->slotService->getAvailableSlots($blockedDoctor->id, $date->toDateString());
        $clinicSlots = $this->slotService->getAvailableSlotsForDepartment($department->id, $date->toDateString());

        $this->assertSame([], $blockedSlots);
        $this->assertNotEmpty($clinicSlots);
    }

    /** @test */
    public function department_calendar_is_empty_when_all_doctors_blocked(): void
    {
        $department = \App\Models\Department::create([
            'name' => 'Test Clinic',
            'slug' => 'dept-blocked-' . uniqid(),
            'description' => 'Test',
        ]);

        $doctorA = $this->makeDoctor('dept-a');
        $doctorB = $this->makeDoctor('dept-b');
        $doctorA->update(['department_id' => $department->id]);
        $doctorB->update(['department_id' => $department->id]);

        $date = $this->futureMonday();

        $this->makeRule($doctorA, 'monday', DoctorAvailabilityRule::MODALITY_ALL);
        $this->makeRule($doctorB, 'monday', DoctorAvailabilityRule::MODALITY_ALL);

        foreach ([$doctorA, $doctorB] as $doctor) {
            DoctorAvailabilityException::create([
                'doctor_id' => $doctor->id,
                'exception_date' => $date->toDateString(),
                'type' => 'blocked',
                'is_all_day' => true,
            ]);
        }

        $this->assertTrue($this->slotService->isDepartmentFullyBlocked($department->id, $date->toDateString()));
        $this->assertSame([], $this->slotService->getAvailableSlotsForDepartment($department->id, $date->toDateString()));
    }

    /** @test */
    public function department_calendar_can_still_show_slots_when_one_doctor_is_blocked(): void
    {
        $department = \App\Models\Department::create([
            'name' => 'Mixed Clinic',
            'slug' => 'dept-mixed-' . uniqid(),
            'description' => 'Test',
        ]);

        $blockedDoctor = $this->makeDoctor('blocked-one');
        $workingDoctor = $this->makeDoctor('working-one');
        $blockedDoctor->update(['department_id' => $department->id]);
        $workingDoctor->update(['department_id' => $department->id]);

        $date = $this->futureMonday();

        $this->makeRule($blockedDoctor, 'monday', DoctorAvailabilityRule::MODALITY_ALL);
        $this->makeRule($workingDoctor, 'monday', DoctorAvailabilityRule::MODALITY_ALL);

        DoctorAvailabilityException::create([
            'doctor_id' => $blockedDoctor->id,
            'exception_date' => $date->toDateString(),
            'type' => 'blocked',
            'is_all_day' => true,
        ]);

        $this->assertFalse($this->slotService->isDepartmentFullyBlocked($department->id, $date->toDateString()));
        $this->assertNotEmpty($this->slotService->getAvailableSlotsForDepartment($department->id, $date->toDateString()));
    }

    /** @test */
    public function resolve_doctor_for_clinic_slot_skips_blocked_primary_doctor(): void
    {
        $department = \App\Models\Department::create([
            'name' => 'Routing Clinic',
            'slug' => 'route-clinic-' . uniqid(),
            'description' => 'Test',
        ]);

        $blockedPrimary = $this->makeDoctor('primary-blocked');
        $workingDoctor = $this->makeDoctor('working-alt');
        $blockedPrimary->update(['department_id' => $department->id, 'is_active' => true]);
        $workingDoctor->update(['department_id' => $department->id, 'is_active' => true]);

        $blockedPrimary->departments()->sync([
            $department->id => ['is_primary' => true],
        ]);
        $workingDoctor->departments()->sync([
            $department->id => ['is_primary' => false],
        ]);

        $date = $this->futureMonday();

        $this->makeRule($blockedPrimary, 'monday', DoctorAvailabilityRule::MODALITY_ALL);
        $this->makeRule($workingDoctor, 'monday', DoctorAvailabilityRule::MODALITY_ALL);

        DoctorAvailabilityException::create([
            'doctor_id' => $blockedPrimary->id,
            'exception_date' => $date->toDateString(),
            'type' => 'blocked',
            'is_all_day' => true,
        ]);

        $clinicService = app(\App\Services\ClinicBookingService::class);

        $resolved = $clinicService->resolveDoctorForClinicSlot(
            $department->id,
            $date->toDateString(),
            '10:00'
        );

        $this->assertNotNull($resolved);
        $this->assertSame($workingDoctor->id, $resolved->id);
        $this->assertFalse($clinicService->canDoctorAcceptClinicRequest($blockedPrimary, new \App\Models\ClinicBookingRequest([
            'department_id' => $department->id,
            'appointment_date' => $date->toDateString(),
            'appointment_time' => '10:00:00',
            'consultation_type' => 'in_person',
        ])));
    }

    /** @test */
    public function doctors_available_for_clinic_slot_excludes_blocked_doctor(): void
    {
        $department = \App\Models\Department::create([
            'name' => 'Available Clinic',
            'slug' => 'avail-clinic-' . uniqid(),
            'description' => 'Test',
        ]);

        $blockedDoctor = $this->makeDoctor('blocked-available');
        $workingDoctor = $this->makeDoctor('working-available');
        $blockedDoctor->update(['department_id' => $department->id]);
        $workingDoctor->update(['department_id' => $department->id]);

        $date = $this->futureMonday();

        $this->makeRule($blockedDoctor, 'monday', DoctorAvailabilityRule::MODALITY_ALL);
        $this->makeRule($workingDoctor, 'monday', DoctorAvailabilityRule::MODALITY_ALL);

        DoctorAvailabilityException::create([
            'doctor_id' => $blockedDoctor->id,
            'exception_date' => $date->toDateString(),
            'type' => 'blocked',
            'is_all_day' => true,
        ]);

        $available = $this->slotService->doctorsAvailableForClinicSlot(
            $department->id,
            $date->toDateString(),
            '10:00'
        );

        $this->assertCount(1, $available);
        $this->assertSame($workingDoctor->id, $available->first()->id);
    }

    private function futureMonday(): Carbon
    {
        return Carbon::now()->addWeek()->startOfWeek(Carbon::MONDAY);
    }

    private function makeDoctor(string $suffix): Doctor
    {
        return Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Block',
            'last_name' => $suffix,
            'slug' => 'block-' . $suffix . '-' . uniqid(),
            'specialization' => 'General',
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
        ]);
    }

    private function makeRule(Doctor $doctor, string $day, string $modality): DoctorAvailabilityRule
    {
        return $doctor->availabilityRules()->create([
            'day_of_week' => $day,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'modality' => $modality,
            'is_active' => true,
            'needs_review' => false,
            'source' => 'manual',
        ]);
    }
}
