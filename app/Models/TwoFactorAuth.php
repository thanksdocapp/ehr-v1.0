<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TwoFactorAuth extends Model
{
    use HasFactory;

    protected $table = 'two_factor_auth';

    protected $fillable = [
        'user_id',
        'enabled',
        'secret',
        'recovery_codes',
        'confirmed_at',
        'method', // 'email' or 'authenticator'
        'last_used_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'recovery_codes' => 'array',
        'confirmed_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'secret',
        'recovery_codes',
    ];

    /**
     * Get the user that owns the 2FA record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a random 6-digit code for email 2FA.
     */
    public static function generateCode(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Store a 2FA code in cache for verification.
     */
    public function storeCode(string $code, int $expiryMinutes = 10): void
    {
        $key = "2fa_code_{$this->user_id}";
        Cache::put($key, [
            'code' => $code,
            'attempts' => 0,
            'created_at' => now(),
        ], now()->addMinutes($expiryMinutes));
    }

    /**
     * Verify a 2FA code.
     */
    public function verifyCode(string $code): bool
    {
        $key = "2fa_code_{$this->user_id}";
        $data = Cache::get($key);

        if (!$data) {
            return false;
        }

        // Check if too many attempts
        if ($data['attempts'] >= 5) {
            Cache::forget($key);
            return false;
        }

        // Increment attempts
        $data['attempts']++;
        Cache::put($key, $data, now()->addMinutes(10));

        // Verify code
        if ($data['code'] === $code) {
            Cache::forget($key);
            $this->update(['last_used_at' => now()]);
            return true;
        }

        return false;
    }

    /**
     * Clear any stored 2FA code.
     */
    public function clearCode(): void
    {
        $key = "2fa_code_{$this->user_id}";
        Cache::forget($key);
    }

    /**
     * Generate recovery codes.
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        }
        return $codes;
    }

    /**
     * Verify a recovery code.
     */
    public function verifyRecoveryCode(string $code): bool
    {
        $codes = $this->recovery_codes ?? [];
        $code = strtoupper(trim($code));

        if (in_array($code, $codes)) {
            // Remove used recovery code
            $codes = array_values(array_diff($codes, [$code]));
            $this->update(['recovery_codes' => $codes]);
            return true;
        }

        return false;
    }

    /**
     * Check if 2FA is enabled and confirmed.
     */
    public function isActive(): bool
    {
        return $this->enabled && $this->confirmed_at !== null;
    }

    /**
     * Check if 2FA code has expired.
     */
    public function hasCodeExpired(): bool
    {
        $key = "2fa_code_{$this->user_id}";
        $data = Cache::get($key);

        if (!$data) {
            return true;
        }

        return $data['created_at']->addMinutes(10)->isPast();
    }

    /**
     * Get remaining recovery codes count.
     */
    public function getRemainingRecoveryCodesAttribute(): int
    {
        return count($this->recovery_codes ?? []);
    }
}

