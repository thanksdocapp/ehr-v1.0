<?php

namespace Tests\Feature;

use App\Http\Middleware\RequireTwoFactor;
use App\Models\Doctor;
use App\Models\DoctorAvailabilityRule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffAppointmentAvailableSlotsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function doctor_can_load_evening_slots_from_weekly_availability(): void
    {
        config(['booking.modality_rules_enabled' => true]);

        [$doctorUser, $doctor] = $this->createDoctorUser();

        $thursday = Carbon::now()->next(Carbon::THURSDAY)->toDateString();

        DoctorAvailabilityRule::create([
            'doctor_id' => $doctor->id,
            'day_of_week' => 'thursday',
            'start_time' => '13:00:00',
            'end_time' => '20:00:00',
            'modality' => DoctorAvailabilityRule::MODALITY_ALL,
            'is_active' => true,
        ]);

        DoctorAvailabilityRule::create([
            'doctor_id' => $doctor->id,
            'day_of_week' => 'thursday',
            'start_time' => '17:00:00',
            'end_time' => '19:00:00',
            'modality' => DoctorAvailabilityRule::MODALITY_ALL,
            'is_active' => true,
        ]);

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($doctorUser);

        $response = $this->getJson(route('staff.api.doctors.available-slots', [
            'doctor' => $doctor->id,
            'date' => $thursday,
            'duration' => 15,
            'modality' => 'in_person',
        ]));

        $response->assertOk()
            ->assertJsonPath('date', $thursday);

        $starts = collect($response->json('slots'))->pluck('start')->all();
        $this->assertContains('18:00', $starts);
    }

    /**
     * @return array{0: User, 1: Doctor}
     */
    private function createDoctorUser(): array
    {
        $doctorUser = User::create([
            'name' => 'Schedule Doctor',
            'email' => 'schedule-doctor-'.uniqid().'@example.com',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'title' => 'Dr.',
            'first_name' => 'Schedule',
            'last_name' => 'Doctor',
            'slug' => 'schedule-doctor-'.uniqid(),
            'specialization' => 'GP',
            'bio' => 'Test',
            'qualification' => 'MBBS',
            'experience_years' => 5,
            'email' => $doctorUser->email,
        ]);

        $doctorUser->setRelation('doctor', $doctor);

        return [$doctorUser, $doctor];
    }
}
