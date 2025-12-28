<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Safe encrypted cast.
 *
 * Why: Some deployments have legacy/plaintext values in columns that are now
 * marked as encrypted. Laravel's built-in 'encrypted' cast will throw a
 * DecryptException on read, breaking pages (e.g. admin patient view).
 *
 * Behavior:
 * - On get(): try decrypt; if payload is invalid, return raw value.
 * - On set(): if value already looks decryptable, store as-is; otherwise encrypt.
 */
class SafeEncrypted implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        try {
            // Try decryptString first (common for string columns)
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Fall back to raw value for legacy/plaintext rows
            // Log the error for debugging but don't throw
            \Log::debug('SafeEncrypted: Failed to decrypt field', [
                'key' => $key,
                'model' => get_class($model),
                'model_id' => $model->id ?? null,
                'error' => $e->getMessage()
            ]);
            return $value;
        } catch (\Throwable $e) {
            // Any other unexpected failure: safest is to return raw value
            // Log the error for debugging but don't throw
            \Log::debug('SafeEncrypted: Unexpected error decrypting field', [
                'key' => $key,
                'model' => get_class($model),
                'model_id' => $model->id ?? null,
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ]);
            return $value;
        }
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return '';
            }

            // If it's already an encrypted payload (decrypt succeeds), keep as-is to avoid double-encryption.
            try {
                Crypt::decryptString($value);
                return $value;
            } catch (\Throwable $e) {
                // Not decryptable -> encrypt below
            }

            return Crypt::encryptString($value);
        }

        // For non-string values, attempt to encrypt via serialize-friendly encrypt()
        try {
            return Crypt::encrypt($value);
        } catch (\Throwable $e) {
            // As a last resort, cast to string and encrypt
            return Crypt::encryptString((string) $value);
        }
    }
}


