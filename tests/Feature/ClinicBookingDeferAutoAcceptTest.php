<?php

namespace Tests\Feature;

use App\Models\BookingService;
use App\Models\ClinicBookingRequest;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorServicePrice;
use App\Services\ClinicBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ClinicBookingDeferAutoAcceptTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function create_from_clinic_booking_defers_auto_accept_during_http_request(): void
    {
        $department = Department::create([
            'name' => 'Defer Clinic',
            'slug' => 'defer-clinic-' . uniqid(),
            'is_active' => true,
        ]);

        $service = BookingService::create([
            'name' => 'Deferred Service',
            'default_duration_minutes' => 30,
            'default_price' => 0,
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Defer',
            'last_name' => 'Doctor',
            'slug' => 'defer-doc-' . uniqid(),
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

        $acceptCalledDuringRequest = false;
        $serviceMock = Mockery::mock(ClinicBookingService::class)->makePartial();
        $serviceMock->shouldReceive('acceptRequest')->andReturnUsing(function () use (&$acceptCalledDuringRequest) {
            $acceptCalledDuringRequest = true;

            throw new \RuntimeException('acceptRequest should not run during the HTTP request');
        });
        $this->app->instance(ClinicBookingService::class, $serviceMock);

        $request = $serviceMock->createFromClinicBooking([
            'department_id' => $department->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addWeek()->toDateString(),
            'appointment_time' => '10:00',
            'first_name' => 'Pat',
            'last_name' => 'Defer',
            'email' => 'pat-defer-' . uniqid() . '@example.com',
            'phone' => '07000000002',
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
            'consultation_type' => 'in_person',
            'notes' => 'Deferred auto-accept test',
            'address' => '1 Test Street',
            'city' => 'London',
            'postal_code' => 'SW1A 1AA',
            'country' => 'United Kingdom',
        ], deferAutoAccept: true);

        $this->assertInstanceOf(ClinicBookingRequest::class, $request);
        $this->assertSame('pending_acceptance', $request->status);
        $this->assertFalse($acceptCalledDuringRequest);
    }
}
