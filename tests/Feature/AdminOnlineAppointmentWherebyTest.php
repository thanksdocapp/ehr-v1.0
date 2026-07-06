<?php

namespace Tests\Feature;

use App\Http\Middleware\RequireTwoFactor;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Services\HospitalEmailNotificationService;
use App\Services\WherebyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AdminOnlineAppointmentWherebyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_online_appointment_maps_consultation_type_and_emails_include_meeting_links(): void
    {
        $department = Department::create([
            'name' => 'Video Clinic',
            'slug' => 'video-clinic-' . uniqid(),
            'description' => 'Test',
            'is_active' => true,
        ]);

        $doctorUser = User::create([
            'name' => 'Video Doctor',
            'email' => 'video-doctor-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'is_admin' => false,
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Video',
            'last_name' => 'Doctor',
            'slug' => 'video-doc-' . uniqid(),
            'specialization' => 'General',
            'user_id' => $doctorUser->id,
            'department_id' => $department->id,
            'bio' => 'Test',
            'qualification' => 'MBBS',
            'experience_years' => 5,
            'email' => 'video-doctor-profile-' . uniqid() . '@example.com',
        ]);

        $patient = Patient::create([
            'patient_id' => 'P-VIDEO-' . uniqid(),
            'first_name' => 'Video',
            'last_name' => 'Patient',
            'email' => 'video-patient-' . uniqid() . '@example.com',
            'phone' => '07123456789',
            'password' => bcrypt('password'),
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $admin = User::create([
            'name' => 'Video Admin',
            'email' => 'video-admin-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
        ]);

        $participantLink = 'https://whereby.test/patient-room';
        $hostLink = 'https://whereby.test/host-room';

        $this->mock(WherebyService::class, function ($mock) use ($participantLink, $hostLink) {
            $mock->shouldReceive('isEnabled')->andReturn(true);
            $mock->shouldReceive('createMeetingForAppointment')
                ->once()
                ->andReturnUsing(function (Appointment $appointment) use ($participantLink, $hostLink) {
                    $appointment->update([
                        'meeting_link' => $participantLink,
                        'whereby_host_url' => $hostLink,
                    ]);

                    return true;
                });
        });

        $emailSpy = Mockery::spy(HospitalEmailNotificationService::class);
        $this->app->instance(HospitalEmailNotificationService::class, $emailSpy);

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($admin, 'admin');

        $response = $this->post(route('admin.appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'appointment_time' => '10:00',
            'type' => 'consultation',
            'consultation_type' => 'online',
            'meeting_platform' => 'whereby',
            'reason' => 'Online follow-up',
        ]);

        $response->assertRedirect(route('admin.appointments.index'));

        $appointment = Appointment::query()->latest('id')->first();
        $this->assertNotNull($appointment);
        $this->assertTrue($appointment->is_online);
        $this->assertSame('online', $appointment->consultation_type);
        $this->assertSame('whereby', $appointment->meeting_platform);
        $this->assertSame($participantLink, $appointment->meeting_link);
        $this->assertSame($hostLink, $appointment->whereby_host_url);

        $emailSpy->shouldHaveReceived('sendAppointmentConfirmation')
            ->once()
            ->with(
                Mockery::on(fn (Appointment $apt) => $apt->id === $appointment->id && $apt->meeting_link === $participantLink),
                Mockery::any()
            );

        $emailSpy->shouldHaveReceived('sendNewAppointmentToDoctor')
            ->once()
            ->with(Mockery::on(fn (Appointment $apt) => $apt->id === $appointment->id && $apt->whereby_host_url === $hostLink));
    }
}
