<?php

namespace App\Traits;

use App\Models\Setting;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Exception;

trait ConfiguresSmtp
{
    /**
     * Normalize/repair SMTP host for common local setups.
     * In many dev environments (esp. Windows without Docker DNS), "mailpit" won't resolve.
     */
    protected function normalizeSmtpHost(): void
    {
        try {
            $host = (string) Config::get('mail.mailers.smtp.host', '');
            if ($host === '') {
                return;
            }

            // If MAIL_HOST is "mailpit" but DNS doesn't resolve, fall back to localhost.
            if (strtolower($host) === 'mailpit') {
                $resolved = @gethostbyname($host);
                if ($resolved === $host) {
                    Config::set('mail.mailers.smtp.host', '127.0.0.1');
                    Log::warning('SMTP host "mailpit" not resolvable; falling back to 127.0.0.1');
                }
            }
        } catch (Exception $e) {
            // Never break sending due to normalization.
        }
    }

    /**
     * Configure mail settings from database.
     * This ensures all emails use the correct SMTP settings.
     *
     * @return void
     */
    protected function configureMailFromDatabase()
    {
        try {
            // Prefer SiteSetting (Admin > Communication > Email) then Setting (Admin > Settings > Email)
            $settings = SiteSetting::getSettings();
            $host = $settings['smtp_host'] ?? null;
            $port = $settings['smtp_port'] ?? null;
            $username = $settings['smtp_username'] ?? null;
            $password = $settings['smtp_password'] ?? null;
            $encryption = $settings['smtp_encryption'] ?? null;
            $fromEmail = $settings['from_email'] ?? null;
            $fromName = $settings['from_name'] ?? null;

            if (! $host) {
                $emailGroup = Setting::getGroup('email');
                if (! empty($emailGroup['mail_host'])) {
                    $host = $emailGroup['mail_host'];
                    $port = $emailGroup['mail_port'] ?? 587;
                    $username = $emailGroup['mail_username'] ?? '';
                    $password = $emailGroup['mail_password'] ?? '';
                    $encryption = $emailGroup['mail_encryption'] ?? 'tls';
                    $fromEmail = $emailGroup['mail_from_address'] ?? null;
                    $fromName = $emailGroup['mail_from_name'] ?? null;
                }
            }

            if ($host) {
                Config::set('mail.default', 'smtp');
                Config::set('mail.mailers.smtp.host', $host);
                Config::set('mail.mailers.smtp.port', $port ?? 587);
                Config::set('mail.mailers.smtp.username', $username ?? '');
                Config::set('mail.mailers.smtp.password', $password ?? '');

                if ($encryption === 'none' || $encryption === null || $encryption === '') {
                    Config::set('mail.mailers.smtp.encryption', null);
                } else {
                    Config::set('mail.mailers.smtp.encryption', $encryption ?? 'tls');
                }

                if ($fromEmail) {
                    Config::set('mail.from.address', $fromEmail);
                    Config::set('mail.from.name', $fromName ?? SiteSetting::get('hospital_name', config('app.name')));
                }

                Log::info('Mail configuration updated from database', [
                    'host' => $host,
                    'port' => $port ?? 587,
                    'from_email' => $fromEmail ?? '',
                ]);
            } else {
                Log::warning('SMTP host not configured in database settings (SiteSetting or Setting email group)');
            }

            $this->normalizeSmtpHost();
        } catch (Exception $e) {
            Log::error('Failed to configure mail from database', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
