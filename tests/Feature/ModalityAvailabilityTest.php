<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BookingService;
use App\Models\Doctor;
use App\Models\DoctorAvailabilityRule;
use App\Models\PendingBooking;
use App\Services\SlotAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModalityAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected SlotAvailabilityService $slotService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->slotService = app(SlotAvailabilityService::class);
        config(['booking.modality_rules_enabled' => true]);
        config(['booking.lock_pending_bookings' => true]);
    }

    /** A Monday at least a week out, so all generated slots are in the future. */
    private function futureMonday(): Carbon
    {
        return Carbon::now()->addWeek()->startOfWeek(Carbon::MONDAY);
    }

    private function makeDoctor(string $suffix): Doctor
    {
        return Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Mod',
            'last_name' => $suffix,
            'slug' => 'mod-' . $suffix . '-' . uniqid(),
            'specialization' => 'General',
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
        ]);
    }

    private function makeService(string $consultationType): BookingService
    {
        return BookingService::create([
            'name' => ucfirst($consultationType) . ' Consultation',
            'default_duration_minutes' => 30,
            'default_consultation_type' => $consultationType,
        ]);
    }

    private function makeRule(Doctor $doctor, string $day, string $modality): DoctorAvailabilityRule
    {
        return $doctor->availabilityRules()->create([
            'day_of_week' => $day,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'modality' => $modality,
            'is_active' => true,
            'needs_review' => false,
            'source' => 'manual',
        ]);
    }

    /** @test */
    public function online_rule_only_serves_online_services(): void
    {
        $doctor = $this->makeDoctor('online');
        $date = $this->futureMonday();
        $this->makeRule($doctor, 'monday', DoctorAvailabilityRule::MODALITY_ONLINE);

        $onlineService = $this->makeService('online');
        $inPersonService = $this->makeService('in_person');

        $onlineSlots = $this->slotService->getAvailableSlots($doctor->id, $date->format('Y-m-d'), $onlineService->id);
        $this->assertNotNull(collect($onlineSlots)->firstWhere('start', '10:00'), 'Online service should see the 10:00 slot');

        $inPersonSlots = $this->slotService->getAvailableSlots($doctor->id, $date->format('Y-m-d'), $inPersonService->id);
        $this->assertEmpty($inPersonSlots, 'In-person service must see no slots when only an online rule exists');
    }

    /** @test */
    public function all_rule_serves_every_modality(): void
    {
        $doctor = $this->makeDoctor('all');
        $date = $this->futureMonday();
        $this->makeRule($doctor, 'monday', DoctorAvailabilityRule::MODALITY_ALL);

        foreach (['online', 'in_person', 'telephone'] as $type) {
            $service = $this->makeService($type);
            $slots = $this->slotService->getAvailableSlots($doctor->id, $date->format('Y-m-d'), $service->id);
            $this->assertNotNull(
                collect($slots)->firstWhere('start', '10:00'),
                "An 'all' rule should serve the {$type} service at 10:00"
            );
        }
    }

    /** @test */
    public function booking_one_modality_blocks_the_physical_slot_for_other_modalities(): void
    {
        $doctor = $this->makeDoctor('cross');
        $date = $this->futureMonday();
        // Doctor offers both online and in-person in the same window.
        $this->makeRule($doctor, 'monday', DoctorAvailabilityRule::MODALITY_ONLINE);
        $this->makeRule($doctor, 'monday', DoctorAvailabilityRule::MODALITY_IN_PERSON);

        $onlineService = $this->makeService('online');
        $inPersonService = $this->makeService('in_person');

        // 10:00 available for in-person before any booking.
        $before = $this->slotService->getAvailableSlots($doctor->id, $date->format('Y-m-d'), $inPersonService->id);
        $this->assertNotNull(collect($before)->firstWhere('start', '10:00'));

        // Book the ONLINE service at 10:00.
        Appointment::create([
            'appointment_number' => 'APT-' . uniqid(),
            'doctor_id' => $doctor->id,
            'service_id' => $onlineService->id,
            'appointment_date' => $date->format('Y-m-d'),
            'appointment_time' => '10:00:00',
            'type' => 'consultation',
            'status' => 'confirmed',
        ]);

        // The physical block must remove 10:00 from the IN-PERSON list too.
        $after = $this->slotService->getAvailableSlots($doctor->id, $date->format('Y-m-d'), $inPersonService->id);
        $this->assertNull(
            collect($after)->firstWhere('start', '10:00'),
            'Booking the online modality must block the in-person slot at the same time'
        );
    }

    /** @test */
    public function in_progress_pending_booking_blocks_slot_and_expiry_frees_it(): void
    {
        $doctor = $this->makeDoctor('pending');
        $date = $this->futureMonday();
        $this->makeRule($doctor, 'monday', DoctorAvailabilityRule::MODALITY_ALL);
        $service = $this->makeService('in_person');

        $pending = PendingBooking::create([
            'booking_token' => PendingBooking::generateBookingToken(),
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => $date->format('Y-m-d'),
            'appointment_time' => '10:00:00',
            'is_online' => false,
            'patient_data' => ['first_name' => 'Temp', 'last_name' => 'Hold', 'email' => 'temp@example.com'],
            'fee' => 0,
            'status' => 'pending_payment',
            'expires_at' => now()->addMinutes(15),
        ]);

        $held = $this->slotService->getAvailableSlots($doctor->id, $date->format('Y-m-d'), $service->id);
        $this->assertNull(
            collect($held)->firstWhere('start', '10:00'),
            'An in-progress pending booking must hold the 10:00 slot'
        );

        // Expire the hold — the slot should be free again.
        $pending->update(['expires_at' => now()->subMinute()]);

        $freed = $this->slotService->getAvailableSlots($doctor->id, $date->format('Y-m-d'), $service->id);
        $this->assertNotNull(
            collect($freed)->firstWhere('start', '10:00'),
            'Once the pending booking expires, the slot must be bookable again'
        );
    }

    /** @test */
    public function disabling_the_feature_flag_falls_back_to_legacy_unfiltered_slots(): void
    {
        config(['booking.modality_rules_enabled' => false]);

        $doctor = $this->makeDoctor('legacy');
        $date = $this->futureMonday();
        // Legacy JSON availability for that weekday.
        $doctor->update([
            'availability' => [
                'monday' => ['available' => true, 'start' => '09:00', 'end' => '17:00', 'breaks' => []],
            ],
        ]);
        // Even though an online-only rule exists, the disabled flag must ignore it.
        $this->makeRule($doctor, 'monday', DoctorAvailabilityRule::MODALITY_ONLINE);

        $inPersonService = $this->makeService('in_person');
        $slots = $this->slotService->getAvailableSlots($doctor->id, $date->format('Y-m-d'), $inPersonService->id);

        $this->assertNotNull(
            collect($slots)->firstWhere('start', '10:00'),
            'With the feature disabled, legacy availability should serve any modality'
        );
    }
}
