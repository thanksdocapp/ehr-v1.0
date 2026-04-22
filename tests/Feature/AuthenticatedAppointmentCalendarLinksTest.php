<?php

namespace Tests\Feature;

use App\Http\Middleware\RequireTwoFactor;
use App\Models\Appointment;
use App\Models\BookingService;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticatedAppointmentCalendarLinksTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function staff_appointment_show_includes_calendar_actions(): void
    {
        $department = Department::create([
            'name' => 'Staff Cal Dept',
            'slug' => 'staff-cal-dept-'.uniqid(),
            'description' => 'Test',
        ]);

        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Staff',
            'last_name' => 'Cal',
            'slug' => 'staff-cal-doc-'.uniqid(),
            'specialization' => 'General',
            'department_id' => $department->id,
            'bio' => 'Test',
            'qualification' => 'MBBS',
            'experience_years' => 5,
        ]);

        $patient = Patient::create([
            'patient_id' => 'P-STAFF-CAL-'.uniqid(),
            'first_name' => 'Pat',
            'last_name' => 'StaffCal',
            'email' => 'staff-cal-pat-'.uniqid().'@example.com',
            'phone' => '07123456781',
            'password' => bcrypt('password'),
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
        ]);

        $service = BookingService::create([
            'name' => 'Consultation',
            'default_duration_minutes' => 30,
        ]);

        $appointment = Appointment::create([
            'appointment_number' => 'APT-STAFF-CAL-'.uniqid(),
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDays(2)->format('Y-m-d'),
            'appointment_time' => '11:00:00',
            'type' => 'consultation',
            'status' => 'confirmed',
        ]);

        $admin = User::create([
            'name' => 'Admin Staff Cal',
            'email' => 'admin-staff-cal-'.uniqid().'@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
        ]);

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($admin);

        $response = $this->get(route('staff.appointments.show', $appointment->id));

        $response->assertOk();
        $response->assertSee('Add to your calendar', false);
        $response->assertSee('calendar.google.com', false);
        $response->assertSee('Apple / Outlook', false);
    }

    /** @test */
    public function patient_appointment_show_includes_calendar_actions(): void
    {
        $department = Department::create([
            'name' => 'Patient Cal Dept',
            'slug' => 'patient-cal-dept-'.uniqid(),
            'description' => 'Test',
        ]);

        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Pat',
            'last_name' => 'DocCal',
            'slug' => 'pat-cal-doc-'.uniqid(),
            'specialization' => 'General',
            'department_id' => $department->id,
            'bio' => 'Test',
            'qualification' => 'MBBS',
            'experience_years' => 5,
        ]);

        $patient = Patient::create([
            'patient_id' => 'P-PAT-CAL-'.uniqid(),
            'first_name' => 'Jane',
            'last_name' => 'PatientCal',
            'email' => 'patient-cal-'.uniqid().'@example.com',
            'phone' => '07123456782',
            'password' => bcrypt('password'),
            'date_of_birth' => '1992-06-15',
            'gender' => 'female',
        ]);

        $service = BookingService::create([
            'name' => 'Follow-up',
            'default_duration_minutes' => 20,
        ]);

        $appointment = Appointment::create([
            'appointment_number' => 'APT-PAT-CAL-'.uniqid(),
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDays(5)->format('Y-m-d'),
            'appointment_time' => '09:30:00',
            'type' => 'consultation',
            'status' => 'pending',
        ]);

        $this->actingAs($patient, 'patient');

        $response = $this->get(route('patient.appointments.show', $appointment));

        $response->assertOk();
        $response->assertSee('Add to your calendar', false);
        $response->assertSee('calendar.google.com', false);
    }
}
