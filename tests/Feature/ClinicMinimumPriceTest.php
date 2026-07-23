<?php

namespace Tests\Feature;

use App\Models\BookingService;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorServicePrice;
use App\Services\ClinicBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicMinimumPriceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function minimum_clinic_price_includes_zero_when_doctors_are_free(): void
    {
        $department = Department::create([
            'name' => 'Zero Price Clinic',
            'slug' => 'zero-price-clinic-' . uniqid(),
            'is_active' => true,
        ]);

        $service = BookingService::create([
            'name' => 'Complimentary Service',
            'default_duration_minutes' => 30,
            'default_price' => 50,
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Free',
            'last_name' => 'Doctor',
            'slug' => 'free-doc-' . uniqid(),
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

        $minimum = app(ClinicBookingService::class)->minimumListPriceForClinicService($service, $department->id);

        $this->assertSame(0.0, $minimum);
    }
}
