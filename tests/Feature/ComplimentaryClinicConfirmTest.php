<?php

namespace Tests\Feature;

use App\Models\BookingService;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorServicePrice;
use App\Models\Setting;
use App\Services\SlotAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplimentaryClinicConfirmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::updateOrCreate(
            ['key' => 'public_booking_enabled'],
            ['value' => '1']
        );
    }

    /** @test */
    public function complimentary_clinic_confirm_returns_success_redirect_when_doctors_are_free(): void
    {
        $department = Department::create([
            'name' => 'Complimentary Clinic',
            'slug' => 'complimentary-clinic-' . uniqid(),
            'is_active' => true,
        ]);

        $service = BookingService::create([
            'name' => 'Free At Clinic Service',
            'default_duration_minutes' => 30,
            'default_price' => 50,
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Comp',
            'last_name' => 'Doctor',
            'slug' => 'comp-doc-' . uniqid(),
            'specialization' => 'General',
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        DoctorServicePrice::create([
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'custom_price' => 0,
            'custom_duration_minutes' => 30,
            'consultation_type' => 'in_person',
            'is_active' => true,
        ]);

        $appointmentDate = now()->addWeek()->toDateString();

        $this->mock(SlotAvailabilityService::class, function ($mock) use ($appointmentDate, $department, $service): void {
            $mock->shouldReceive('getAvailableSlotsForDepartment')
                ->with($department->id, $appointmentDate, $service->id)
                ->andReturn([['start' => '10:00', 'end' => '10:30', 'display' => '10:00 AM']]);
        });

        $response = $this->postJson(route('public.booking.clinic-confirm'), [
            'department_id' => $department->id,
            'service_id' => $service->id,
            'appointment_date' => $appointmentDate,
            'appointment_time' => '10:00',
            'first_name' => 'Pat',
            'last_name' => 'Free',
            'email' => 'pat-free-' . uniqid() . '@example.com',
            'phone' => '07000000004',
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
            'consultation_type' => 'in_person',
            'notes' => 'Complimentary booking test',
            'address' => '1 Test Street',
            'city' => 'London',
            'postal_code' => 'SW1A 1AA',
            'country' => 'United Kingdom',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['redirect']);

        $this->assertStringContainsString('/book/clinic-success/', $response->json('redirect'));
        $this->assertStringNotContainsString('/pay/', $response->json('redirect'));
    }
}
