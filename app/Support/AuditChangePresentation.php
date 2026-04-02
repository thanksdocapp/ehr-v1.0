<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Support\Str;

/**
 * Presents UserActivity old_values / new_values in a human-readable way for audit UI.
 */
class AuditChangePresentation
{
    /**
     * @return list<array{key: string, label: string, before: string, after: string}>
     */
    public static function buildRows(?array $oldValues, ?array $newValues): array
    {
        $old = $oldValues ?? [];
        $new = $newValues ?? [];

        $ctx = self::preloadContext($old, $new);

        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));

        usort($keys, function (string $a, string $b): int {
            if ($a === 'edit_reason') {
                return -1;
            }
            if ($b === 'edit_reason') {
                return 1;
            }

            return strcasecmp(self::fieldLabel($a), self::fieldLabel($b));
        });

        $rows = [];
        foreach ($keys as $key) {
            $beforeRaw = $old[$key] ?? null;
            $afterRaw = $new[$key] ?? null;
            if (self::valuesEquivalent($beforeRaw, $afterRaw)) {
                continue;
            }
            $rows[] = [
                'key' => $key,
                'label' => self::fieldLabel($key),
                'before' => self::formatValue($key, $beforeRaw, $ctx),
                'after' => self::formatValue($key, $afterRaw, $ctx),
            ];
        }

        return $rows;
    }

    /**
     * @return array{patients: \Illuminate\Support\Collection, doctors: \Illuminate\Support\Collection, appointments: \Illuminate\Support\Collection}
     */
    private static function preloadContext(array $old, array $new): array
    {
        $patientIds = self::collectIds($old, $new, 'patient_id');
        $doctorIds = self::collectIds($old, $new, 'doctor_id');
        $appointmentIds = self::collectIds($old, $new, 'appointment_id');

        return [
            'patients' => $patientIds->isEmpty()
                ? collect()
                : Patient::query()->whereIn('id', $patientIds)->get()->keyBy('id'),
            'doctors' => $doctorIds->isEmpty()
                ? collect()
                : Doctor::query()->with('user')->whereIn('id', $doctorIds)->get()->keyBy('id'),
            'appointments' => $appointmentIds->isEmpty()
                ? collect()
                : Appointment::query()->whereIn('id', $appointmentIds)->get()->keyBy('id'),
        ];
    }

    private static function collectIds(array $old, array $new, string $key): \Illuminate\Support\Collection
    {
        $ids = collect([$old[$key] ?? null, $new[$key] ?? null])
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->map(fn ($v) => is_numeric($v) ? (int) $v : null)
            ->filter()
            ->unique()
            ->values();

        return $ids;
    }

    private static function valuesEquivalent(mixed $a, mixed $b): bool
    {
        if ($a === null && $b === null) {
            return true;
        }
        if ($a === '' && $b === '') {
            return true;
        }
        if (is_numeric($a) && is_numeric($b) && (string) $a === (string) $b) {
            return true;
        }

        return json_encode(self::normalizeScalar($a)) === json_encode(self::normalizeScalar($b));
    }

    private static function normalizeScalar(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }
        if (is_array($value)) {
            return $value;
        }

        return $value;
    }

    public static function fieldLabel(string $key): string
    {
        $map = [
            'edit_reason' => 'Reason for this change',
            'patient_id' => 'Patient',
            'doctor_id' => 'Doctor',
            'appointment_id' => 'Appointment',
            'record_type' => 'Record type',
            'record_date' => 'Record date',
            'presenting_complaint' => 'Presenting complaint',
            'history_of_presenting_complaint' => 'History of presenting complaint',
            'past_medical_history' => 'Past medical history',
            'drug_history' => 'Drug history',
            'allergies' => 'Allergies',
            'social_history' => 'Social history',
            'family_history' => 'Family history',
            'ideas_concerns_expectations' => 'Ideas, concerns & expectations',
            'plan' => 'Plan',
            'diagnosis' => 'Diagnosis',
            'symptoms' => 'Symptoms',
            'treatment' => 'Treatment',
            'notes' => 'Notes',
            'vital_signs' => 'Vital signs',
            'follow_up_date' => 'Follow-up date',
            'is_private' => 'Private record',
            'created_by' => 'Created by (user id)',
            'updated_by' => 'Updated by (user id)',
        ];

        return $map[$key] ?? Str::title(str_replace('_', ' ', $key));
    }

    /**
     * @param  array{patients: Collection<int, Patient>, doctors: Collection<int, Doctor>, appointments: Collection<int, Appointment>}  $ctx
     */
    private static function formatValue(string $key, mixed $value, array $ctx): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if ($key === 'is_private') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No';
        }

        if (Str::endsWith($key, '_date') && is_string($value) && $value !== '') {
            try {
                return formatDateUkLong($value);
            } catch (\Throwable) {
                return $value;
            }
        }

        if ($key === 'record_type' && is_string($value)) {
            return Str::title(str_replace('_', ' ', $value));
        }

        if ($key === 'patient_id' && is_numeric($value)) {
            $id = (int) $value;
            $p = $ctx['patients']->get($id);
            if ($p) {
                return trim($p->first_name.' '.$p->last_name).' (#'.$id.')';
            }

            return 'Patient #'.$id;
        }

        if ($key === 'doctor_id' && is_numeric($value)) {
            $id = (int) $value;
            $d = $ctx['doctors']->get($id);
            if ($d) {
                $name = $d->user->name ?? trim(($d->first_name ?? '').' '.($d->last_name ?? ''));

                return ($name !== '' ? $name : 'Doctor').' (#'.$id.')';
            }

            return 'Doctor #'.$id;
        }

        if ($key === 'appointment_id' && is_numeric($value)) {
            $id = (int) $value;
            $a = $ctx['appointments']->get($id);
            if ($a && !empty($a->appointment_number)) {
                return $a->appointment_number.' (#'.$id.')';
            }

            return 'Appointment #'.$id;
        }

        if (is_array($value)) {
            if ($value === []) {
                return '—';
            }

            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return is_string($value) ? $value : (string) json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
