<?php

namespace App\Services;

use App\Models\Patient;
use App\Support\PatientContactNormalizer;
use Carbon\Carbon;
use Illuminate\Support\Str;

class GuestPatientService
{
    /**
     * Find or create a guest patient.
     *
     * Email and phone are not globally unique: we only reuse a row when the same
     * individual is booking again (email + normalized phone + name + DOB alignment).
     * Different people sharing an email or phone always get separate records.
     *
     * @param array $data
     * @return Patient
     */
    public function findOrCreateGuest(array $data)
    {
        $patient = $this->tryFindMatchingGuest($data);

        if ($patient) {
            if ($patient->phone !== ($data['phone'] ?? null)) {
                $patient->phone = $data['phone'];
            }
            if ($patient->first_name !== $data['first_name']) {
                $patient->first_name = $data['first_name'];
            }
            if ($patient->last_name !== $data['last_name']) {
                $patient->last_name = $data['last_name'];
            }
            if (! empty($data['date_of_birth'])) {
                $patient->date_of_birth = $data['date_of_birth'];
            }
            if (! empty($data['gender'])) {
                $patient->gender = $data['gender'];
            }
            if (! empty($data['address'])) {
                $patient->address = $data['address'];
            }
            if (! empty($data['city'])) {
                $patient->city = $data['city'];
            }
            if (array_key_exists('state', $data) && $data['state'] !== null && (string) $data['state'] !== '') {
                $patient->state = $data['state'];
            }
            if (! empty($data['postal_code'])) {
                $patient->postal_code = $data['postal_code'];
            }
            if (! empty($data['country'])) {
                $patient->country = $data['country'];
            }
            if (! empty($data['guardian_name'])) {
                $patient->guardian_name = $data['guardian_name'];
            }
            if (! empty($data['guardian_phone'])) {
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

        if (\Illuminate\Support\Facades\Schema::hasColumn('patients', 'is_guest')) {
            $patientData['is_guest'] = true;
        }

        if (isset($data['date_of_birth']) && ! empty($data['date_of_birth'])) {
            $patientData['date_of_birth'] = $data['date_of_birth'];
        } else {
            $patientData['date_of_birth'] = '1900-01-01';
        }

        if (isset($data['gender']) && ! empty($data['gender'])) {
            $patientData['gender'] = $data['gender'];
        } else {
            $patientData['gender'] = 'other';
        }

        if (isset($data['address']) && $data['address'] !== null && (string) $data['address'] !== '') {
            $patientData['address'] = $data['address'];
        }
        if (! empty($data['city'])) {
            $patientData['city'] = $data['city'];
        }
        if (array_key_exists('state', $data) && $data['state'] !== null && (string) $data['state'] !== '') {
            $patientData['state'] = $data['state'];
        }
        if (! empty($data['postal_code'])) {
            $patientData['postal_code'] = $data['postal_code'];
        }
        if (! empty($data['country'])) {
            $patientData['country'] = $data['country'];
        }
        if (! empty($data['guardian_name'])) {
            $patientData['guardian_name'] = $data['guardian_name'];
        }
        if (! empty($data['guardian_phone'])) {
            $patientData['guardian_phone'] = $data['guardian_phone'];
        }

        return Patient::create($patientData);
    }

    /**
     * Match only when this is plausibly the same guest person (not a relative sharing email/phone).
     */
    private function tryFindMatchingGuest(array $data): ?Patient
    {
        $email = $data['email'] ?? null;
        if (! $email) {
            return null;
        }

        $fn = PatientContactNormalizer::normalizeName($data['first_name'] ?? '');
        $ln = PatientContactNormalizer::normalizeName($data['last_name'] ?? '');
        $phoneNorm = PatientContactNormalizer::normalizePhone($data['phone'] ?? '');

        $candidates = Patient::where('email', $email)->get();

        foreach ($candidates as $patient) {
            if (PatientContactNormalizer::normalizeName($patient->first_name) !== $fn) {
                continue;
            }
            if (PatientContactNormalizer::normalizeName($patient->last_name) !== $ln) {
                continue;
            }
            if (PatientContactNormalizer::normalizePhone($patient->phone) !== $phoneNorm) {
                continue;
            }
            if (! $this->guestDateOfBirthMatches($data['date_of_birth'] ?? null, $patient)) {
                continue;
            }

            return $patient;
        }

        return null;
    }

    private function guestDateOfBirthMatches(?string $incomingRaw, Patient $patient): bool
    {
        $placeholder = '1900-01-01';
        $storedRaw = $patient->date_of_birth
            ? $patient->date_of_birth->format('Y-m-d')
            : null;

        $inc = $incomingRaw ? Carbon::parse($incomingRaw)->format('Y-m-d') : null;

        // Incoming real DOB must not attach to a placeholder-only guest row (e.g. sibling / dependant).
        if ($inc && $inc !== $placeholder && $storedRaw === $placeholder) {
            return false;
        }

        if (! $inc && $storedRaw === $placeholder) {
            return true;
        }

        if (! $inc || ! $storedRaw) {
            return ($inc ?? $placeholder) === ($storedRaw ?? $placeholder);
        }

        return $inc === $storedRaw;
    }

    /**
     * Convert a guest patient to a full patient.
     *
     * @param array $additionalData
     * @return bool
     */
    public function convertToFullPatient(Patient $patient, array $additionalData = [])
    {
        if (! $patient->is_guest) {
            return false;
        }

        $requiredFields = ['date_of_birth', 'gender'];
        foreach ($requiredFields as $field) {
            if (empty($patient->$field) && empty($additionalData[$field])) {
                throw new \Exception("Field {$field} is required to complete the provisional profile.");
            }
        }

        $patient->fill($additionalData);
        $patient->is_guest = false;

        return $patient->save();
    }
}
