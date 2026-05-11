<?php

namespace App\Support;

class PatientContactNormalizer
{
    public static function normalizePhone(?string $phone): string
    {
        if ($phone === null || $phone === '') {
            return '';
        }

        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    public static function normalizeName(?string $name): string
    {
        return mb_strtolower(trim((string) $name));
    }
}
