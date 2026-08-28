<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorAvailabilityRule;
use App\Services\DoctorWeeklyAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorWeeklyAvailabilityDisplayTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_display_includes_multiple_sessions_per_day(): void
    {
        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Weekly',
            'last_name' => 'Display',
            'slug' => 'weekly-display-'.uniqid(),
            'specialization' => 'GP',
            'bio' => 'Test',
            'qualification' => 'MBBS',
            'experience_years' => 3,
            'email' => 'weekly-display-'.uniqid().'@example.com',
            'availability' => [
                'thursday' => [
                    'available' => true,
                    'sessions' => [
                        ['start' => '13:00', 'end' => '20:00'],
                        ['start' => '17:00', 'end' => '19:00'],
                    ],
                ],
            ],
        ]);

        $days = app(DoctorWeeklyAvailabilityService::class)->displayDaysForDoctor($doctor);

        $this->assertCount(2, $days['thursday']['sessions']);
        $this->assertSame('13:00', $days['thursday']['sessions'][0]['start']);
        $this->assertSame('20:00', $days['thursday']['sessions'][0]['end']);
    }

    /** @test */
    public function syncing_weekly_schedule_replaces_backfill_rules(): void
    {
        config(['booking.modality_rules_enabled' => true]);

        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Weekly',
            'last_name' => 'Sync',
            'slug' => 'weekly-sync-'.uniqid(),
            'specialization' => 'GP',
            'bio' => 'Test',
            'qualification' => 'MBBS',
            'experience_years' => 3,
            'email' => 'weekly-sync-'.uniqid().'@example.com',
            'availability' => [
                'thursday' => [
                    'available' => true,
                    'sessions' => [
                        ['start' => '13:00', 'end' => '20:00'],
                    ],
                ],
            ],
        ]);

        DoctorAvailabilityRule::create([
            'doctor_id' => $doctor->id,
            'day_of_week' => 'thursday',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'modality' => DoctorAvailabilityRule::MODALITY_ALL,
            'is_active' => true,
            'needs_review' => true,
            'source' => 'backfill',
        ]);

        app(DoctorWeeklyAvailabilityService::class)->syncRulesFromWeeklySchedule($doctor);

        $rules = DoctorAvailabilityRule::query()
            ->where('doctor_id', $doctor->id)
            ->where('day_of_week', 'thursday')
            ->orderBy('start_time')
            ->get();

        $this->assertCount(1, $rules);
        $this->assertSame(DoctorAvailabilityRule::SOURCE_WEEKLY_SCHEDULE, $rules->first()->source);
        $this->assertSame('13:00:00', $rules->first()->start_time);
        $this->assertSame('20:00:00', $rules->first()->end_time);
    }

    /** @test */
    public function admin_edit_form_matches_show_page_sessions(): void
    {
        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Weekly',
            'last_name' => 'Edit',
            'slug' => 'weekly-edit-'.uniqid(),
            'specialization' => 'GP',
            'bio' => 'Test',
            'qualification' => 'MBBS',
            'experience_years' => 3,
            'email' => 'weekly-edit-'.uniqid().'@example.com',
            'availability' => [
                'monday' => [
                    'available' => true,
                    'sessions' => [
                        ['start' => '09:00:00', 'end' => '12:30:00'],
                    ],
                ],
                'thursday' => [
                    'sessions' => [
                        ['start' => '13:00', 'end' => '20:00'],
                    ],
                ],
            ],
        ]);

        $service = app(DoctorWeeklyAvailabilityService::class);
        $formData = $service->availabilityForAdminForm($doctor);

        $this->assertTrue($formData['monday']['available']);
        $this->assertSame('09:00', $formData['monday']['sessions'][0]['start']);
        $this->assertSame('12:30', $formData['monday']['sessions'][0]['end']);
        $this->assertTrue($formData['thursday']['available']);
        $this->assertSame('13:00', $formData['thursday']['sessions'][0]['start']);
        $this->assertSame('20:00', $formData['thursday']['sessions'][0]['end']);
    }
}
