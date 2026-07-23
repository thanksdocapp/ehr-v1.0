<?php

namespace Tests\Feature;

use App\Models\ClinicBookingRequest;
use App\Models\Department;
use App\Models\Doctor;
use App\Services\PostBookingRedirectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicPostBookingRedirectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function clinic_redirect_falls_back_to_another_doctor_when_assignee_has_no_thank_you_url(): void
    {
        $department = Department::create([
            'name' => 'ThanksDoc Clinic',
            'slug' => 'thanksdoc-clinic-' . uniqid(),
            'is_active' => true,
        ]);

        $assignee = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Assigned',
            'last_name' => 'NoUrl',
            'slug' => 'assigned-' . uniqid(),
            'specialization' => 'General',
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $tracker = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Tracker',
            'last_name' => 'HasUrl',
            'slug' => 'tracker-' . uniqid(),
            'specialization' => 'General',
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
            'department_id' => $department->id,
            'is_active' => true,
            'clinic_post_booking_redirect_url' => 'https://example.com/clinic-thank-you',
        ]);

        $request = ClinicBookingRequest::create([
            'request_number' => ClinicBookingRequest::generateRequestNumber(),
            'department_id' => $department->id,
            'doctor_id' => $assignee->id,
            'appointment_date' => now()->addWeek()->toDateString(),
            'appointment_time' => '11:30:00',
            'fee' => 0,
            'patient_data' => ['email' => 'patient@example.com'],
            'status' => 'accepted',
        ]);

        $service = app(PostBookingRedirectService::class);
        $doctor = $service->resolveDoctorForClinicBookingRedirect($request);
        $url = $service->buildRedirectUrlForClinicBookingRequest($request);

        $this->assertNotNull($doctor);
        $this->assertSame($tracker->id, $doctor->id);
        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://example.com/clinic-thank-you', $url);
        $this->assertStringContainsString('checkout_status=complimentary', $url);
    }

    /** @test */
    public function redirect_finds_url_on_sibling_doctor_row_for_same_user(): void
    {
        $department = Department::create([
            'name' => 'Sibling Clinic',
            'slug' => 'sibling-clinic-' . uniqid(),
            'is_active' => true,
        ]);

        $sharedUserId = 9001;

        $bookingRow = Doctor::create([
            'user_id' => $sharedUserId,
            'title' => 'Dr.',
            'first_name' => 'td',
            'last_name' => '1',
            'slug' => 'td-1-' . uniqid(),
            'specialization' => 'General',
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        Doctor::create([
            'user_id' => $sharedUserId,
            'title' => 'Dr.',
            'first_name' => 'td',
            'last_name' => 'profile',
            'slug' => 'td-profile-' . uniqid(),
            'specialization' => 'General',
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
            'department_id' => $department->id,
            'is_active' => true,
            'post_booking_redirect_url' => 'https://example.com/shared-thank-you',
            'clinic_post_booking_redirect_url' => 'https://example.com/shared-thank-you',
        ]);

        $request = ClinicBookingRequest::create([
            'request_number' => ClinicBookingRequest::generateRequestNumber(),
            'department_id' => $department->id,
            'doctor_id' => $bookingRow->id,
            'appointment_date' => now()->addWeek()->toDateString(),
            'appointment_time' => '11:30:00',
            'fee' => 0,
            'patient_data' => ['email' => 'patient@example.com'],
            'status' => 'accepted',
        ]);

        $url = app(PostBookingRedirectService::class)->buildRedirectUrlForClinicBookingRequest($request);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://example.com/shared-thank-you', $url);
    }
}
