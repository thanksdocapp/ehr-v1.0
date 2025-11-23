<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Appointment;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestPatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only seed in local environment
        if (!app()->environment('local')) {
            return;
        }

        // Check if patients already exist
        $existingPatients = Patient::count();
        
        if ($existingPatients > 0) {
            $this->command->warn("⚠️  WARNING: Found {$existingPatients} existing patient(s) in database!");
            $this->command->info('This seeder will NOT delete existing patients - it will only add new test data.');
            $this->command->info('Skipping patient seeding to prevent duplicates.');
            return;
        }

        $this->command->info('Creating test patients and basic appointments...');

        // Create test patients
        $patientsData = [
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@example.com',
                'phone' => '+1234567890',
                'date_of_birth' => '1985-03-15',
                'gender' => 'male',
                'address' => '123 Main Street',
                'city' => 'Anytown',
                'state' => 'ST',
                'postal_code' => '12345',
                'blood_group' => 'O+',
                'medical_conditions' => ['Hypertension'],
                'allergies' => [],
                'insurance_provider' => 'Blue Cross Blue Shield',
                'insurance_number' => 'BCBS123456789'
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@example.com',
                'phone' => '+1234567891',
                'date_of_birth' => '1990-07-22',
                'gender' => 'female',
                'address' => '456 Oak Avenue',
                'city' => 'Somewhere',
                'state' => 'ST',
                'postal_code' => '12346',
                'blood_group' => 'A+',
                'medical_conditions' => [],
                'allergies' => ['Penicillin'],
                'insurance_provider' => 'Aetna',
                'insurance_number' => 'AET987654321'
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'email' => 'michael.brown@example.com',
                'phone' => '+1234567892',
                'date_of_birth' => '1978-11-08',
                'gender' => 'male',
                'address' => '789 Pine Road',
                'city' => 'Another City',
                'state' => 'ST',
                'postal_code' => '12347',
                'blood_group' => 'B-',
                'medical_conditions' => ['Diabetes Type 2'],
                'allergies' => [],
                'insurance_provider' => 'Cigna',
                'insurance_number' => 'CIG456789123'
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'email' => 'emily.davis@example.com',
                'phone' => '+1234567893',
                'date_of_birth' => '1995-01-30',
                'gender' => 'female',
                'address' => '321 Elm Street',
                'city' => 'Different Town',
                'state' => 'ST',
                'postal_code' => '12348',
                'blood_group' => 'AB+',
                'medical_conditions' => [],
                'allergies' => [],
                'insurance_provider' => 'United Healthcare',
                'insurance_number' => 'UHC789123456'
            ],
            [
                'first_name' => 'Robert',
                'last_name' => 'Wilson',
                'email' => 'robert.wilson@example.com',
                'phone' => '+1234567894',
                'date_of_birth' => '1972-09-14',
                'gender' => 'male',
                'address' => '654 Maple Lane',
                'city' => 'Old Town',
                'state' => 'ST',
                'postal_code' => '12349',
                'blood_group' => 'O-',
                'medical_conditions' => ['Previous heart surgery (2018)'],
                'allergies' => [],
                'insurance_provider' => 'Kaiser Permanent',
                'insurance_number' => 'KP123789456'
            ]
        ];

        $createdPatients = [];
        $patientNumber = 1;
        
        foreach ($patientsData as $patientData) {
            // Generate patient_id
            $lastPatientId = Patient::orderBy('id', 'desc')->first();
            if ($lastPatientId && $lastPatientId->patient_id) {
                // Extract number from last patient_id (e.g., P2025001 -> 1)
                preg_match('/P(\d{4})(\d{3})/', $lastPatientId->patient_id, $matches);
                if ($matches) {
                    $year = $matches[1];
                    $lastNum = intval($matches[2]);
                    $patientNumber = $lastNum + 1;
                }
            }
            
            $yearPrefix = date('Y');
            $patientId = 'P' . $yearPrefix . str_pad($patientNumber, 3, '0', STR_PAD_LEFT);
            
            $patient = Patient::create(array_merge($patientData, [
                'patient_id' => $patientId,
                'password' => Hash::make('password123'), // Default password for testing
                'email_verified_at' => Carbon::now(),
                'emergency_contact' => 'Emergency Contact',
                'emergency_phone' => '+1987654321',
                'is_active' => true
            ]));
            
            $createdPatients[] = $patient;
            $patientNumber++;
        }

        // Create some basic appointments if doctors exist
        $doctors = Doctor::all();
        if ($doctors->isNotEmpty()) {
            foreach ($createdPatients as $patient) {
                // Create 1-2 appointments per patient
                $appointmentCount = rand(1, 2);
                
                for ($i = 0; $i < $appointmentCount; $i++) {
                    $doctor = $doctors->random();
                    $appointmentDate = Carbon::now()->addDays(rand(-30, 30));
                    
                    Appointment::create([
                        'patient_id' => $patient->id,
                        'doctor_id' => $doctor->id,
                        'department_id' => $doctor->department_id ?? Department::first()?->id,
                        'appointment_date' => $appointmentDate->format('Y-m-d'),
                        'appointment_time' => $appointmentDate->format('H:i:s'),
                        'status' => ['scheduled', 'completed', 'cancelled'][array_rand(['scheduled', 'completed', 'cancelled'])],
                        'reason' => 'General consultation and health checkup',
                        'notes' => 'Regular appointment for health monitoring'
                    ]);
                }
            }
        }

        $this->command->info('Created ' . count($createdPatients) . ' test patients successfully!');
        $this->command->info('Test patient credentials:');
        $this->command->info('Email: john.smith@example.com | Password: password123');
        $this->command->info('Email: sarah.johnson@example.com | Password: password123');
        $this->command->info('(All test patients use password: password123)');
    }
}
