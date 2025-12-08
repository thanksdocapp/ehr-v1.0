<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TemplateMergeService
{
    /**
     * Merge patient data into template placeholders.
     *
     * @param Template $template
     * @param Patient $patient
     * @param User|null $doctor
     * @param array $customData Additional custom placeholder values
     * @return string
     */
    public function merge(Template $template, Patient $patient, ?User $doctor = null, array $customData = []): string
    {
        $doctor = $doctor ?? Auth::user();
        $content = $template->content;

        // Build placeholder data
        $placeholders = $this->buildPlaceholderData($patient, $doctor, $customData);

        // Replace all placeholders
        foreach ($placeholders as $placeholder => $value) {
            $content = str_replace($placeholder, $value ?? '', $content);
        }

        return $content;
    }

    /**
     * Build the placeholder data array from patient and doctor models.
     *
     * @param Patient $patient
     * @param User|null $doctor
     * @param array $customData
     * @return array
     */
    protected function buildPlaceholderData(Patient $patient, ?User $doctor, array $customData = []): array
    {
        $data = [];

        // Patient placeholders
        $data['{{patient_name}}'] = $patient->full_name ?? ($patient->first_name . ' ' . $patient->last_name);
        $data['{{patient_first_name}}'] = $patient->first_name ?? '';
        $data['{{patient_last_name}}'] = $patient->last_name ?? '';
        $data['{{patient_email}}'] = $patient->email ?? '';
        $data['{{patient_phone}}'] = $patient->phone ?? '';
        $data['{{patient_dob}}'] = $patient->date_of_birth ? $patient->date_of_birth->format('d/m/Y') : '';
        $data['{{patient_age}}'] = $this->calculateAge($patient);
        $data['{{patient_gender}}'] = ucfirst($patient->gender ?? '');
        $data['{{patient_address}}'] = $this->formatPatientAddress($patient);
        $data['{{patient_id}}'] = $patient->patient_id ?? $patient->id;

        // Doctor placeholders
        if ($doctor) {
            $doctorModel = $doctor->doctor ?? null;
            $data['{{doctor_name}}'] = $doctor->name ?? '';
            $data['{{doctor_email}}'] = $doctor->email ?? '';
            $data['{{doctor_specialization}}'] = $doctorModel?->specialization ?? '';
            $data['{{doctor_qualification}}'] = $doctorModel?->qualification ?? '';
            $data['{{doctor_phone}}'] = $doctorModel?->phone ?? $doctor->phone ?? '';
        } else {
            $data['{{doctor_name}}'] = '';
            $data['{{doctor_email}}'] = '';
            $data['{{doctor_specialization}}'] = '';
            $data['{{doctor_qualification}}'] = '';
            $data['{{doctor_phone}}'] = '';
        }

        // Clinic placeholders (from settings)
        $data['{{clinic_name}}'] = $this->getClinicSetting('hospital_name', config('app.name'));
        $data['{{clinic_address}}'] = $this->getClinicSetting('hospital_address', '');
        $data['{{clinic_phone}}'] = $this->getClinicSetting('hospital_phone', '');
        $data['{{clinic_email}}'] = $this->getClinicSetting('hospital_email', '');

        // Date/Time placeholders
        $data['{{current_date}}'] = now()->format('d/m/Y');
        $data['{{current_time}}'] = now()->format('H:i');
        $data['{{current_datetime}}'] = now()->format('d/m/Y H:i');
        $data['{{current_date_long}}'] = now()->format('jS F Y');

        // Merge custom data
        foreach ($customData as $key => $value) {
            // Ensure key is wrapped in {{ }}
            if (!str_starts_with($key, '{{')) {
                $key = '{{' . $key . '}}';
            }
            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * Calculate patient age.
     */
    protected function calculateAge(Patient $patient): string
    {
        if (!$patient->date_of_birth) {
            return '';
        }

        return (string) $patient->date_of_birth->age;
    }

    /**
     * Format patient address.
     */
    protected function formatPatientAddress(Patient $patient): string
    {
        $parts = array_filter([
            $patient->address ?? null,
            $patient->city ?? null,
            $patient->state ?? null,
            $patient->postal_code ?? null,
            $patient->country ?? null,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get clinic setting from SiteSetting or Setting model.
     */
    protected function getClinicSetting(string $key, $default = ''): string
    {
        // Try SiteSetting first
        if (class_exists(\App\Models\SiteSetting::class)) {
            $value = \App\Models\SiteSetting::get($key);
            if ($value) {
                return $value;
            }
        }

        // Try Setting model
        if (class_exists(\App\Models\Setting::class)) {
            $value = \App\Models\Setting::get($key);
            if ($value) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Get list of all available placeholders with descriptions.
     */
    public function getAvailablePlaceholders(): array
    {
        return Template::DEFAULT_PLACEHOLDERS;
    }

    /**
     * Preview merged content without saving.
     */
    public function preview(Template $template, Patient $patient, ?User $doctor = null, array $customData = []): string
    {
        return $this->merge($template, $patient, $doctor, $customData);
    }

    /**
     * Validate that all required placeholders in template have values.
     */
    public function validatePlaceholders(Template $template, Patient $patient, ?User $doctor = null): array
    {
        $doctor = $doctor ?? Auth::user();
        $placeholderData = $this->buildPlaceholderData($patient, $doctor);

        $usedPlaceholders = $template->extractUsedPlaceholders();
        $missingValues = [];

        foreach ($usedPlaceholders as $placeholder) {
            if (!isset($placeholderData[$placeholder]) || empty($placeholderData[$placeholder])) {
                $missingValues[] = $placeholder;
            }
        }

        return $missingValues;
    }
}
