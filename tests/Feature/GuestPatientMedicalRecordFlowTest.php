<?php

namespace Tests\Feature;

use App\Http\Middleware\RequireTwoFactor;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuestPatientMedicalRecordFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_flag_clears_when_demographics_match_complete_information_rules(): void
    {
        $patient = Patient::create([
            'patient_id' => 'P-GUEST-' . uniqid(),
            'first_name' => 'Guest',
            'last_name' => 'Patient',
            'email' => 'pay-' . uniqid() . '@payment-link.temp',
            'phone' => 'Not specified',
            'password' => bcrypt('password'),
            'is_guest' => true,
            'is_active' => true,
        ]);

        $this->assertTrue($patient->is_guest);
        $this->assertTrue($patient->hasIncompleteInformation()['is_incomplete']);

        $patient->update([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe.' . uniqid() . '@example.com',
            'phone' => '07123456789',
            'date_of_birth' => '1980-05-15',
            'gender' => 'female',
            'address' => '1 High Street',
            'emergency_contact' => 'Bob Nextofkin',
            'emergency_phone' => '07987654321',
        ]);

        $patient->refresh();
        $info = $patient->hasIncompleteInformation();
        $this->assertFalse($info['is_incomplete']);
        $this->assertFalse($info['has_recommended_gaps']);

        $this->assertTrue($patient->clearGuestFlagIfInformationComplete());
        $patient->refresh();

        $this->assertFalse($patient->is_guest);
        $this->assertFalse($patient->hasIncompleteInformation()['is_incomplete']);
    }

    /** @test */
    public function uk_core_complete_without_emergency_clears_guest_but_flags_recommended(): void
    {
        $patient = Patient::create([
            'patient_id' => 'P-GUEST-CORE-' . uniqid(),
            'first_name' => 'Guest',
            'last_name' => 'Patient',
            'email' => 'pay-core-' . uniqid() . '@payment-link.temp',
            'phone' => 'Not specified',
            'password' => bcrypt('password'),
            'is_guest' => true,
            'is_active' => true,
        ]);

        $patient->update([
            'first_name' => 'Alex',
            'last_name' => 'Core',
            'email' => 'alex.core.' . uniqid() . '@example.com',
            'phone' => '07123456789',
            'date_of_birth' => '1990-03-01',
            'gender' => 'male',
            'address' => '10 Test Street, London',
        ]);
        $patient->refresh();

        $info = $patient->hasIncompleteInformation();
        $this->assertFalse($info['is_incomplete'], 'UK core demographics should be complete');
        $this->assertTrue($info['has_recommended_gaps'], 'Emergency and NHS should still be recommended');

        $this->assertTrue($patient->clearGuestFlagIfInformationComplete());
        $patient->refresh();
        $this->assertFalse($patient->is_guest);
    }

    /** @test */
    public function guest_flag_stays_when_information_still_incomplete(): void
    {
        $patient = Patient::create([
            'patient_id' => 'P-GUEST2-' . uniqid(),
            'first_name' => 'Guest',
            'last_name' => 'Patient',
            'email' => 'pay2-' . uniqid() . '@payment-link.temp',
            'phone' => 'Not specified',
            'password' => bcrypt('password'),
            'is_guest' => true,
            'is_active' => true,
        ]);

        $patient->update([
            'address' => '1 High Street',
        ]);
        $patient->refresh();

        $this->assertFalse($patient->clearGuestFlagIfInformationComplete());
        $this->assertTrue($patient->is_guest);
        $this->assertTrue($patient->hasIncompleteInformation()['is_incomplete']);
    }

    /** @test */
    public function staff_appointments_index_links_guest_patients_to_complete_profile_edit(): void
    {
        $department = Department::create([
            'name' => 'Test Dept Guest Row',
            'slug' => 'test-dept-guest-row-' . uniqid(),
            'description' => 'Test',
        ]);

        $nurse = User::create([
            'name' => 'Nurse Guest Row',
            'email' => 'nurse-guest-row-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'nurse',
            'is_active' => true,
            'department_id' => $department->id,
        ]);

        $doctor = Doctor::create([
            'title' => 'Dr.',
            'first_name' => 'Doc',
            'last_name' => 'GuestRow',
            'slug' => 'doc-guest-row-' . uniqid(),
            'specialization' => 'General',
            'department_id' => $department->id,
            'bio' => 'Test',
            'qualification' => 'MD',
            'experience_years' => 5,
        ]);
        $department->doctors()->attach($doctor->id, ['is_primary' => true]);

        $patient = Patient::create([
            'patient_id' => 'P-GUEST-ROW-' . uniqid(),
            'first_name' => 'Guest',
            'last_name' => 'ListRow',
            'email' => 'guest-row-' . uniqid() . '@payment-link.temp',
            'phone' => 'Not specified',
            'password' => bcrypt('password'),
            'department_id' => $department->id,
            'is_guest' => true,
            'is_active' => true,
        ]);

        Appointment::create([
            'appointment_number' => 'APT-GUEST-ROW-' . uniqid(),
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $department->id,
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'appointment_time' => '10:00:00',
            'type' => 'consultation',
            'status' => 'pending',
        ]);

        $this->withoutMiddleware([RequireTwoFactor::class]);
        $this->actingAs($nurse);

        $response = $this->get(route('staff.appointments.index'));
        $response->assertOk();
        $response->assertSee('/staff/patients/' . $patient->id . '/edit', false);
    }
}
