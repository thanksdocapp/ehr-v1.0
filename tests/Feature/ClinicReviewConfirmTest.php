<?php

namespace Tests\Feature;

use App\Models\BookingService;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorServicePrice;
use App\Models\Setting;
use App\Services\ClinicBookingService;
use App\Services\SlotAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicReviewConfirmTest extends TestCase
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
    public function clinic_review_shows_session_error_after_confirm_failure(): void
    {
        $department = Department::create([
            'name' => 'Review Error Clinic',
            'slug' => 'review-error-clinic-' . uniqid(),
            'is_active' => true,
        ]);

        $service = BookingService::create([
            'name' => 'Free Clinic Service',
            'default_duration_minutes' => 30,
            'default_price' => 0,
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Review',
            'last_name' => 'Doctor',
            'slug' => 'review-doc-' . uniqid(),
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
        $sessionData = [
            'department_id' => $department->id,
            'service_id' => $service->id,
            'appointment_date' => $appointmentDate,
            'appointment_time' => '10:00',
            'patient_data' => [
                'first_name' => 'Pat',
                'last_name' => 'Test',
                'email' => 'pat-review-' . uniqid() . '@example.com',
                'phone' => '07000000001',
                'date_of_birth' => '1990-01-01',
                'gender' => 'female',
                'consultation_type' => 'in_person',
                'notes' => 'Test booking reason',
                'address' => '1 Test Street',
                'city' => 'London',
                'postal_code' => 'SW1A 1AA',
                'country' => 'United Kingdom',
            ],
            'price' => 0,
        ];

        $availableSlot = [['start' => '10:00', 'end' => '10:30', 'display' => '10:00 AM']];

        $this->mock(SlotAvailabilityService::class, function ($mock) use ($appointmentDate, $department, $service, $availableSlot): void {
            $mock->shouldReceive('getAvailableSlotsForDepartment')
                ->with($department->id, $appointmentDate, $service->id)
                ->andReturn($availableSlot);
        });

        $this->mock(ClinicBookingService::class, function ($mock): void {
            $mock->shouldReceive('createFromClinicBooking')
                ->once()
                ->andThrow(new \RuntimeException('Simulated booking failure'));
        });

        $this->withSession(['clinic_booking_review' => $sessionData])
            ->post(route('public.booking.clinic-confirm'), [
                'department_id' => $department->id,
                'service_id' => $service->id,
                'appointment_date' => $appointmentDate,
                'appointment_time' => '10:00',
                'first_name' => 'Pat',
                'last_name' => 'Test',
                'email' => $sessionData['patient_data']['email'],
                'phone' => '07000000001',
                'date_of_birth' => '1990-01-01',
                'gender' => 'female',
                'consultation_type' => 'in_person',
                'notes' => 'Test booking reason',
                'address' => '1 Test Street',
                'city' => 'London',
                'postal_code' => 'SW1A 1AA',
                'country' => 'United Kingdom',
            ])
            ->assertRedirect(route('public.booking.clinic-review.show'))
            ->assertSessionHas('error', 'Failed to submit booking request. Please try again.');

        $this->get(route('public.booking.clinic-review.show'))
            ->assertOk()
            ->assertSee('Failed to submit booking request. Please try again.', false);
    }

    /** @test */
    public function direct_clinic_review_url_without_session_shows_expired_page(): void
    {
        $this->get(route('public.booking.clinic-review.show'))
            ->assertOk()
            ->assertSee('Session expired', false);
    }
}
