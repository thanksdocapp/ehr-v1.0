<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use Carbon\Carbon;

class MedicalRecordSeeder extends Seeder
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

        $patients = Patient::all();
        $doctors = Doctor::all();
        $appointments = Appointment::all();

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command->warn('No patients or doctors found. Please seed patients and doctors first.');
            return;
        }

        $recordTypes = ['consultation', 'follow_up', 'emergency', 'routine_checkup', 'specialist_visit'];
        $diagnoses = [
            'Hypertension',
            'Type 2 Diabetes',
            'Common Cold',
            'Migraine',
            'Allergic Rhinitis',
            'Gastroenteritis',
            'Anxiety Disorder',
            'Lower Back Pain',
            'Bronchitis',
            'Dermatitis'
        ];

        $symptoms = [
            ['headache', 'nausea', 'dizziness'],
            ['fever', 'cough', 'sore throat'],
            ['chest pain', 'shortness of breath'],
            ['abdominal pain', 'bloating', 'nausea'],
            ['fatigue', 'weakness', 'joint pain'],
            ['skin rash', 'itching', 'redness'],
            ['back pain', 'muscle stiffness'],
            ['runny nose', 'sneezing', 'congestion']
        ];

        $treatments = [
            'Prescribed medication and rest',
            'Physical therapy recommended',
            'Lifestyle modifications advised',
            'Follow-up in 2 weeks',
            'Specialist referral made',
            'Diagnostic tests ordered',
            'Patient education provided',
            'Immediate treatment administered'
        ];

        // Create medical records for each patient
        foreach ($patients as $patient) {
            $recordCount = rand(2, 6); // 2-6 records per patient
            
            for ($i = 0; $i < $recordCount; $i++) {
                $doctor = $doctors->random();
                $appointment = $appointments->where('patient_id', $patient->id)->first();
                
                $vitalSigns = [
                    'blood_pressure' => rand(110, 140) . '/' . rand(70, 90),
                    'heart_rate' => rand(60, 100),
                    'temperature' => rand(360, 390) / 10, // 36.0 - 39.0
                    'respiratory_rate' => rand(12, 20),
                    'oxygen_saturation' => rand(95, 100),
                    'weight' => rand(50, 100),
                    'height' => rand(150, 190)
                ];

                MedicalRecord::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'appointment_id' => $appointment?->id,
                    'record_type' => $recordTypes[array_rand($recordTypes)],
                    'record_date' => Carbon::now()->subDays(rand(1, 365)),
                    'diagnosis' => $diagnoses[array_rand($diagnoses)],
                    'symptoms' => implode(', ', $symptoms[array_rand($symptoms)]),
                    'treatment' => $treatments[array_rand($treatments)],
                    'notes' => 'Patient responded well to treatment. Continue monitoring condition.',
                    'vital_signs' => $vitalSigns,
                    'follow_up_date' => rand(0, 1) ? Carbon::now()->addDays(rand(7, 30)) : null,
                    'is_private' => rand(0, 10) < 2 // 20% chance of being private
                ]);
            }
        }

        $this->command->info('Medical records seeded successfully!');
    }
}
