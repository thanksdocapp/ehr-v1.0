<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Str;

class GuestPatientService
{
    /**
     * Find or create a guest patient.
     *
     * @param array $data
     * @return Patient
     */
    public function findOrCreateGuest(array $data)
    {
        // Try to find existing patient by email and phone
        $patient = Patient::where('email', $data['email'])
            ->where('phone', $data['phone'])
            ->first();

        if ($patient) {
            // If found but is a guest, we can still use it
            // If found and is not a guest, use it (existing patient)
            return $patient;
        }

        // Create new guest patient
        $patientData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'is_active' => true,
            'patient_id' => Patient::generatePatientId(),
        ];
        
        // Only set is_guest if column exists
        if (\Illuminate\Support\Facades\Schema::hasColumn('patients', 'is_guest')) {
            $patientData['is_guest'] = true;
        }
        
        // For guest patients, provide placeholder values for required fields if not provided
        // These will be updated when converting to full patient
        
        // Handle date_of_birth - use provided value or placeholder
        if (isset($data['date_of_birth']) && !empty($data['date_of_birth'])) {
            $patientData['date_of_birth'] = $data['date_of_birth'];
        } else {
            // Use a placeholder date that's clearly a placeholder (will be updated later)
            // Using a date far in the past that's unlikely to be a real birth date
            $patientData['date_of_birth'] = '1900-01-01';
        }
        
        // Handle gender - use provided value or placeholder
        if (isset($data['gender']) && !empty($data['gender'])) {
            $patientData['gender'] = $data['gender'];
        } else {
            // Use 'other' as placeholder (will be updated when converting to full patient)
            $patientData['gender'] = 'other';
        }
        
        // Optional fields if provided
        if (isset($data['address'])) {
            $patientData['address'] = $data['address'];
        }

        $patient = Patient::create($patientData);

        return $patient;
    }

    /**
     * Convert a guest patient to a full patient.
     *
     * @param Patient $patient
     * @param array $additionalData
     * @return bool
     */
    public function convertToFullPatient(Patient $patient, array $additionalData = [])
    {
        if (!$patient->is_guest) {
            return false; // Already a full patient
        }

        // Validate required fields
        $requiredFields = ['date_of_birth', 'gender'];
        foreach ($requiredFields as $field) {
            if (empty($patient->$field) && empty($additionalData[$field])) {
                throw new \Exception("Field {$field} is required to convert guest to full patient.");
            }
        }

        // Merge additional data
        $patient->fill($additionalData);
        $patient->is_guest = false;
        
        return $patient->save();
    }
}

