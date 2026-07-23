<?php

namespace Tests\Feature;

use App\Models\BookingService;
use App\Models\ClinicBookingRequest;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorServicePrice;
use App\Models\Setting;
use App\Services\ClinicBookingService;
use App\Services\SlotAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ClinicConfirmJsonRedirectTest extends TestCase
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
    public function clinic_confirm_json_returns_internal_success_redirect_url(): void
    {
        $department = Department::create([
            'name' => 'JSON Clinic',
            'slug' => 'json-clinic-' . uniqid(),
            'is_active' => true,
        ]);

        $service = BookingService::create([
            'name' => 'Free JSON Service',
            'default_duration_minutes' => 30,
            'default_price' => 0,
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Json',
            'last_name' => 'Doctor',
            'slug' => 'json-doc-' . uniqid(),
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
                'last_name' => 'Json',
                'email' => 'pat-json-' . uniqid() . '@example.com',
                'phone' => '07000000003',
                'date_of_birth' => '1990-01-01',
                'gender' => 'female',
                'consultation_type' => 'in_person',
                'notes' => 'JSON redirect test',
                'address' => '1 Test Street',
                'city' => 'London',
                'postal_code' => 'SW1A 1AA',
                'country' => 'United Kingdom',
            ],
            'price' => 0,
        ];

        $this->mock(SlotAvailabilityService::class, function ($mock) use ($appointmentDate, $department, $service): void {
            $mock->shouldReceive('getAvailableSlotsForDepartment')
                ->with($department->id, $appointmentDate, $service->id)
                ->andReturn([['start' => '10:00', 'end' => '10:30', 'display' => '10:00 AM']]);
        });

        $createdRequest = null;
        $serviceMock = Mockery::mock(ClinicBookingService::class)->makePartial();
        $serviceMock->shouldReceive('createFromClinicBooking')->once()->andReturnUsing(function () use (&$createdRequest, $department, $service, $appointmentDate) {
            $createdRequest = ClinicBookingRequest::create([
                'request_number' => ClinicBookingRequest::generateRequestNumber(),
                'department_id' => $department->id,
                'service_id' => $service->id,
                'appointment_date' => $appointmentDate,
                'appointment_time' => '10:00:00',
                'fee' => 0,
                'patient_data' => ['email' => 'json@example.com'],
                'status' => 'pending_acceptance',
            ]);

            return $createdRequest;
        });
        $this->app->instance(ClinicBookingService::class, $serviceMock);

        $response = $this->withSession(['clinic_booking_review' => $sessionData])
            ->postJson(route('public.booking.clinic-confirm'), [
                'department_id' => $department->id,
                'service_id' => $service->id,
                'appointment_date' => $appointmentDate,
                'appointment_time' => '10:00',
                'first_name' => 'Pat',
                'last_name' => 'Json',
                'email' => 'json@example.com',
                'phone' => '07000000003',
                'date_of_birth' => '1990-01-01',
                'gender' => 'female',
                'consultation_type' => 'in_person',
                'notes' => 'JSON redirect test',
                'address' => '1 Test Street',
                'city' => 'London',
                'postal_code' => 'SW1A 1AA',
                'country' => 'United Kingdom',
            ]);

        $response->assertOk()
            ->assertJsonStructure(['redirect']);

        $this->assertStringContainsString(
            '/book/clinic-success/'.$createdRequest->request_number,
            $response->json('redirect')
        );
    }
}
