<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Carbon\Carbon;

class PrescriptionSeeder extends Seeder
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
        $medicalRecords = MedicalRecord::all();

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command->warn('No patients or doctors found. Please seed patients and doctors first.');
            return;
        }

        $medications = [
            ['name' => 'Amoxicillin', 'dosage' => '500mg', 'frequency' => '3 times daily', 'duration' => '7 days'],
            ['name' => 'Ibuprofen', 'dosage' => '400mg', 'frequency' => '2 times daily', 'duration' => '5 days'],
            ['name' => 'Metformin', 'dosage' => '500mg', 'frequency' => '2 times daily', 'duration' => '30 days'],
            ['name' => 'Lisinopril', 'dosage' => '10mg', 'frequency' => '1 time daily', 'duration' => '30 days'],
            ['name' => 'Omeprazole', 'dosage' => '20mg', 'frequency' => '1 time daily', 'duration' => '14 days'],
            ['name' => 'Cetirizine', 'dosage' => '10mg', 'frequency' => '1 time daily', 'duration' => '10 days'],
            ['name' => 'Paracetamol', 'dosage' => '500mg', 'frequency' => '4 times daily', 'duration' => '3 days'],
            ['name' => 'Clopidogrel', 'dosage' => '75mg', 'frequency' => '1 time daily', 'duration' => '30 days'],
            ['name' => 'Salbutamol', 'dosage' => '100mcg', 'frequency' => 'As needed', 'duration' => '30 days'],
            ['name' => 'Fluoxetine', 'dosage' => '20mg', 'frequency' => '1 time daily', 'duration' => '30 days']
        ];

        $instructions = [
            'Take with food',
            'Take on empty stomach',
            'Take before bedtime',
            'Take with plenty of water',
            'Do not crush or chew',
            'Complete the full course',
            'Take at the same time each day',
            'Avoid alcohol while taking this medication'
        ];

        $statuses = ['active', 'completed', 'discontinued', 'expired'];

        // Create prescriptions for patients
        foreach ($patients as $patient) {
            $prescriptionCount = rand(1, 4); // 1-4 prescriptions per patient
            
            for ($i = 0; $i < $prescriptionCount; $i++) {
                $doctor = $doctors->random();
                $appointment = Appointment::where('patient_id', $patient->id)->first();
                $medicalRecord = $medicalRecords->where('patient_id', $patient->id)->first();
                $medication = $medications[array_rand($medications)];
                
                $prescribedDate = Carbon::now()->subDays(rand(1, 90));
                $expiryDate = $prescribedDate->copy()->addDays(rand(30, 365));
                
                // Generate prescription number
                $prescriptionNumber = 'RX-' . strtoupper(substr(md5(uniqid()), 0, 8));
                
                $status = $statuses[array_rand($statuses)];
                
                // Adjust dates based on status
                if ($status === 'expired') {
                    $expiryDate = Carbon::now()->subDays(rand(1, 30));
                } elseif ($status === 'completed') {
                    $expiryDate = Carbon::now()->subDays(rand(1, 10));
                }
                
                Prescription::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'appointment_id' => $appointment?->id,
                    'medical_record_id' => $medicalRecord?->id,
                    'prescription_number' => $prescriptionNumber,
                    'medication_name' => $medication['name'],
                    'dosage' => $medication['dosage'],
                    'frequency' => $medication['frequency'],
                    'duration' => $medication['duration'],
                    'instructions' => $instructions[array_rand($instructions)],
                    'status' => $status,
                    'prescribed_date' => $prescribedDate,
                    'expiry_date' => $expiryDate,
                    'notes' => rand(0, 1) ? 'Monitor for side effects. Contact doctor if any adverse reactions occur.' : null
                ]);
            }
        }

        $this->command->info('Prescriptions seeded successfully!');
    }
}
