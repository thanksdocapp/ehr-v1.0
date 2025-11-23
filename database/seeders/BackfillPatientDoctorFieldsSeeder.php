<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\MedicalRecord;

class BackfillPatientDoctorFieldsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Backfills created_by_doctor_id, assigned_doctor_id, and department_id for existing patients.
     */
    public function run(): void
    {
        $this->command->info('Starting to backfill patient doctor fields...');
        
        $patients = Patient::all();
        $updatedCount = 0;
        
        foreach ($patients as $patient) {
            $updateData = [];
            
            // If patient already has all fields set, skip
            if ($patient->department_id && $patient->created_by_doctor_id && $patient->assigned_doctor_id) {
                continue;
            }
            
            // Try to get department and doctor from appointments
            $firstAppointment = Appointment::where('patient_id', $patient->id)
                ->with('doctor')
                ->orderBy('created_at', 'asc')
                ->first();
            
            if ($firstAppointment && $firstAppointment->doctor) {
                $doctor = $firstAppointment->doctor;
                
                // Set department_id if not set
                if (!$patient->department_id && $doctor->department_id) {
                    $updateData['department_id'] = $doctor->department_id;
                }
                
                // Set created_by_doctor_id if not set (use first appointment's doctor as creator)
                if (!$patient->created_by_doctor_id) {
                    $updateData['created_by_doctor_id'] = $doctor->id;
                }
                
                // Set assigned_doctor_id if not set (use most recent appointment's doctor)
                if (!$patient->assigned_doctor_id) {
                    $latestAppointment = Appointment::where('patient_id', $patient->id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    if ($latestAppointment && $latestAppointment->doctor_id) {
                        $updateData['assigned_doctor_id'] = $latestAppointment->doctor_id;
                    } else {
                        $updateData['assigned_doctor_id'] = $doctor->id;
                    }
                }
            } else {
                // Try to get from medical records if no appointments
                $firstMedicalRecord = MedicalRecord::where('patient_id', $patient->id)
                    ->with('doctor')
                    ->orderBy('created_at', 'asc')
                    ->first();
                
                if ($firstMedicalRecord && $firstMedicalRecord->doctor) {
                    $doctor = $firstMedicalRecord->doctor;
                    
                    // Set department_id if not set
                    if (!$patient->department_id && $doctor->department_id) {
                        $updateData['department_id'] = $doctor->department_id;
                    }
                    
                    // Set created_by_doctor_id if not set
                    if (!$patient->created_by_doctor_id) {
                        $updateData['created_by_doctor_id'] = $doctor->id;
                    }
                    
                    // Set assigned_doctor_id if not set
                    if (!$patient->assigned_doctor_id) {
                        $updateData['assigned_doctor_id'] = $doctor->id;
                    }
                } else {
                    // No appointments or medical records - try to infer from patient's created_at
                    // Find doctors who were active around the time the patient was created
                    $patientCreatedAt = $patient->created_at;
                    
                    // This is a fallback - we can't reliably determine the creator
                    // So we'll leave it null and let the system handle it on next interaction
                    $this->command->warn("Patient {$patient->patient_id} has no appointments or medical records. Skipping backfill.");
                    continue;
                }
            }
            
            // Update patient if we have data to update
            if (!empty($updateData)) {
                $patient->update($updateData);
                $updatedCount++;
                $this->command->info("Updated patient {$patient->patient_id}: " . json_encode($updateData));
            }
        }
        
        $this->command->info("Backfill completed. Updated {$updatedCount} patients.");
    }
}
