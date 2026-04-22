<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BookingService;
use App\Models\ClinicBookingRequest;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PublicBookingCalendarInviteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function signed_appointment_calendar_invite_downloads_as_ics(): void
    {
        $department = Department::create([
            'name' => 'Calendar Dept',
            'slug' => 'calendar-dept-'.uniqid(),
            'description' => 'Calendar test department',
        ]);

        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Cal',
            'last_name' => 'Doctor',
            'slug' => 'cal-doc-'.uniqid(),
            'specialization' => 'General',
            'department_id' => $department->id,
            'bio' => 'Test',
            'qualification' => 'MBBS',
            'experience_years' => 7,
        ]);

        $patient = Patient::create([
            'patient_id' => 'P-CAL-'.uniqid(),
            'first_name' => 'Pat',
            'last_name' => 'Calendar',
            'email' => 'pat-cal-'.uniqid().'@example.com',
            'phone' => '07123456780',
            'password' => bcrypt('password'),
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
        ]);

        $service = BookingService::create([
            'name' => 'Video Consultation',
            'default_duration_minutes' => 30,
        ]);

        $appointment = Appointment::create([
            'appointment_number' => 'APT-CAL-'.uniqid(),
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDays(3)->format('Y-m-d'),
            'appointment_time' => '10:00:00',
            'type' => 'consultation',
            'status' => 'confirmed',
            'consultation_type' => 'online',
            'meeting_link' => 'https://example.test/meeting/abc123',
        ]);

        $url = URL::temporarySignedRoute(
            'public.booking.calendar.appointment',
            now()->addMinutes(30),
            ['appointmentNumber' => $appointment->appointment_number]
        );

        $response = $this->get($url);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=UTF-8');
        $response->assertSeeText('BEGIN:VCALENDAR');
        $response->assertSeeText('BEGIN:VEVENT');
        $response->assertSeeText('END:VEVENT');
        $response->assertSeeText('END:VCALENDAR');
        $response->assertSeeText('APT-CAL-');
    }

    /** @test */
    public function signed_clinic_request_calendar_invite_downloads_as_ics(): void
    {
        $department = Department::create([
            'name' => 'Clinic Calendar Dept',
            'slug' => 'clinic-calendar-dept-'.uniqid(),
            'description' => 'Clinic calendar test department',
        ]);

        $service = BookingService::create([
            'name' => 'Clinic Consultation',
            'default_duration_minutes' => 45,
        ]);

        $clinicRequest = ClinicBookingRequest::create([
            'request_number' => 'CB-CAL-'.uniqid(),
            'department_id' => $department->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDays(4)->format('Y-m-d'),
            'appointment_time' => '14:30:00',
            'consultation_type' => 'in_person',
            'fee' => 0,
            'notes' => 'Initial clinic request note',
            'patient_data' => [
                'first_name' => 'Clinic',
                'last_name' => 'Patient',
                'email' => 'clinic-patient@example.com',
            ],
            'status' => 'pending_acceptance',
            'created_from' => 'Public Clinic Booking',
        ]);

        $url = URL::temporarySignedRoute(
            'public.booking.calendar.clinic-request',
            now()->addMinutes(30),
            ['requestNumber' => $clinicRequest->request_number]
        );

        $response = $this->get($url);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=UTF-8');
        $response->assertSeeText('BEGIN:VCALENDAR');
        $response->assertSeeText('BEGIN:VEVENT');
        $response->assertSeeText('END:VEVENT');
        $response->assertSeeText('END:VCALENDAR');
        $response->assertSeeText('CB-CAL-');
    }
}
