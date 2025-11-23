<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'app_name',
                'value' => 'ThankDoc EHR',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Application name displayed across the platform',
                'is_public' => true
            ],
            [
                'key' => 'app_logo',
                'value' => '/assets/images/logo.png',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Main application logo path',
                'is_public' => true
            ],
            [
                'key' => 'app_favicon',
                'value' => '/assets/images/favicon.ico',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Application favicon path',
                'is_public' => true
            ],
            [
                'key' => 'app_timezone',
                'value' => 'UTC',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default application timezone',
                'is_public' => false
            ],
            [
                'key' => 'default_currency',
                'value' => 'USD',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default currency for the platform',
                'is_public' => true
            ],
            [
                'key' => 'currency_symbol',
                'value' => '$',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Currency symbol',
                'is_public' => true
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Enable/disable maintenance mode',
                'is_public' => true
            ],
            [
                'key' => 'registration_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Allow new user registration',
                'is_public' => true
            ],
            [
                'key' => 'kyc_required',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Require KYC verification for new users',
                'is_public' => true
            ],
            
            // Email Settings
            [
                'key' => 'mail_driver',
                'value' => 'smtp',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Email driver (smtp, sendmail, mailgun, etc.)',
                'is_public' => false
            ],
            [
                'key' => 'mail_host',
                'value' => 'smtp.mailtrap.io',
                'type' => 'string',
                'group' => 'email',
                'description' => 'SMTP host server',
                'is_public' => false
            ],
            [
                'key' => 'mail_port',
                'value' => '587',
                'type' => 'integer',
                'group' => 'email',
                'description' => 'SMTP port number',
                'is_public' => false
            ],
            [
                'key' => 'mail_username',
                'value' => '',
                'type' => 'string',
                'group' => 'email',
                'description' => 'SMTP username',
                'is_public' => false
            ],
            [
                'key' => 'mail_password',
                'value' => '',
                'type' => 'string',
                'group' => 'email',
                'description' => 'SMTP password',
                'is_public' => false
            ],
            [
                'key' => 'mail_encryption',
                'value' => 'tls',
                'type' => 'string',
                'group' => 'email',
                'description' => 'SMTP encryption (tls, ssl)',
                'is_public' => false
            ],
            [
                'key' => 'mail_from_address',
                'value' => 'noreply@newwaveshospital.com',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default from email address',
                'is_public' => false
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'ThankDoc EHR',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default from name',
                'is_public' => false
            ],
            [
                'key' => 'email_notifications',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Enable email notifications',
                'is_public' => false
            ],
            
            // SMS Settings
            [
                'key' => 'sms_driver',
                'value' => 'twilio',
                'type' => 'string',
                'group' => 'sms',
                'description' => 'SMS service provider',
                'is_public' => false
            ],
            [
                'key' => 'twilio_sid',
                'value' => '',
                'type' => 'string',
                'group' => 'sms',
                'description' => 'Twilio Account SID',
                'is_public' => false
            ],
            [
                'key' => 'twilio_token',
                'value' => '',
                'type' => 'string',
                'group' => 'sms',
                'description' => 'Twilio Auth Token',
                'is_public' => false
            ],
            [
                'key' => 'twilio_from',
                'value' => '',
                'type' => 'string',
                'group' => 'sms',
                'description' => 'Twilio from number',
                'is_public' => false
            ],
            [
                'key' => 'sms_notifications',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'sms',
                'description' => 'Enable SMS notifications',
                'is_public' => false
            ],
            
            // Security Settings
            [
                'key' => 'two_factor_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'security',
                'description' => 'Enable two-factor authentication',
                'is_public' => false
            ],
            [
                'key' => 'session_lifetime',
                'value' => '120',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Session lifetime in minutes',
                'is_public' => false
            ],
            [
                'key' => 'password_min_length',
                'value' => '8',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Minimum password length',
                'is_public' => true
            ],
            [
                'key' => 'login_attempts',
                'value' => '5',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Maximum login attempts before lockout',
                'is_public' => false
            ],
            [
                'key' => 'lockout_duration',
                'value' => '15',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Account lockout duration in minutes',
                'is_public' => false
            ],
            
            // Maintenance Settings
            [
                'key' => 'maintenance_message',
                'value' => 'We are currently performing scheduled maintenance. Please check back soon.',
                'type' => 'string',
                'group' => 'maintenance',
                'description' => 'Maintenance mode message',
                'is_public' => true
            ],
            [
                'key' => 'backup_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'backup',
                'description' => 'Enable automatic backups',
                'is_public' => false
            ],
            [
                'key' => 'backup_frequency',
                'value' => 'daily',
                'type' => 'string',
                'group' => 'backup',
                'description' => 'Backup frequency (daily, weekly, monthly)',
                'is_public' => false
            ],
            [
                'key' => 'backup_retention',
                'value' => '30',
                'type' => 'integer',
                'group' => 'backup',
                'description' => 'Number of days to retain backups',
                'is_public' => false
            ],
            
            // Financial Settings
            [
                'key' => 'min_transaction_amount',
                'value' => '1.00',
                'type' => 'float',
                'group' => 'financial',
                'description' => 'Minimum transaction amount',
                'is_public' => true
            ],
            [
                'key' => 'max_transaction_amount',
                'value' => '10000.00',
                'type' => 'float',
                'group' => 'financial',
                'description' => 'Maximum transaction amount',
                'is_public' => true
            ],
            [
                'key' => 'daily_transaction_limit',
                'value' => '50000.00',
                'type' => 'float',
                'group' => 'financial',
                'description' => 'Daily transaction limit per user',
                'is_public' => true
            ],
            [
                'key' => 'transaction_fee',
                'value' => '2.50',
                'type' => 'float',
                'group' => 'financial',
                'description' => 'Default transaction fee',
                'is_public' => true
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
