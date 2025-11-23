<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PatientNotification;
use App\Models\Patient;

class PatientNotificationTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first patient or create one if none exists
        $patient = Patient::first();
        
        if (!$patient) {
            $patient = Patient::create([
                'first_name' => 'Test',
                'last_name' => 'Patient',
                'email' => 'testpatient@example.com',
                'phone' => '+1234567890',
                'date_of_birth' => '1990-01-01',
                'gender' => 'male',
                'address' => '123 Test Street',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'emergency_contact' => 'John Doe',
                'emergency_phone' => '+1987654321',
                'password' => bcrypt('password'),
            ]);
        }

        // Create sample notifications for testing
        $notifications = [
            [
                'patient_id' => $patient->id,
                'type' => PatientNotification::TYPE_APPOINTMENT,
                'title' => 'Appointment Confirmation',
                'message' => 'Your appointment with Dr. Smith on January 15th has been confirmed.',
                'category' => PatientNotification::CATEGORY_APPOINTMENT,
                'is_read' => false,
                'action_url' => '/patient/appointments',
                'priority' => 'high',
            ],
            [
                'patient_id' => $patient->id,
                'type' => PatientNotification::TYPE_LAB_RESULT,
                'title' => 'Lab Results Available',
                'message' => 'Your blood test results are ready for review.',
                'category' => PatientNotification::CATEGORY_MEDICAL,
                'is_read' => false,
                'action_url' => '/patient/lab-reports',
                'priority' => 'medium',
            ],
            [
                'patient_id' => $patient->id,
                'type' => PatientNotification::TYPE_PRESCRIPTION,
                'title' => 'Prescription Ready',
                'message' => 'Your prescription for Amoxicillin is ready for pickup.',
                'category' => PatientNotification::CATEGORY_MEDICAL,
                'is_read' => false,
                'action_url' => '/patient/prescriptions',
                'priority' => 'medium',
            ],
            [
                'patient_id' => $patient->id,
                'type' => PatientNotification::TYPE_BILLING,
                'title' => 'Payment Due',
                'message' => 'You have an outstanding balance of $150 for your recent visit.',
                'category' => PatientNotification::CATEGORY_BILLING,
                'is_read' => false,
                'action_url' => '/patient/billing',
                'priority' => 'high',
            ],
            [
                'patient_id' => $patient->id,
                'type' => PatientNotification::TYPE_SYSTEM,
                'title' => 'Profile Update Required',
                'message' => 'Please update your emergency contact information.',
                'category' => PatientNotification::CATEGORY_ADMINISTRATIVE,
                'is_read' => true,
                'read_at' => now()->subHours(2),
                'action_url' => '/patient/profile/edit',
                'priority' => 'low',
            ],
        ];

        foreach ($notifications as $notification) {
            PatientNotification::create($notification);
        }

        $this->command->info('Created ' . count($notifications) . ' test notifications for patient: ' . $patient->first_name . ' ' . $patient->last_name);
    }
}
