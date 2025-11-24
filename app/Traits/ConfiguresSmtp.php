<?php

namespace App\Traits;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Exception;

trait ConfiguresSmtp
{
    /**
     * Configure mail settings from database.
     * This ensures all emails use the correct SMTP settings.
     *
     * @return void
     */
    protected function configureMailFromDatabase()
    {
        try {
            // Get mail settings from database
            $settings = SiteSetting::getSettings();
            
            if (isset($settings['smtp_host']) && $settings['smtp_host']) {
                // Set default mailer to smtp
                Config::set('mail.default', 'smtp');
                
                // Configure SMTP settings
                Config::set('mail.mailers.smtp.host', $settings['smtp_host']);
                Config::set('mail.mailers.smtp.port', $settings['smtp_port'] ?? 587);
                Config::set('mail.mailers.smtp.username', $settings['smtp_username'] ?? '');
                Config::set('mail.mailers.smtp.password', $settings['smtp_password'] ?? '');
                
                // Handle encryption - 'none' means no encryption
                $encryption = $settings['smtp_encryption'] ?? 'tls';
                if ($encryption === 'none') {
                    Config::set('mail.mailers.smtp.encryption', null);
                } else {
                    Config::set('mail.mailers.smtp.encryption', $encryption);
                }
                
                // Set from email address
                if (isset($settings['from_email']) && $settings['from_email']) {
                    Config::set('mail.from.address', $settings['from_email']);
                    Config::set('mail.from.name', $settings['from_name'] ?? $settings['hospital_name'] ?? config('app.name'));
                }
                
                Log::info('Mail configuration updated from database', [
                    'host' => $settings['smtp_host'],
                    'port' => $settings['smtp_port'] ?? 587,
                    'encryption' => $encryption === 'none' ? null : $encryption,
                    'username' => $settings['smtp_username'] ?? '',
                    'from_email' => $settings['from_email'] ?? ''
                ]);
            } else {
                Log::warning('SMTP host not configured in database settings');
            }
        } catch (Exception $e) {
            Log::error('Failed to configure mail from database', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

