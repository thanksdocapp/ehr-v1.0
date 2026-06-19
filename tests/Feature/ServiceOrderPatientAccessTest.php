<?php

namespace Tests\Feature;

use App\Http\Middleware\RequireTwoFactor;
use App\Models\BookingService;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ServiceOrderPatientAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function doctor_can_open_patient_linked_only_through_service_order(): void
    {
        $department = Department::create([
            'name' => 'Service Order Clinic',
            'slug' => 'service-order-clinic-' . uniqid(),
            'description' => 'Test clinic',
            'is_active' => true,
        ]);

        $doctorUser = User::create([
            'name' => 'Service Order Doctor',
            'email' => 'service-order-doctor-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'is_admin' => false,
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Service',
            'last_name' => 'Doctor',
            'slug' => 'service-order-doc-' . uniqid(),
            'specialization' => 'General',
            'user_id' => $doctorUser->id,
            'department_id' => $department->id,
            'bio' => 'Test',
            'qualification' => 'MBBS',
            'experience_years' => 5,
            'email' => 'doctor-profile-' . uniqid() . '@example.com',
        ]);

        $patient = Patient::create([
            'patient_id' => 'P-SO-' . uniqid(),
            'first_name' => 'Kevin',
            'last_name' => 'Wood',
            'email' => 'kevin-' . uniqid() . '@example.com',
            'phone' => '07758247109',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $service = BookingService::create([
            'name' => 'Comprehensive Health Package',
            'default_duration_minutes' => 30,
            'default_consultation_type' => 'in_person',
        ]);

        $order = ServiceOrder::create([
            'order_number' => 'SO-TEST-' . uniqid(),
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'service_id' => $service->id,
            'notes' => 'No allergies',
            'list_price' => 259.00,
            'discount_amount' => 0,
            'fee' => 259.00,
            'status' => ServiceOrder::STATUS_CONTACTED,
            'paid_at' => now(),
            'contacted_at' => now(),
        ]);

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($doctorUser);

        $this->assertTrue($patient->fresh()->isVisibleTo($doctorUser));

        $this->get(route('staff.service-orders.show', $order))
            ->assertOk()
            ->assertSee('Kevin Wood');

        $this->get(route('staff.patients.show', $patient))
            ->assertOk()
            ->assertSee('Kevin Wood');
    }
}
