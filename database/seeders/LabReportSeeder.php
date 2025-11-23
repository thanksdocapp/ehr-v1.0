<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LabReport;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Carbon\Carbon;

class LabReportSeeder extends Seeder
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

        $labTests = [
            [
                'type' => 'blood_test',
                'name' => 'Complete Blood Count (CBC)',
                'results' => [
                    'white_blood_cells' => rand(4000, 11000) . ' /μL',
                    'red_blood_cells' => rand(450, 550) / 100 . ' million/μL',
                    'hemoglobin' => rand(120, 160) / 10 . ' g/dL',
                    'hematocrit' => rand(36, 48) . '%',
                    'platelets' => rand(150000, 450000) . ' /μL'
                ],
                'reference_values' => [
                    'white_blood_cells' => '4,000-11,000 /μL',
                    'red_blood_cells' => '4.5-5.5 million/μL',
                    'hemoglobin' => '12-16 g/dL',
                    'hematocrit' => '36-48%',
                    'platelets' => '150,000-450,000 /μL'
                ]
            ],
            [
                'type' => 'blood_test',
                'name' => 'Basic Metabolic Panel',
                'results' => [
                    'glucose' => rand(70, 140) . ' mg/dL',
                    'sodium' => rand(135, 145) . ' mEq/L',
                    'potassium' => rand(35, 50) / 10 . ' mEq/L',
                    'chloride' => rand(98, 107) . ' mEq/L',
                    'creatinine' => rand(6, 12) / 10 . ' mg/dL'
                ],
                'reference_values' => [
                    'glucose' => '70-100 mg/dL',
                    'sodium' => '135-145 mEq/L',
                    'potassium' => '3.5-5.0 mEq/L',
                    'chloride' => '98-107 mEq/L',
                    'creatinine' => '0.6-1.2 mg/dL'
                ]
            ],
            [
                'type' => 'blood_test',
                'name' => 'Lipid Panel',
                'results' => [
                    'total_cholesterol' => rand(150, 250) . ' mg/dL',
                    'ldl_cholesterol' => rand(70, 160) . ' mg/dL',
                    'hdl_cholesterol' => rand(40, 80) . ' mg/dL',
                    'triglycerides' => rand(50, 200) . ' mg/dL'
                ],
                'reference_values' => [
                    'total_cholesterol' => '<200 mg/dL',
                    'ldl_cholesterol' => '<100 mg/dL',
                    'hdl_cholesterol' => '>40 mg/dL (M), >50 mg/dL (F)',
                    'triglycerides' => '<150 mg/dL'
                ]
            ],
            [
                'type' => 'urine_test',
                'name' => 'Urinalysis',
                'results' => [
                    'color' => 'Yellow',
                    'clarity' => 'Clear',
                    'specific_gravity' => '1.0' . rand(10, 25),
                    'protein' => rand(0, 1) ? 'Negative' : 'Trace',
                    'glucose' => 'Negative',
                    'ketones' => 'Negative',
                    'blood' => rand(0, 1) ? 'Negative' : 'Trace'
                ],
                'reference_values' => [
                    'color' => 'Pale yellow to amber',
                    'clarity' => 'Clear',
                    'specific_gravity' => '1.010-1.025',
                    'protein' => 'Negative',
                    'glucose' => 'Negative',
                    'ketones' => 'Negative',
                    'blood' => 'Negative'
                ]
            ],
            [
                'type' => 'imaging',
                'name' => 'Chest X-Ray',
                'results' => [
                    'findings' => 'Normal heart size and lung fields',
                    'impression' => 'No acute cardiopulmonary abnormality'
                ],
                'reference_values' => [
                    'normal' => 'Clear lung fields, normal cardiac silhouette'
                ]
            ]
        ];

        $statuses = ['pending', 'completed', 'reviewed'];
        $technicians = ['Dr. Smith Lab', 'Sarah Johnson, MT', 'Michael Brown, MLT', 'Lisa Davis, CLS'];

        // Create lab reports for patients
        foreach ($patients as $patient) {
            $reportCount = rand(1, 3); // 1-3 reports per patient
            
            for ($i = 0; $i < $reportCount; $i++) {
                $doctor = $doctors->random();
                $appointment = Appointment::where('patient_id', $patient->id)->first();
                $medicalRecord = $medicalRecords->where('patient_id', $patient->id)->first();
                $labTest = $labTests[array_rand($labTests)];
                
                $testDate = Carbon::now()->subDays(rand(1, 60));
                
                // Generate report number
                $reportNumber = 'LAB-' . date('Y') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                
                LabReport::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'appointment_id' => $appointment?->id,
                    'medical_record_id' => $medicalRecord?->id,
                    'report_number' => $reportNumber,
                    'test_type' => $labTest['type'],
                    'test_name' => $labTest['name'],
                    'test_date' => $testDate,
                    'results' => $labTest['results'],
                    'reference_values' => $labTest['reference_values'],
                    'status' => $statuses[array_rand($statuses)],
                    'technician_name' => $technicians[array_rand($technicians)],
                    'notes' => rand(0, 1) ? 'All values within normal limits. Recommend routine follow-up.' : null,
                    'file_path' => null // We're not creating actual files for test data
                ]);
            }
        }

        $this->command->info('Lab reports seeded successfully!');
    }
}
