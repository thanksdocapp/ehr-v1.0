<?php

namespace App\Services;

use App\Models\User;
use App\Models\TwoFactorAuth;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;

class TwoFactorAuthService
{
    /**
     * Enable 2FA for a user.
     */
    public function enable(User $user, string $method = 'email'): TwoFactorAuth
    {
        $twoFactorAuth = TwoFactorAuth::firstOrNew(['user_id' => $user->id]);
        
        $twoFactorAuth->enabled = true;
        $twoFactorAuth->method = $method;
        $twoFactorAuth->recovery_codes = TwoFactorAuth::generateRecoveryCodes();
        $twoFactorAuth->confirmed_at = null; // Will be set after first successful verification
        $twoFactorAuth->save();

        // Update user table as well for compatibility
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => $twoFactorAuth->recovery_codes,
        ]);

        Log::info('2FA enabled for user', [
            'user_id' => $user->id,
            'method' => $method,
        ]);

        return $twoFactorAuth;
    }

    /**
     * Disable 2FA for a user.
     */
    public function disable(User $user): bool
    {
        $twoFactorAuth = \App\Models\TwoFactorAuth::where('user_id', $user->id)->first();

        if ($twoFactorAuth) {
            $twoFactorAuth->update([
                'enabled' => false,
                'confirmed_at' => null,
            ]);
            $twoFactorAuth->clearCode();
        }

        // Update user table as well
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        Log::info('2FA disabled for user', ['user_id' => $user->id]);

        return true;
    }

    /**
     * Send a 2FA code via email.
     */
    public function sendCode(User $user): bool
    {
        try {
            $twoFactorAuth = \App\Models\TwoFactorAuth::where('user_id', $user->id)->first();

            if (!$twoFactorAuth || !$twoFactorAuth->enabled) {
                Log::warning('Attempted to send 2FA code for user without 2FA enabled', [
                    'user_id' => $user->id,
                ]);
                return false;
            }

            // Generate and store code
            $code = TwoFactorAuth::generateCode();
            $twoFactorAuth->storeCode($code);

            // Ensure the 2FA email template exists and is active
            $template = EmailTemplate::where('name', 'two_factor_code')->first();
            if (!$template) {
                Log::info('Creating two_factor_code email template');
                $template = EmailTemplate::create([
                    'name' => 'two_factor_code',
                    'subject' => 'Your {{ hospital_name }} Two-Factor Authentication Code',
                    'body' => '<p>Hello {{ user_name }},</p>
<p>You are receiving this email because a two-factor authentication code was requested for your account on {{ hospital_name }}.</p>
<p><strong>Your verification code:</strong><br>
<span style="font-size: 24px; font-weight: bold; letter-spacing: 2px;">{{ verification_code }}</span></p>
<p>This code will expire in {{ expires_minutes }} minutes.</p>
<p>If you did not request this code, you can ignore this email. For your security, never share this code with anyone.</p>
<p>Thanks,<br>{{ hospital_name }}</p>',
                    'sender_name' => '{{ hospital_name }}',
                    'sender_email' => null,
                    'status' => 'active',
                    'category' => 'security',
                    'description' => 'Two-factor authentication code sent to users during login or when enabling 2FA.',
                ]);
                Log::info('Created two_factor_code template', ['template_id' => $template->id]);
            } elseif ($template->status !== 'active') {
                Log::info('Activating two_factor_code template', ['template_id' => $template->id]);
                $template->update(['status' => 'active']);
            }

            Log::info('Attempting to send 2FA email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'template_exists' => $template ? 'yes' : 'no',
                'template_status' => $template->status ?? 'unknown'
            ]);

            /** @var \App\Services\EmailNotificationService $emailService */
            $emailService = app(\App\Services\EmailNotificationService::class);
            $variables = [
                'user_name' => $user->name,
                'verification_code' => $code,
                'expires_minutes' => 10,
            ];
            $log = $emailService->sendTemplateEmail(
                'two_factor_code',
                [$user->email => $user->name],
                $variables
            );
            
            if (!$log) {
                Log::error('2FA email service returned null - template may not exist or be active', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'template' => 'two_factor_code',
                ]);
                return false;
            }
            
            // Refresh log to get latest status
            $log->refresh();
            
            if ($log->status !== 'sent') {
                Log::error('2FA template email send failed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'template' => 'two_factor_code',
                    'log_id' => $log->id,
                    'log_status' => $log->status,
                    'log_error' => $log->error_message,
                ]);
                return false;
            }

            Log::info('2FA code sent to user', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send 2FA code', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Verify a 2FA code.
     */
    public function verifyCode(User $user, string $code): bool
    {
        $twoFactorAuth = TwoFactorAuth::where('user_id', $user->id)->first();

        if (!$twoFactorAuth || !$twoFactorAuth->enabled) {
            return false;
        }

        $verified = $twoFactorAuth->verifyCode($code);

        if ($verified) {
            // Confirm 2FA if this is the first successful verification
            if (!$twoFactorAuth->confirmed_at) {
                $twoFactorAuth->update(['confirmed_at' => now()]);
                $user->update(['two_factor_confirmed_at' => now()]);
            }

            Log::info('2FA code verified successfully', ['user_id' => $user->id]);
        } else {
            Log::warning('Failed 2FA code verification', ['user_id' => $user->id]);
        }

        return $verified;
    }

    /**
     * Verify a recovery code.
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $twoFactorAuth = TwoFactorAuth::where('user_id', $user->id)->first();

        if (!$twoFactorAuth || !$twoFactorAuth->enabled) {
            return false;
        }

        $verified = $twoFactorAuth->verifyRecoveryCode($code);

        if ($verified) {
            Log::info('2FA recovery code used', [
                'user_id' => $user->id,
                'remaining_codes' => $twoFactorAuth->remaining_recovery_codes,
            ]);

            // Update user table as well
            $user->update(['two_factor_recovery_codes' => $twoFactorAuth->recovery_codes]);
        } else {
            Log::warning('Invalid 2FA recovery code attempted', ['user_id' => $user->id]);
        }

        return $verified;
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $twoFactorAuth = TwoFactorAuth::where('user_id', $user->id)->first();

        if (!$twoFactorAuth) {
            throw new \Exception('2FA is not enabled for this user');
        }

        $codes = TwoFactorAuth::generateRecoveryCodes();
        $twoFactorAuth->update(['recovery_codes' => $codes]);
        $user->update(['two_factor_recovery_codes' => $codes]);

        Log::info('2FA recovery codes regenerated', ['user_id' => $user->id]);

        return $codes;
    }

    /**
     * Check if user needs 2FA verification.
     */
    public function requiresTwoFactor(User $user): bool
    {
        $twoFactorAuth = TwoFactorAuth::where('user_id', $user->id)->first();

        if (!$twoFactorAuth) {
            return false;
        }

        return $twoFactorAuth->isActive();
    }

    /**
     * Check if 2FA is required by security settings.
     */
    public function isRequired(User $user): bool
    {
        // Do NOT force admins. Admins can enable 2FA voluntarily.
        if ($user->is_admin) {
            return false;
        }

        // Only force staff/users when the global force_2fa toggle is ON
        $forceStaff = \App\Models\Setting::get('force_2fa', false);
        return (bool) $forceStaff;
    }

    /**
     * Get 2FA status for a user.
     */
    public function getStatus(User $user): array
    {
        $twoFactorAuth = TwoFactorAuth::where('user_id', $user->id)->first();

        if (!$twoFactorAuth) {
            return [
                'enabled' => false,
                'confirmed' => false,
                'method' => null,
                'recovery_codes_count' => 0,
                'last_used' => null,
            ];
        }

        return [
            'enabled' => $twoFactorAuth->enabled,
            'confirmed' => $twoFactorAuth->confirmed_at !== null,
            'method' => $twoFactorAuth->method,
            'recovery_codes_count' => $twoFactorAuth->remaining_recovery_codes,
            'last_used' => $twoFactorAuth->last_used_at,
        ];
    }
}

