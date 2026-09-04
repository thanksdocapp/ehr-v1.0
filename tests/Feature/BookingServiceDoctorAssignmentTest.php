<?php

namespace Tests\Feature;

use App\Models\BookingService;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorServicePrice;
use App\Models\User;
use App\Services\BookingServiceDoctorAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class BookingServiceDoctorAssignmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_create_syncs_selected_doctors_and_auto_assigns_creating_doctor(): void
    {
        $department = Department::create([
            'name' => 'Assign Clinic',
            'slug' => 'assign-clinic-' . uniqid(),
            'is_active' => true,
        ]);

        $primary = $this->makeDoctor('primary', $department);
        $colleague = $this->makeDoctor('colleague', $department);

        $service = BookingService::create([
            'name' => 'Shared Service',
            'default_duration_minutes' => 30,
            'default_consultation_type' => 'in_person',
            'default_price' => 100,
            'is_active' => true,
        ]);

        $request = Request::create('/', 'POST', [
            'assigned_doctor_ids' => [(string) $colleague->id],
            'created_for_doctor_id' => (string) $primary->id,
            'default_consultation_type' => 'online',
        ]);

        app(BookingServiceDoctorAssignmentService::class)->syncFromAdminRequest($request, $service);

        $this->assertTrue($service->isAvailableForDoctor($primary->id));
        $this->assertTrue($service->isAvailableForDoctor($colleague->id));

        $primaryRow = DoctorServicePrice::query()
            ->where('service_id', $service->id)
            ->where('doctor_id', $primary->id)
            ->first();
        $colleagueRow = DoctorServicePrice::query()
            ->where('service_id', $service->id)
            ->where('doctor_id', $colleague->id)
            ->first();

        $this->assertNotNull($primaryRow);
        $this->assertNotNull($colleagueRow);
        $this->assertSame('online', $primaryRow->consultation_type);
        $this->assertSame('online', $colleagueRow->consultation_type);
    }

    /** @test */
    public function staff_edit_can_assign_colleagues_in_same_clinic(): void
    {
        $department = Department::create([
            'name' => 'Staff Assign Clinic',
            'slug' => 'staff-assign-' . uniqid(),
            'is_active' => true,
        ]);

        $owner = $this->makeDoctor('owner', $department);
        $colleague = $this->makeDoctor('colleague-two', $department);

        $service = BookingService::create([
            'name' => 'Owner Service',
            'default_duration_minutes' => 45,
            'default_consultation_type' => 'in_person',
            'default_price' => 80,
            'created_by' => $owner->user_id,
            'is_active' => true,
        ]);

        DoctorServicePrice::create([
            'doctor_id' => $owner->id,
            'service_id' => $service->id,
            'custom_price' => 80,
            'custom_duration_minutes' => 45,
            'consultation_type' => 'in_person',
            'is_active' => true,
        ]);

        $request = Request::create('/', 'PUT', [
            'assigned_doctor_ids' => [(string) $colleague->id],
            'default_price' => 80,
            'custom_duration_minutes' => 45,
            'consultation_type' => 'in_person',
            'is_active' => '1',
            'doctor_assignments' => [
                (string) $colleague->id => [
                    'consultation_type' => 'telephone',
                    'is_active' => '1',
                ],
            ],
        ]);

        app(BookingServiceDoctorAssignmentService::class)->syncFromStaffRequest($request, $service, $owner);

        $this->assertDatabaseHas('doctor_service_prices', [
            'doctor_id' => $owner->id,
            'service_id' => $service->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('doctor_service_prices', [
            'doctor_id' => $colleague->id,
            'service_id' => $service->id,
            'consultation_type' => 'telephone',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function removing_doctor_assignment_deletes_pivot_row_on_edit(): void
    {
        $department = Department::create([
            'name' => 'Remove Assign Clinic',
            'slug' => 'remove-assign-' . uniqid(),
            'is_active' => true,
        ]);

        $doctorA = $this->makeDoctor('doctor-a', $department);
        $doctorB = $this->makeDoctor('doctor-b', $department);

        $service = BookingService::create([
            'name' => 'Removable Service',
            'default_duration_minutes' => 30,
            'default_consultation_type' => 'in_person',
            'default_price' => 50,
            'is_active' => true,
        ]);

        DoctorServicePrice::create([
            'doctor_id' => $doctorA->id,
            'service_id' => $service->id,
            'is_active' => true,
            'consultation_type' => 'in_person',
        ]);
        DoctorServicePrice::create([
            'doctor_id' => $doctorB->id,
            'service_id' => $service->id,
            'is_active' => true,
            'consultation_type' => 'in_person',
        ]);

        $request = Request::create('/', 'PUT', [
            'assigned_doctor_ids' => [(string) $doctorA->id],
            'default_consultation_type' => 'in_person',
        ]);

        app(BookingServiceDoctorAssignmentService::class)->syncFromAdminRequest($request, $service);

        $this->assertDatabaseHas('doctor_service_prices', [
            'doctor_id' => $doctorA->id,
            'service_id' => $service->id,
        ]);
        $this->assertDatabaseMissing('doctor_service_prices', [
            'doctor_id' => $doctorB->id,
            'service_id' => $service->id,
        ]);
    }

    private function makeDoctor(string $suffix, Department $department): Doctor
    {
        $user = User::factory()->create([
            'name' => 'Doctor ' . $suffix,
            'email' => $suffix . '-' . uniqid() . '@example.com',
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'title' => 'Dr.',
            'first_name' => 'Test',
            'last_name' => $suffix,
            'slug' => 'doctor-' . $suffix . '-' . uniqid(),
            'specialization' => 'General',
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $doctor->departments()->sync([$department->id => ['is_primary' => true]]);

        return $doctor;
    }
}
