<?php

namespace App\Support;

use App\Models\MedicalRecord;
use Carbon\CarbonInterface;
use DateTimeInterface;

/**
 * Builds old/new snapshots for medical record updates (UserActivity audit trail).
 */
class MedicalRecordAuditDiff
{
    /**
     * @param  array<string, mixed>  $updateData  Keys must match model attributes (e.g. from controller update payload).
     * @return array{old_values: array<string, mixed>, new_values: array<string, mixed>}
     */
    public static function build(MedicalRecord $record, array $updateData): array
    {
        $old = [];
        $new = [];

        $attributes = $record->getAttributes();

        foreach ($updateData as $key => $incoming) {
            if ($key === 'updated_by') {
                continue;
            }

            if (!array_key_exists($key, $attributes)) {
                continue;
            }

            $previous = $record->getAttribute($key);
            if (!self::valuesAreEqual($previous, $incoming)) {
                $old[$key] = self::normalizeForAudit($previous);
                $new[$key] = self::normalizeForAudit($incoming);
            }
        }

        return [
            'old_values' => $old,
            'new_values' => $new,
        ];
    }

    private static function valuesAreEqual(mixed $a, mixed $b): bool
    {
        return json_encode(self::normalizeForAudit($a)) === json_encode(self::normalizeForAudit($b));
    }

    /**
     * Values suitable for JSON storage (decrypted strings preserved for clinical fields).
     */
    private static function normalizeForAudit(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d');
        }
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_array($value)) {
            return $value;
        }

        return $value;
    }
}
