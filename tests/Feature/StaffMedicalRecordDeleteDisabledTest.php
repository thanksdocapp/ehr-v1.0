<?php

namespace Tests\Feature;

use App\Http\Middleware\RequireTwoFactor;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffMedicalRecordDeleteDisabledTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function doctor_cannot_delete_whole_medical_record_via_staff_route(): void
    {
        [$doctorUser, $doctor] = $this->createDoctorUser();

        $patient = Patient::create([
            'patient_id' => 'P-DELETE-TEST-' . uniqid(),
            'first_name' => 'Robert',
            'last_name' => 'Beynon',
            'email' => 'robert.beynon.' . uniqid() . '@example.com',
            'phone' => '07123456789',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $medicalRecord = MedicalRecord::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'record_type' => 'consultation',
            'record_date' => now()->toDateString(),
            'created_by' => $doctorUser->id,
            'updated_by' => $doctorUser->id,
        ]);

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($doctorUser);

        $response = $this->delete('/staff/medical-records/' . $medicalRecord->id);

        $response->assertNotFound();
        $this->assertDatabaseHas('medical_records', ['id' => $medicalRecord->id]);
    }

    /**
     * @return array{0: User, 1: Doctor}
     */
    private function createDoctorUser(): array
    {
        $doctorUser = User::create([
            'name' => 'Delete Test Doctor',
            'email' => 'delete-test-doctor-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'title' => 'Dr.',
            'first_name' => 'Delete',
            'last_name' => 'Test',
            'slug' => 'delete-test-doctor-' . uniqid(),
            'specialization' => 'General',
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
        ]);

        return [$doctorUser, $doctor];
    }
}
