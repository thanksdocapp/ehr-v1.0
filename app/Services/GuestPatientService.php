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
        // Try to find existing patient by email first (email is unique)
        $patient = Patient::where('email', $data['email'])->first();

        if ($patient) {
            // Update phone if different
            if ($patient->phone !== $data['phone']) {
                $patient->phone = $data['phone'];
            }
            // Update name if different
            if ($patient->first_name !== $data['first_name']) {
                $patient->first_name = $data['first_name'];
            }
            if ($patient->last_name !== $data['last_name']) {
                $patient->last_name = $data['last_name'];
            }
            if (!empty($data['date_of_birth'])) {
                $patient->date_of_birth = $data['date_of_birth'];
            }
            if (!empty($data['gender'])) {
                $patient->gender = $data['gender'];
            }
            if (!empty($data['address'])) {
                $patient->address = $data['address'];
            }
            if (!empty($data['city'])) {
                $patient->city = $data['city'];
            }
            if (array_key_exists('state', $data) && $data['state'] !== null && (string) $data['state'] !== '') {
                $patient->state = $data['state'];
            }
            if (!empty($data['postal_code'])) {
                $patient->postal_code = $data['postal_code'];
            }
            if (!empty($data['country'])) {
                $patient->country = $data['country'];
            }
            if (!empty($data['guardian_name'])) {
                $patient->guardian_name = $data['guardian_name'];
            }
            if (!empty($data['guardian_phone'])) {
                $patient->guardian_phone = $data['guardian_phone'];
            }
            $patient->save();

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
        if (isset($data['address']) && $data['address'] !== null && (string) $data['address'] !== '') {
            $patientData['address'] = $data['address'];
        }
        if (!empty($data['city'])) {
            $patientData['city'] = $data['city'];
        }
        if (array_key_exists('state', $data) && $data['state'] !== null && (string) $data['state'] !== '') {
            $patientData['state'] = $data['state'];
        }
        if (!empty($data['postal_code'])) {
            $patientData['postal_code'] = $data['postal_code'];
        }
        if (!empty($data['country'])) {
            $patientData['country'] = $data['country'];
        }
        if (!empty($data['guardian_name'])) {
            $patientData['guardian_name'] = $data['guardian_name'];
        }
        if (!empty($data['guardian_phone'])) {
            $patientData['guardian_phone'] = $data['guardian_phone'];
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
                throw new \Exception("Field {$field} is required to complete the provisional profile.");
            }
        }

        // Merge additional data
        $patient->fill($additionalData);
        $patient->is_guest = false;
        
        return $patient->save();
    }
}

