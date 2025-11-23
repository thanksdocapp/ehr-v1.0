<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\User;

class UpdatePatientP20259405 extends Seeder
{
    /**
     * Run the database seeds.
     * Manually update patient P20259405 to be linked to Dr. EMP0006
     */
    public function run(): void
    {
        $this->command->info('Updating patient P20259405...');
        
        // Find the patient
        $patient = Patient::where('patient_id', 'P20259405')->first();
        
        if (!$patient) {
            $this->command->error('Patient P20259405 not found!');
            return;
        }
        
        // Find the doctor with employee_id EMP0006
        $user = User::where('employee_id', 'EMP0006')->first();
        
        if (!$user) {
            $this->command->error('User with employee_id EMP0006 not found!');
            return;
        }
        
        // Find the doctor record linked to this user
        $doctor = Doctor::where('user_id', $user->id)->first();
        
        if (!$doctor) {
            $this->command->error('Doctor record not found for user EMP0006!');
            return;
        }
        
        $this->command->info("Found doctor: {$doctor->first_name} {$doctor->last_name} (ID: {$doctor->id}, Department: {$doctor->department_id})");
        
        // Update the patient
        $updateData = [];
        
        if (!$patient->department_id && $doctor->department_id) {
            $updateData['department_id'] = $doctor->department_id;
        }
        
        if (!$patient->created_by_doctor_id) {
            $updateData['created_by_doctor_id'] = $doctor->id;
        }
        
        if (!$patient->assigned_doctor_id) {
            $updateData['assigned_doctor_id'] = $doctor->id;
        }
        
        if (!empty($updateData)) {
            $patient->update($updateData);
            $this->command->info('Patient updated: ' . json_encode($updateData));
        } else {
            $this->command->info('Patient already has all fields set. Current values:');
            $this->command->info('  Department: ' . ($patient->department_id ?? 'NULL'));
            $this->command->info('  Created By Doctor: ' . ($patient->created_by_doctor_id ?? 'NULL'));
            $this->command->info('  Assigned Doctor: ' . ($patient->assigned_doctor_id ?? 'NULL'));
        }
        
        $this->command->info('Update completed!');
    }
}

