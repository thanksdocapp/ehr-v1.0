<?php

namespace Tests\Feature;

use App\Http\Middleware\RequireTwoFactor;
use App\Models\BookingService;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorServicePrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DoctorServiceActiveCheckboxTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function doctor_can_create_inactive_non_consultation_service(): void
    {
        [$doctorUser, $doctor] = $this->createDoctorUser();

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($doctorUser);

        $response = $this->post(route('staff.doctor-services.store'), [
            'name' => 'Standalone Health Package',
            'description' => 'Paid upfront, no appointment slot',
            'is_non_consultation' => '1',
            'is_active' => '0',
            'default_price' => 199.00,
        ]);

        $response->assertRedirect(route('staff.doctor-services.index'));

        $service = BookingService::query()->where('name', 'Standalone Health Package')->first();
        $this->assertNotNull($service);
        $this->assertTrue($service->is_non_consultation);
        $this->assertFalse($service->is_active);

        $override = DoctorServicePrice::query()
            ->where('doctor_id', $doctor->id)
            ->where('service_id', $service->id)
            ->first();

        $this->assertNotNull($override);
        $this->assertFalse($override->is_active);
    }

    /** @test */
    public function doctor_can_deactivate_existing_service_on_update(): void
    {
        [$doctorUser, $doctor] = $this->createDoctorUser();

        $service = BookingService::create([
            'name' => 'Active Standalone Package',
            'description' => 'Initially active',
            'default_duration_minutes' => 30,
            'default_price' => 99.00,
            'created_by' => $doctorUser->id,
            'is_active' => true,
            'is_non_consultation' => true,
        ]);

        DoctorServicePrice::create([
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'custom_price' => 99.00,
            'custom_duration_minutes' => 30,
            'consultation_type' => 'in_person',
            'is_active' => true,
        ]);

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($doctorUser);

        $response = $this->put(route('staff.doctor-services.update', $service), [
            'name' => 'Active Standalone Package',
            'default_price' => 99.00,
            'description' => 'Now inactive',
            'is_non_consultation' => '1',
            'is_active' => '0',
            'custom_duration_minutes' => 30,
        ]);

        $response->assertRedirect(route('staff.doctor-services.index'));

        $service->refresh();
        $override = DoctorServicePrice::query()
            ->where('doctor_id', $doctor->id)
            ->where('service_id', $service->id)
            ->first();

        $this->assertFalse($service->is_active);
        $this->assertNotNull($override);
        $this->assertFalse($override->is_active);
    }

    /**
     * @return array{0: User, 1: Doctor}
     */
    private function createDoctorUser(): array
    {
        $department = Department::create([
            'name' => 'Standalone Service Clinic',
            'slug' => 'standalone-service-clinic-' . uniqid(),
            'description' => 'Test clinic',
            'is_active' => true,
        ]);

        $doctorUser = User::create([
            'name' => 'Standalone Service Doctor',
            'email' => 'standalone-service-doctor-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'is_admin' => false,
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Standalone',
            'last_name' => 'Doctor',
            'slug' => 'standalone-service-doc-' . uniqid(),
            'specialization' => 'General',
            'user_id' => $doctorUser->id,
            'department_id' => $department->id,
            'bio' => 'Test',
            'qualification' => 'MBBS',
            'experience_years' => 5,
            'email' => 'standalone-service-profile-' . uniqid() . '@example.com',
        ]);

        return [$doctorUser, $doctor];
    }
}
