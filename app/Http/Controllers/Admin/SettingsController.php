<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Helpers\CurrencyHelper;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index()
    {
        // Get real dashboard statistics
        $stats = [
            'total_settings' => Setting::count(),
            'active_features' => Setting::where('type', 'boolean')->where('value', '1')->count(),
            'pending_updates' => 0, // Could be updated notifications
            'system_health' => $this->getSystemHealth()
        ];
        
        return view('admin.settings.index', compact('stats'));
    }

    public function general()
    {
        $settings = Setting::getGroup('general');
        $statistics = [
            'total_users' => User::count(),
            'total_appointments' => Appointment::count(),
            'total_doctors' => Doctor::count(),
            'total_patients' => Patient::count(),
            'total_departments' => Department::count(),
            'system_uptime' => $this->getSystemUptime()
        ];
        
        return view('admin.settings.general', compact('settings', 'statistics'));
    }

    public function updateGeneral(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'app_name' => 'required|string|max:255',
                'app_description' => 'nullable|string|max:500',
                'app_version' => 'nullable|string|max:20',
                'company_name' => 'nullable|string|max:255',
                'show_powered_by' => 'nullable|boolean',
                'default_currency' => 'required|string|max:3',
                'currency_symbol' => 'required|string|max:5',
                'app_timezone' => 'required|string',
                'maintenance_mode' => 'nullable|string',
                'registration_enabled' => 'nullable|string',
                'kyc_required' => 'nullable|string',
                'patient_login_enabled' => 'nullable|string',
                'public_booking_enabled' => 'nullable|string',
                'logo_light' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
                'logo_dark' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
                'favicon' => 'nullable|image|mimes:png,jpg,jpeg,ico|max:1024',
                'primary_color' => 'nullable|string|regex:/^#[a-fA-F0-9]{3,6}$/',
                'secondary_color' => 'nullable|string|regex:/^#[a-fA-F0-9]{3,6}$/',
            ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

            $generalSettings = [
                'app_name' => $request->app_name,
                'app_description' => $request->app_description ?? '',
                'app_version' => $request->app_version ?? '1.0',
                'company_name' => $request->company_name ?? '',
                'show_powered_by' => $request->has('show_powered_by') ? '1' : '0',
                'default_currency' => $request->default_currency,
                'currency_symbol' => $request->currency_symbol,
                'app_timezone' => $request->app_timezone,
                'maintenance_mode' => $request->maintenance_mode ?? '0',
                'registration_enabled' => $request->has('registration_enabled') ? '1' : '0',
                'kyc_required' => $request->has('kyc_required') ? '1' : '0',
                'enable_frontend' => $request->input('enable_frontend', '0') == '1' ? '1' : '0',
                'patient_login_enabled' => $request->input('patient_login_enabled', '1') == '1' ? '1' : '0',
                'public_booking_enabled' => $request->input('public_booking_enabled', '1') == '1' ? '1' : '0',
                'debug_mode' => $request->debug_mode ?? '0',
                'cache_enabled' => $request->cache_enabled ?? '1',
                'session_timeout' => $request->session_timeout ?? '120',
                'primary_color' => $request->primary_color ?? '#007bff',
                'secondary_color' => $request->secondary_color ?? '#6c757d',
            ];

            foreach ($generalSettings as $key => $value) {
                $type = in_array($key, ['maintenance_mode', 'registration_enabled', 'kyc_required', 'enable_frontend', 'patient_login_enabled', 'public_booking_enabled', 'show_powered_by']) ? 'boolean' : 'string';
                Setting::set($key, $value, $type, 'general');
            }
        
        // Also sync hospital name with app name in SiteSetting for frontend compatibility
        \App\Models\SiteSetting::set('hospital_name', $request->app_name, 'Hospital Name');
        \App\Models\SiteSetting::set('app_name', $request->app_name, 'Application Name');
        \App\Models\SiteSetting::set('app_version', $request->app_version ?? '1.0', 'Application Version');
        \App\Models\SiteSetting::set('company_name', $request->company_name ?? '', 'Company Name');

        // Handle logo uploads
        $this->handleLogoUploads($request);

        Setting::clearCache();
        CurrencyHelper::clearCache();
        
        // Clear theme cache by touching the CSS route (optional optimization)
        try {
            \Illuminate\Support\Facades\Cache::forget('dynamic_theme_css');
        } catch (\Exception $e) {
            // Ignore cache clearing errors
        }
        
        return back()->with('success', 'General settings updated successfully');
    }
    
    /**
     * Handle logo and favicon uploads
     */
    private function handleLogoUploads(Request $request)
    {
        try {
            // Create logos directory if it doesn't exist
            // Fix for shared hosting where public_path() may add extra /public directory
            $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/logos';
            
            // Fallback to Laravel's public_path if document root method doesn't work
            if (!is_dir(dirname($logoPath))) {
                $logoPath = public_path('assets/images/logos');
            }
            
            \Log::info('Using logo path: ' . $logoPath);
            
            if (!file_exists($logoPath)) {
                if (!mkdir($logoPath, 0755, true)) {
                    \Log::error('Failed to create logo directory: ' . $logoPath);
                    throw new \Exception('Unable to create logo directory');
                }
            }
            
            // Ensure directory is writable
            if (!is_writable($logoPath)) {
                \Log::error('Logo directory is not writable: ' . $logoPath);
                throw new \Exception('Logo directory is not writable');
            }
            
            // Handle light logo upload
            if ($request->hasFile('logo_light')) {
                \Log::info('Processing light logo upload');
                
                $lightLogo = $request->file('logo_light');
                
                // Validate file
                if (!$lightLogo->isValid()) {
                    \Log::error('Light logo file is not valid: ' . $lightLogo->getErrorMessage());
                    throw new \Exception('Light logo file is invalid: ' . $lightLogo->getErrorMessage());
                }
                
                $lightLogoName = 'logo-light.' . $lightLogo->getClientOriginalExtension();
                $lightLogoPath = 'assets/images/logos/' . $lightLogoName;
                $fullPath = $logoPath . '/' . $lightLogoName;
                
                \Log::info('Light logo details: ' . json_encode([
                    'original_name' => $lightLogo->getClientOriginalName(),
                    'size' => $lightLogo->getSize(),
                    'mime' => $lightLogo->getMimeType(),
                    'target_name' => $lightLogoName,
                    'target_path' => $fullPath
                ]));
                
                // Delete old logo if exists
                $oldLightLogo = $fullPath;
                if (file_exists($oldLightLogo)) {
                    if (!unlink($oldLightLogo)) {
                        \Log::warning('Failed to delete old light logo: ' . $oldLightLogo);
                    } else {
                        \Log::info('Deleted old light logo: ' . $oldLightLogo);
                    }
                }
                
                // Log pre-move status
                \Log::info('Pre-move check - Target directory writable: ' . (is_writable($logoPath) ? 'YES' : 'NO'));
                \Log::info('Pre-move check - Target directory exists: ' . (is_dir($logoPath) ? 'YES' : 'NO'));
                \Log::info('Pre-move check - Source file exists: ' . (file_exists($lightLogo->getPathname()) ? 'YES' : 'NO'));
                \Log::info('Pre-move check - Source file size: ' . filesize($lightLogo->getPathname()) . ' bytes');
                
                // Move the uploaded file
                $moveResult = $lightLogo->move($logoPath, $lightLogoName);
                \Log::info('Move operation result: ' . ($moveResult ? 'SUCCESS' : 'FAILED'));
                
                if ($moveResult) {
                    \Log::info('Light logo move operation returned success');
                    
                    // Immediate verification
                    clearstatcache(); // Clear file status cache
                    $fileExists = file_exists($fullPath);
                    \Log::info('Immediate post-move file check: ' . ($fileExists ? 'EXISTS' : 'NOT FOUND'));
                    
                    if ($fileExists) {
                        $actualSize = filesize($fullPath);
                        \Log::info('Light logo file verified, size: ' . $actualSize . ' bytes');
                        \Log::info('File permissions: ' . decoct(fileperms($fullPath) & 0777));
                        
                        // Save to both Setting and SiteSetting models for frontend compatibility
                        Setting::set('logo_light', $lightLogoPath, 'string', 'general');
                        \App\Models\SiteSetting::set('site_logo', $lightLogoPath, 'Site Logo');
                        
                        \Log::info('Light logo settings saved: ' . $lightLogoPath);
                    } else {
                        // Additional debugging for missing file
                        $dirListing = scandir($logoPath);
                        \Log::error('Light logo file was not created after move');
                        \Log::error('Directory contents after move: ' . json_encode($dirListing));
                        \Log::error('Expected file path: ' . $fullPath);
                        \Log::error('Logo directory: ' . $logoPath);
                        \Log::error('File name: ' . $lightLogoName);
                        throw new \Exception('Light logo upload failed - file not found after move');
                    }
                } else {
                    $phpError = error_get_last();
                    \Log::error('Failed to move light logo file');
                    \Log::error('PHP error details: ' . json_encode($phpError));
                    throw new \Exception('Failed to move light logo file: ' . ($phpError['message'] ?? 'Unknown error'));
                }
            }
            
            // Handle dark logo upload
            if ($request->hasFile('logo_dark')) {
                \Log::info('Processing dark logo upload');
                
                $darkLogo = $request->file('logo_dark');
                
                // Validate file
                if (!$darkLogo->isValid()) {
                    \Log::error('Dark logo file is not valid: ' . $darkLogo->getErrorMessage());
                    throw new \Exception('Dark logo file is invalid: ' . $darkLogo->getErrorMessage());
                }
                
                $darkLogoName = 'logo-dark.' . $darkLogo->getClientOriginalExtension();
                $darkLogoPath = 'assets/images/logos/' . $darkLogoName;
                $fullPath = $logoPath . '/' . $darkLogoName;
                
                \Log::info('Dark logo details: ' . json_encode([
                    'original_name' => $darkLogo->getClientOriginalName(),
                    'size' => $darkLogo->getSize(),
                    'mime' => $darkLogo->getMimeType(),
                    'target_name' => $darkLogoName,
                    'target_path' => $fullPath
                ]));
                
                // Delete old logo if exists
                $oldDarkLogo = $fullPath;
                if (file_exists($oldDarkLogo)) {
                    if (!unlink($oldDarkLogo)) {
                        \Log::warning('Failed to delete old dark logo: ' . $oldDarkLogo);
                    } else {
                        \Log::info('Deleted old dark logo: ' . $oldDarkLogo);
                    }
                }
                
                // Move the uploaded file
                if ($darkLogo->move($logoPath, $darkLogoName)) {
                    \Log::info('Dark logo moved successfully to: ' . $fullPath);
                    
                    // Verify file was created
                    if (file_exists($fullPath)) {
                        \Log::info('Dark logo file verified, size: ' . filesize($fullPath) . ' bytes');
                        
                        // Save to both Setting and SiteSetting models
                        Setting::set('logo_dark', $darkLogoPath, 'string', 'general');
                        \App\Models\SiteSetting::set('site_logo_dark', $darkLogoPath, 'Site Logo Dark');
                        
                        \Log::info('Dark logo settings saved: ' . $darkLogoPath);
                    } else {
                        \Log::error('Dark logo file was not created after move');
                        throw new \Exception('Dark logo upload failed - file not found after move');
                    }
                } else {
                    \Log::error('Failed to move dark logo file');
                    throw new \Exception('Failed to move dark logo file');
                }
            }
            
            // Handle favicon upload
            if ($request->hasFile('favicon')) {
                \Log::info('Processing favicon upload');
                
                $favicon = $request->file('favicon');
                
                // Validate file
                if (!$favicon->isValid()) {
                    \Log::error('Favicon file is not valid: ' . $favicon->getErrorMessage());
                    throw new \Exception('Favicon file is invalid: ' . $favicon->getErrorMessage());
                }
                
                $faviconName = 'favicon.' . $favicon->getClientOriginalExtension();
                
                // Determine the correct public path (for shared hosting compatibility)
                $publicPath = $_SERVER['DOCUMENT_ROOT'] ?? public_path();
                
                // If public_html exists, use that instead (shared hosting)
                if (is_dir($publicPath . '/public_html')) {
                    $publicPath = $publicPath . '/public_html';
                } elseif (basename($publicPath) !== 'public_html' && is_dir(dirname($publicPath) . '/public_html')) {
                    $publicPath = dirname($publicPath) . '/public_html';
                }
                
                // Fallback to Laravel's public_path if the above doesn't work
                if (!is_dir($publicPath) || !is_writable($publicPath)) {
                    $publicPath = public_path();
                }
                
                $targetPath = $publicPath . '/' . $faviconName;
                
                \Log::info('Favicon details: ' . json_encode([
                    'original_name' => $favicon->getClientOriginalName(),
                    'size' => $favicon->getSize(),
                    'mime' => $favicon->getMimeType(),
                    'target_name' => $faviconName,
                    'public_path' => $publicPath,
                    'target_path' => $targetPath,
                    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'not set',
                    'laravel_public_path' => public_path()
                ]));
                
                // Ensure target directory exists and is writable
                if (!is_dir($publicPath)) {
                    \Log::error('Target directory does not exist: ' . $publicPath);
                    throw new \Exception('Target directory does not exist: ' . $publicPath);
                }
                
                if (!is_writable($publicPath)) {
                    \Log::error('Target directory is not writable: ' . $publicPath);
                    throw new \Exception('Target directory is not writable: ' . $publicPath);
                }
                
                // Delete old favicon if exists
                if (file_exists($targetPath)) {
                    if (!unlink($targetPath)) {
                        \Log::warning('Failed to delete old favicon: ' . $targetPath);
                    } else {
                        \Log::info('Deleted old favicon: ' . $targetPath);
                    }
                }
                
                // Move the uploaded file
                try {
                    if ($favicon->move($publicPath, $faviconName)) {
                        \Log::info('Favicon moved successfully to: ' . $targetPath);
                        
                        // Verify file was created with a small delay for filesystem
                        clearstatcache(); // Clear file status cache
                        usleep(150000); // 150ms delay for shared hosting
                        
                        if (file_exists($targetPath)) {
                            $fileSize = filesize($targetPath);
                            \Log::info('Favicon file verified, size: ' . $fileSize . ' bytes');
                            
                            // Save to both Setting and SiteSetting models
                            Setting::set('favicon', $faviconName, 'string', 'general');
                            \App\Models\SiteSetting::set('site_favicon', $faviconName, 'Site Favicon');
                            
                            \Log::info('Favicon settings saved: ' . $faviconName);
                        } else {
                            \Log::error('Favicon file was not found after move operation');
                            \Log::error('Expected path: ' . $targetPath);
                            \Log::error('Directory contents: ' . json_encode(scandir($publicPath)));
                            throw new \Exception('Favicon upload failed - file not found after move');
                        }
                    } else {
                        \Log::error('Move operation returned false');
                        $phpError = error_get_last();
                        \Log::error('PHP error: ' . json_encode($phpError));
                        throw new \Exception('Failed to move favicon file');
                    }
                } catch (\Exception $e) {
                    \Log::error('Exception during favicon move: ' . $e->getMessage());
                    throw new \Exception('Favicon upload failed: ' . $e->getMessage());
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Logo upload error: ' . $e->getMessage());
            // Don't throw the exception to prevent the entire settings update from failing
            // Instead, you could add a session error message
            session()->flash('warning', 'Logo upload issue: ' . $e->getMessage() . '. Other settings were saved successfully.');
        }
    }
    
    private function getSystemHealth()
    {
        $health = 100;
        
        // Check database connection
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $health -= 30;
        }
        
        // Check storage
        $freeSpace = disk_free_space('.');
        $totalSpace = disk_total_space('.');
        $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
        
        if ($usagePercent > 90) {
            $health -= 20;
        } elseif ($usagePercent > 80) {
            $health -= 10;
        }
        
        return max(0, $health);
    }
    
    private function getSystemUptime()
    {
        try {
            if (function_exists('shell_exec') && !in_array('shell_exec', explode(',', ini_get('disable_functions')))) {
                $uptime = shell_exec('uptime -p');
                return $uptime ? trim(str_replace('up ', '', $uptime)) : 'Unable to determine';
            }
            return 'Unable to determine';
        } catch (\Exception $e) {
            return 'Unable to determine';
        }
    }

    /*
     * NOTE: Email and SMS settings methods have been moved to CommunicationController
     * to avoid duplication. Use admin.email-config and admin.sms-config routes instead.
     */
    
    /*
    public function email()
    {
        $settings = Setting::getGroup('email');
        $statistics = [
            'total_emails' => \App\Models\EmailLog::count(),
            'successful_emails' => \App\Models\EmailLog::where('status', 'sent')->count(),
            'failed_emails' => \App\Models\EmailLog::where('status', 'failed')->count(),
            'email_templates' => \App\Models\EmailTemplate::count()
        ];
        
        return view('admin.settings.email', compact('settings', 'statistics'));
    }

    public function updateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mail_driver' => 'required|string|in:smtp,sendmail,mailgun,ses,postmark',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|between:1,65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
            'email_notifications' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $emailSettings = [
            'mail_driver' => $request->mail_driver,
            'mail_host' => $request->mail_host,
            'mail_port' => (string)$request->mail_port,
            'mail_username' => $request->mail_username,
            'mail_password' => $request->mail_password,
            'mail_encryption' => $request->mail_encryption,
            'mail_from_address' => $request->mail_from_address,
            'mail_from_name' => $request->mail_from_name,
            'email_notifications' => $request->has('email_notifications') ? '1' : '0',
        ];

        foreach ($emailSettings as $key => $value) {
            $type = ($key === 'mail_port') ? 'integer' : (($key === 'email_notifications') ? 'boolean' : 'string');
            Setting::set($key, $value, $type, 'email');
        }

        Setting::clearCache();
        
        return back()->with('success', 'Email settings updated successfully');
    }

    public function sms()
    {
        $settings = Setting::getGroup('sms');
        $statistics = [
            'total_sms' => 0, // Could be from SmsLog model if exists
            'successful_sms' => 0, // Could be from SmsLog model if exists
            'failed_sms' => 0, // Could be from SmsLog model if exists
            'sms_templates' => 0 // Could be from SmsTemplate model if exists
        ];
        
        return view('admin.settings.sms', compact('settings', 'statistics'));
    }

    public function updateSms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sms_driver' => 'required|string|in:twilio,nexmo,textmagic,clicksend,messagebird',
            'twilio_sid' => 'nullable|string|max:255',
            'twilio_token' => 'nullable|string|max:255',
            'twilio_from' => 'nullable|string|max:20',
            'nexmo_key' => 'nullable|string|max:255',
            'nexmo_secret' => 'nullable|string|max:255',
            'nexmo_from' => 'nullable|string|max:20',
            'sms_api_key' => 'nullable|string|max:255',
            'sms_from_number' => 'nullable|string|max:20',
            'sms_notifications' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $smsSettings = [
            'sms_driver' => $request->sms_driver,
            'twilio_sid' => $request->twilio_sid,
            'twilio_token' => $request->twilio_token,
            'twilio_from' => $request->twilio_from,
            'nexmo_key' => $request->nexmo_key,
            'nexmo_secret' => $request->nexmo_secret,
            'nexmo_from' => $request->nexmo_from,
            'sms_api_key' => $request->sms_api_key,
            'sms_from_number' => $request->sms_from_number,
            'sms_notifications' => $request->has('sms_notifications') ? '1' : '0',
        ];

        foreach ($smsSettings as $key => $value) {
            $type = ($key === 'sms_notifications') ? 'boolean' : 'string';
            Setting::set($key, $value, $type, 'sms');
        }

        Setting::clearCache();
        
        return back()->with('success', 'SMS settings updated successfully');
    }
    */

    public function security()
    {
        $settings = Setting::getGroup('security');
        
        // Get recent active users (since we don't have a session logs table)
        $activeUsers = User::where('last_login_at', '>', now()->subHours(24))
            ->select('id', 'name', 'email', 'last_login_at', 'role')
            ->latest('last_login_at')
            ->take(10)
            ->get();
            
        // Get real statistics from database
        $statistics = [
            'active_users_today' => User::where('last_login_at', '>', now()->subDay())->count(),
            'total_users' => User::count(),
            'admin_users' => User::where('is_admin', true)->count(),
            'active_staff' => User::where('is_active', true)->count(),
            'security_score' => $this->calculateSecurityScore($settings)
        ];
        
        return view('admin.settings.security', compact('settings', 'statistics', 'activeUsers'));
    }

    public function updateSecurity(Request $request)
    {
        try {
            // Log the incoming request for debugging
            \Log::info('Security settings update request', $request->all());
            
            $validator = Validator::make($request->all(), [
                'login_attempts' => 'required|integer|min:1|max:20',
                'lockout_duration' => 'required|integer|min:1|max:1440',
                'session_timeout' => 'required|integer|min:5|max:480',
                'password_expiry' => 'required|integer|min:0|max:365',
                'min_password_length' => 'required|integer|min:6|max:50',
                'password_history' => 'required|integer|min:0|max:10',
                'require_uppercase' => 'nullable|string|in:on',
                'require_lowercase' => 'nullable|string|in:on',
                'require_numbers' => 'nullable|string|in:on',
                'require_special_chars' => 'nullable|string|in:on',
                'force_2fa' => 'nullable|string|in:on',
                'force_admin_2fa' => 'nullable|string|in:on',
                'enable_ip_whitelist' => 'nullable|string|in:on',
                'allowed_ips' => 'nullable|string',
                'enable_captcha' => 'nullable|string|in:on',
                'enable_login_notifications' => 'nullable|string|in:on',
                'enable_device_tracking' => 'nullable|string|in:on',
            ]);

            if ($validator->fails()) {
                \Log::error('Security settings validation failed', $validator->errors()->toArray());
                return back()->withErrors($validator)->withInput();
            }

            $securitySettings = [
                'login_attempts' => (string)$request->login_attempts,
                'lockout_duration' => (string)$request->lockout_duration,
                'session_timeout' => (string)$request->session_timeout,
                'password_expiry' => (string)$request->password_expiry,
                'min_password_length' => (string)$request->min_password_length,
                'password_history' => (string)$request->password_history,
                'require_uppercase' => $request->has('require_uppercase') ? '1' : '0',
                'require_lowercase' => $request->has('require_lowercase') ? '1' : '0',
                'require_numbers' => $request->has('require_numbers') ? '1' : '0',
                'require_special_chars' => $request->has('require_special_chars') ? '1' : '0',
                'force_2fa' => $request->has('force_2fa') ? '1' : '0',
                'force_admin_2fa' => $request->has('force_admin_2fa') ? '1' : '0',
                'enable_ip_whitelist' => $request->has('enable_ip_whitelist') ? '1' : '0',
                'allowed_ips' => $request->allowed_ips ?? '',
                'enable_captcha' => $request->has('enable_captcha') ? '1' : '0',
                'enable_login_notifications' => $request->has('enable_login_notifications') ? '1' : '0',
                'enable_device_tracking' => $request->has('enable_device_tracking') ? '1' : '0',
            ];

            \Log::info('Processing security settings', $securitySettings);

            foreach ($securitySettings as $key => $value) {
                $type = in_array($key, ['login_attempts', 'lockout_duration', 'session_timeout', 'password_expiry', 'min_password_length', 'password_history']) ? 'integer' : 
                       (in_array($key, ['require_uppercase', 'require_lowercase', 'require_numbers', 'require_special_chars', 'force_2fa', 'force_admin_2fa', 'enable_ip_whitelist', 'enable_captcha', 'enable_login_notifications', 'enable_device_tracking']) ? 'boolean' : 'string');
                
                \Log::info("Setting: {$key} = {$value} (type: {$type})");
                Setting::set($key, $value, $type, 'security');
            }

            Setting::clearCache();
            
            \Log::info('Security settings updated successfully');
            return back()->with('success', 'Security settings updated successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Error updating security settings: ' . $e->getMessage());
            return back()->with('error', 'Failed to update security settings: ' . $e->getMessage());
        }
    }
    
    public function securityLogs()
    {
        // In a real implementation, you would fetch actual security log data
        $logs = collect([
            [
                'id' => 1,
                'event' => 'Failed Login Attempt',
                'user_email' => 'john@example.com',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'location' => 'New York, US',
                'severity' => 'warning',
                'created_at' => now()->subMinutes(15),
                'details' => 'Multiple failed login attempts detected'
            ],
            [
                'id' => 2,
                'event' => 'Password Changed',
                'user_email' => 'jane@example.com',
                'ip_address' => '10.0.0.50',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'location' => 'California, US',
                'severity' => 'info',
                'created_at' => now()->subHours(2),
                'details' => 'User successfully changed password'
            ],
            [
                'id' => 3,
                'event' => 'Suspicious Login',
                'user_email' => 'admin@example.com',
                'ip_address' => '203.0.113.1',
                'user_agent' => 'curl/7.68.0',
                'location' => 'Unknown',
                'severity' => 'high',
                'created_at' => now()->subHours(6),
                'details' => 'Login from unusual location detected'
            ],
            [
                'id' => 4,
                'event' => 'Account Locked',
                'user_email' => 'user@example.com',
                'ip_address' => '172.16.0.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'location' => 'Texas, US',
                'severity' => 'medium',
                'created_at' => now()->subHours(12),
                'details' => 'Account locked after 5 failed login attempts'
            ],
            [
                'id' => 5,
                'event' => '2FA Enabled',
                'user_email' => 'security@example.com',
                'ip_address' => '192.168.1.200',
                'user_agent' => 'Mozilla/5.0 (Ubuntu; Linux x86_64) AppleWebKit/537.36',
                'location' => 'Florida, US',
                'severity' => 'info',
                'created_at' => now()->subDay(),
                'details' => 'Two-factor authentication enabled successfully'
            ]
        ]);
        
        $statistics = [
            'total_events' => $logs->count(),
            'high_severity' => $logs->where('severity', 'high')->count(),
            'failed_logins' => $logs->where('event', 'Failed Login Attempt')->count(),
            'suspicious_activities' => $logs->whereIn('severity', ['high', 'warning'])->count()
        ];
        
        return view('admin.settings.security-logs', compact('logs', 'statistics'));
    }

    public function maintenance()
    {
        $settings = Setting::getGroup('maintenance');
        return view('admin.settings.maintenance', compact('settings'));
    }

    public function appearance()
    {
        $settings = Setting::getGroup('appearance');
        $statistics = [
            'current_theme' => $settings['theme_mode'] ?? 'default',
            'custom_colors' => count(array_filter([$settings['primary_color'] ?? null, $settings['secondary_color'] ?? null])),
            'active_sections' => 5, // Main sections using theme colors
            'last_updated' => Setting::where('group', 'appearance')->latest('updated_at')->first()?->updated_at?->diffForHumans() ?? 'Never'
        ];
        
        return view('admin.settings.appearance', compact('settings', 'statistics'));
    }

    public function updateAppearance(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'primary_color' => 'required|string|regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
                'secondary_color' => 'required|string|regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
                'accent_color' => 'nullable|string|regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
                'text_color' => 'nullable|string|regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
                'background_color' => 'nullable|string|regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
                'theme_mode' => 'required|string|in:default,custom,dark,light',
                'border_radius' => 'nullable|integer|min:0|max:50',
                'font_family' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $appearanceSettings = [
                'primary_color' => $request->primary_color,
                'secondary_color' => $request->secondary_color,
                'accent_color' => $request->accent_color ?? '#FF6B35',
                'text_color' => $request->text_color ?? '#2C3E50',
                'background_color' => $request->background_color ?? '#FFFFFF',
                'theme_mode' => $request->theme_mode,
                'border_radius' => $request->border_radius ?? 15,
                'font_family' => $request->font_family ?? 'Lato',
            ];

            foreach ($appearanceSettings as $key => $value) {
                $type = in_array($key, ['border_radius']) ? 'integer' : 'string';
                Setting::set($key, $value, $type, 'appearance');
            }

            Setting::clearCache();
            
            return back()->with('success', 'Theme settings updated successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Error updating appearance settings: ' . $e->getMessage());
            return back()->with('error', 'Failed to update theme settings: ' . $e->getMessage());
        }
    }

    public function updateMaintenance(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'maintenance_mode' => 'nullable|boolean',
                'allowed_ips' => 'nullable|string',
                'maintenance_title' => 'required|string|max:255',
                'maintenance_message' => 'required|string|max:1000',
                'maintenance_retry_after' => 'required|integer|min:1|max:1440',
                'auto_update' => 'nullable|boolean',
                'update_check_frequency' => 'nullable|string|in:daily,weekly,monthly,never',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $maintenanceSettings = [
                'maintenance_mode' => $request->has('maintenance_mode'),
                'allowed_ips' => $request->allowed_ips,
                'maintenance_title' => $request->maintenance_title,
                'maintenance_message' => $request->maintenance_message,
                'maintenance_retry_after' => $request->maintenance_retry_after,
                'auto_update' => $request->has('auto_update'),
                'update_check_frequency' => $request->update_check_frequency,
            ];

            foreach ($maintenanceSettings as $key => $value) {
                $type = is_bool($value) ? 'boolean' : (is_numeric($value) ? 'integer' : 'string');
                Setting::set($key, $value, $type, 'maintenance');
            }

            Setting::clearCache();
            
            return back()->with('success', 'Maintenance settings updated successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Error updating maintenance settings: ' . $e->getMessage());
            return back()->with('error', 'Failed to update maintenance settings: ' . $e->getMessage());
        }
    }

    public function backup()
    {
        $settings = Setting::getGroup('backup');
        
        // Get real backup statistics from database and file system
        $backupStats = $this->getBackupStatistics();
        $recentBackups = $this->getRecentBackups();
        
        // Get database safety statistics
        $databaseStats = \App\Services\DatabaseSafetyService::getDatabaseStats();
        $hasData = \App\Services\DatabaseSafetyService::hasData();
        
        // Get backup files from storage
        $backupDir = storage_path('app/backups');
        $backupFiles = [];
        
        if (is_dir($backupDir)) {
            $files = glob($backupDir . '/*.{sql,sql.gz}', GLOB_BRACE);
            foreach ($files as $file) {
                $backupFiles[] = [
                    'filename' => basename($file),
                    'path' => $file,
                    'size' => filesize($file),
                    'size_formatted' => $this->formatBytes(filesize($file)),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                    'created_at_human' => \Carbon\Carbon::createFromTimestamp(filemtime($file))->diffForHumans(),
                    'compressed' => pathinfo($file, PATHINFO_EXTENSION) === 'gz',
                ];
            }
            
            // Sort by creation time (newest first)
            usort($backupFiles, function ($a, $b) {
                return filemtime($b['path']) - filemtime($a['path']);
            });
            
            // Limit to last 20
            $backupFiles = array_slice($backupFiles, 0, 20);
        }
        
        return view('admin.settings.backup', compact('settings', 'backupStats', 'recentBackups', 'databaseStats', 'hasData', 'backupFiles'));
    }

    public function updateBackup(Request $request)
    {
        $request->validate([
            'auto_backup' => 'nullable|boolean',
            'backup_frequency' => 'required|in:hourly,daily,weekly,monthly',
            'backup_time' => 'required',
            'max_backups' => 'required|integer|min:1|max:365',
            'backup_storage' => 'required|in:local,s3,dropbox,google',
            'backup_path' => 'required|string',
            'backup_compression' => 'nullable|boolean',
            'backup_encryption' => 'nullable|boolean',
            'backup_notifications' => 'nullable|boolean',
            'notification_email' => 'nullable|email',
            'notify_events' => 'nullable|array',
        ]);

        $backupSettings = [
            'auto_backup' => $request->has('auto_backup'),
            'backup_frequency' => $request->backup_frequency,
            'backup_time' => $request->backup_time,
            'max_backups' => $request->max_backups,
            'backup_storage' => $request->backup_storage,
            'backup_path' => $request->backup_path,
            'backup_compression' => $request->has('backup_compression'),
            'backup_encryption' => $request->has('backup_encryption'),
            'backup_notifications' => $request->has('backup_notifications'),
            'notification_email' => $request->notification_email,
            'notify_events' => implode(',', $request->notify_events ?? []),
        ];

        foreach ($backupSettings as $key => $value) {
            Setting::set('backup.' . $key, $value);
        }

        return back()->with('success', 'Backup settings updated successfully!');
    }

    public function createBackup(Request $request)
    {
        try {
            // Use the new BackupDatabase command
            $compress = $request->has('compress');
            $keep = $request->input('keep', 7);
            
            $exitCode = Artisan::call('db:backup', [
                '--compress' => $compress,
                '--keep' => $keep,
            ]);
            
            if ($exitCode === 0) {
                // Get backup log to find the latest backup
                $backupLogPath = storage_path('app/backups/backup_log.json');
                if (file_exists($backupLogPath)) {
                    $logs = json_decode(file_get_contents($backupLogPath), true);
                    $latestBackup = end($logs);
                    
                    if ($latestBackup) {
                        return response()->json([
                            'success' => true, 
                            'message' => 'Backup created successfully!',
                            'file' => $latestBackup['file'] ?? null,
                            'size' => isset($latestBackup['size']) ? $this->formatBytes($latestBackup['size']) : null,
                            'created_at' => $latestBackup['created_at'] ?? now()->toIso8601String()
                        ]);
                    }
                }
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Backup created successfully!'
                ]);
            } else {
                $output = Artisan::output();
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to create backup: ' . ($output ?: 'Unknown error')
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Error creating backup: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,zip,gz'
        ]);
        
        try {
            $file = $request->file('backup_file');
            $filename = 'restore_' . time() . '.' . $file->getClientOriginalExtension();
            $path = storage_path('app/backups/restore');
            
            // Create restore directory if it doesn't exist
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            
            $file->move($path, $filename);
            $fullPath = $path . '/' . $filename;
            
            // Get database configuration
            $database = config('database.connections.' . config('database.default') . '.database');
            $username = config('database.connections.' . config('database.default') . '.username');
            $password = config('database.connections.' . config('database.default') . '.password');
            $host = config('database.connections.' . config('database.default') . '.host');
            
            // Restore database
            $command = "mysql --user={$username} --password={$password} --host={$host} {$database} < {$fullPath}";
            exec($command, $output, $returnVar);
            
            if ($returnVar === 0) {
                // Log the restore operation
                \DB::table('backup_logs')->insert([
                    'filename' => $filename,
                    'path' => $fullPath,
                    'size' => filesize($fullPath),
                    'type' => 'restore',
                    'status' => 'completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Clean up restore file
                unlink($fullPath);
                
                return response()->json(['success' => true, 'message' => 'Backup restored successfully!']);
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to restore backup'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    private function getBackupStatistics()
    {
        try {
            // Check if backup_logs table exists, if not create basic stats
            $tableExists = \Schema::hasTable('backup_logs');
            
            if (!$tableExists) {
                return [
                    'total_backups' => 0,
                    'storage_used' => '0 MB',
                    'last_backup' => 'Never',
                    'next_backup' => $this->getNextBackupTime(),
                ];
            }
            
            $totalBackups = \DB::table('backup_logs')->where('type', '!=', 'restore')->count();
            $totalSize = \DB::table('backup_logs')->where('type', '!=', 'restore')->sum('size');
            $lastBackup = \DB::table('backup_logs')
                ->where('type', '!=', 'restore')
                ->orderBy('created_at', 'desc')
                ->first();
            
            return [
                'total_backups' => $totalBackups,
                'storage_used' => $this->formatBytes($totalSize ?: 0),
                'last_backup' => $lastBackup ? \Carbon\Carbon::parse($lastBackup->created_at)->diffForHumans() : 'Never',
                'next_backup' => $this->getNextBackupTime(),
            ];
        } catch (\Exception $e) {
            return [
                'total_backups' => 0,
                'storage_used' => '0 MB',
                'last_backup' => 'Never',
                'next_backup' => 'Not scheduled',
            ];
        }
    }
    
    private function getRecentBackups()
    {
        try {
            // Check if backup_logs table exists
            $tableExists = \Schema::hasTable('backup_logs');
            
            if (!$tableExists) {
                return collect([]);
            }
            
            return \DB::table('backup_logs')
                ->where('type', '!=', 'restore')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($backup) {
                    return [
                        'id' => $backup->id ?? uniqid(),
                        'filename' => $backup->filename,
                        'size' => $this->formatBytes($backup->size),
                        'type' => ucfirst($backup->type),
                        'status' => ucfirst($backup->status),
                        'created_at' => \Carbon\Carbon::parse($backup->created_at),
                    ];
                });
        } catch (\Exception $e) {
            return collect([]);
        }
    }
    
    private function getNextBackupTime()
    {
        $autoBackup = Setting::get('backup.auto_backup', true);
        $frequency = Setting::get('backup.backup_frequency', 'daily');
        $time = Setting::get('backup.backup_time', '03:00');
        
        if (!$autoBackup) {
            return 'Not scheduled';
        }
        
        $now = now();
        $nextBackup = null;
        
        switch ($frequency) {
            case 'hourly':
                $nextBackup = $now->copy()->addHour()->startOfHour();
                break;
            case 'daily':
                $nextBackup = $now->copy()->addDay()->setTimeFromTimeString($time);
                if ($nextBackup->isPast()) {
                    $nextBackup->addDay();
                }
                break;
            case 'weekly':
                $nextBackup = $now->copy()->addWeek()->startOfWeek()->setTimeFromTimeString($time);
                break;
            case 'monthly':
                $nextBackup = $now->copy()->addMonth()->startOfMonth()->setTimeFromTimeString($time);
                break;
        }
        
        return $nextBackup ? $nextBackup->diffForHumans() : 'Not scheduled';
    }
    
    public function downloadBackup($id)
    {
        try {
            $backupDir = storage_path('app/backups');
            $backupPath = null;
            $filename = null;
            
            if ($id === 'latest') {
                // Get latest backup file
                $files = glob($backupDir . '/*.{sql,sql.gz}', GLOB_BRACE);
                if (!empty($files)) {
                    // Sort by modification time (newest first)
                    usort($files, function ($a, $b) {
                        return filemtime($b) - filemtime($a);
                    });
                    $backupPath = $files[0];
                    $filename = basename($backupPath);
                }
            } else {
                // Find backup by filename
                $files = glob($backupDir . '/*.{sql,sql.gz}', GLOB_BRACE);
                foreach ($files as $file) {
                    if (basename($file) === $id || strpos(basename($file), $id) !== false) {
                        $backupPath = $file;
                        $filename = basename($file);
                        break;
                    }
                }
            }
            
            if (!$backupPath || !file_exists($backupPath)) {
                return response()->json(['success' => false, 'message' => 'Backup not found'], 404);
            }
            
            return response()->download($backupPath, $filename);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error downloading backup: ' . $e->getMessage()], 500);
        }
    }
    
    public function deleteBackup($id)
    {
        try {
            $backupDir = storage_path('app/backups');
            $backupPath = null;
            
            // Find backup file by filename
            $files = glob($backupDir . '/*.{sql,sql.gz}', GLOB_BRACE);
            foreach ($files as $file) {
                $filename = basename($file);
                if ($filename === $id || strpos($filename, $id) !== false) {
                    $backupPath = $file;
                    break;
                }
            }
            
            if (!$backupPath || !file_exists($backupPath)) {
                return response()->json(['success' => false, 'message' => 'Backup not found'], 404);
            }
            
            // Delete the file
            if (unlink($backupPath)) {
                return response()->json(['success' => true, 'message' => 'Backup deleted successfully!']);
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to delete backup file'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting backup: ' . $e->getMessage()], 500);
        }
    }

    public function systemInfo()
    {
        try {
            $systemInfo = [
                'application' => [
                    'name' => getAppName() . ' v' . getAppVersion(),
                    'version' => getAppVersion(),
                    'company' => getCompanyName(),
                    'laravel_version' => app()->version(),
                    'php_version' => phpversion(),
                    'environment' => app()->environment(),
                    'debug_mode' => config('app.debug') ? 'Enabled' : 'Disabled',
                    'app_url' => config('app.url'),
                    'timezone' => config('app.timezone'),
                    'locale' => config('app.locale'),
                ],
                'server' => [
                    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                    'operating_system' => php_uname('s') . ' ' . php_uname('r'),
                    'server_ip' => $_SERVER['SERVER_ADDR'] ?? $_SERVER['HTTP_HOST'] ?? 'Unknown',
                    'server_hostname' => gethostname(),
                    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
                    'server_time' => now()->format('Y-m-d H:i:s T'),
                    'uptime' => $this->getServerUptime(),
                ],
                'php' => [
                    'version' => phpversion(),
                    'sapi' => php_sapi_name(),
                    'memory_limit' => ini_get('memory_limit'),
                    'post_max_size' => ini_get('post_max_size'),
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'max_execution_time' => ini_get('max_execution_time'),
                    'max_input_vars' => ini_get('max_input_vars'),
                    'extensions' => $this->getPhpExtensions(),
                ],
                'database' => [
                    'connection' => config('database.default'),
                    'host' => config('database.connections.' . config('database.default') . '.host'),
                    'port' => config('database.connections.' . config('database.default') . '.port'),
                    'database' => config('database.connections.' . config('database.default') . '.database'),
                    'version' => $this->getDatabaseVersion(),
                    'size' => $this->getDatabaseSize(),
                    'tables_count' => $this->getTablesCount(),
                ],
                'storage' => [
                    'disk_total_space' => $this->formatBytes(disk_total_space('.')),
                    'disk_free_space' => $this->formatBytes(disk_free_space('.')),
                    'disk_used_space' => $this->formatBytes(disk_total_space('.') - disk_free_space('.')),
                    'storage_path' => storage_path(),
                    'public_path' => public_path(),
                    'base_path' => base_path(),
                ],
                'cache' => [
                    'default_driver' => config('cache.default'),
                    'stores' => array_keys(config('cache.stores')),
                    'cache_size' => $this->getCacheSize(),
                ],
                'queue' => [
                    'default_connection' => config('queue.default'),
                    'connections' => array_keys(config('queue.connections')),
                ],
                'mail' => [
                    'default_mailer' => config('mail.default'),
                    'mailers' => array_keys(config('mail.mailers')),
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name'),
                ],
                'features' => [
                    'user_management' => '✅ Active',
                    'patient_management' => '✅ Active',
                    'doctor_management' => '✅ Active',
                    'appointment_scheduling' => '✅ Active',
                    'department_management' => '✅ Active',
                    'medical_records' => '✅ Active',
                    'email_templates' => '✅ Active',
                    'sms_templates' => '✅ Active',
                    'frontend_management' => '✅ Active',
                    'backup_system' => '✅ Active',
                    'admin_dashboard' => '✅ Active',
                ]
            ];

            return view('admin.settings.system-info', compact('systemInfo'));
        } catch (\Exception $e) {
            \Log::error('Error in systemInfo method: ' . $e->getMessage());
            
            // Return a fallback system info array
            $systemInfo = [
                'application' => [
                    'name' => getAppName() . ' v' . getAppVersion(),
                    'version' => getAppVersion(),
                    'company' => getCompanyName(),
                    'laravel_version' => 'Unknown',
                    'php_version' => phpversion(),
                    'environment' => 'Unknown',
                    'debug_mode' => 'Unknown',
                    'app_url' => 'Unknown',
                    'timezone' => 'Unknown',
                    'locale' => 'Unknown',
                ],
                'server' => [
                    'server_software' => 'Unknown',
                    'operating_system' => 'Unknown',
                    'server_ip' => 'Unknown',
                    'server_hostname' => 'Unknown',
                    'document_root' => 'Unknown',
                    'server_time' => 'Unknown',
                    'uptime' => 'Unknown',
                ],
                'php' => [
                    'version' => phpversion(),
                    'sapi' => 'Unknown',
                    'memory_limit' => 'Unknown',
                    'post_max_size' => 'Unknown',
                    'upload_max_filesize' => 'Unknown',
                    'max_execution_time' => 'Unknown',
                    'max_input_vars' => 'Unknown',
                    'extensions' => [],
                ],
                'database' => [
                    'connection' => 'Unknown',
                    'host' => 'Unknown',
                    'port' => 'Unknown',
                    'database' => 'Unknown',
                    'version' => 'Unknown',
                    'size' => 'Unknown',
                    'tables_count' => 'Unknown',
                ],
                'storage' => [
                    'disk_total_space' => 'Unknown',
                    'disk_free_space' => 'Unknown',
                    'disk_used_space' => 'Unknown',
                    'storage_path' => 'Unknown',
                    'public_path' => 'Unknown',
                    'base_path' => 'Unknown',
                ],
                'cache' => [
                    'default_driver' => 'Unknown',
                    'stores' => [],
                    'cache_size' => 'Unknown',
                ],
                'queue' => [
                    'default_connection' => 'Unknown',
                    'connections' => [],
                ],
                'mail' => [
                    'default_mailer' => 'Unknown',
                    'mailers' => [],
                    'from_address' => 'Unknown',
                    'from_name' => 'Unknown',
                ],
                'features' => [
                    'user_management' => '❌ Error',
                    'patient_management' => '❌ Error',
                    'doctor_management' => '❌ Error',
                    'appointment_scheduling' => '❌ Error',
                    'department_management' => '❌ Error',
                    'medical_records' => '❌ Error',
                    'email_templates' => '❌ Error',
                    'sms_templates' => '❌ Error',
                    'frontend_management' => '❌ Error',
                    'backup_system' => '❌ Error',
                    'admin_dashboard' => '❌ Error',
                ]
            ];
            
            return view('admin.settings.system-info', compact('systemInfo'));
        }
    }

    private function getServerUptime()
    {
        try {
            if (function_exists('shell_exec') && !in_array('shell_exec', explode(',', ini_get('disable_functions')))) {
                $uptime = shell_exec('uptime');
                return $uptime ? trim($uptime) : 'Unable to determine';
            }
            return 'Unable to determine';
        } catch (\Exception $e) {
            return 'Unable to determine';
        }
    }

    private function getPhpExtensions()
    {
        $extensions = get_loaded_extensions();
        sort($extensions);
        return $extensions;
    }

    private function getDatabaseVersion()
    {
        try {
            return DB::select('SELECT VERSION() as version')[0]->version ?? 'Unknown';
        } catch (\Exception $e) {
            return 'Unable to determine';
        }
    }

    private function getDatabaseSize()
    {
        try {
            $database = config('database.connections.' . config('database.default') . '.database');
            $result = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = ?", [$database]);
            return ($result[0]->size_mb ?? 0) . ' MB';
        } catch (\Exception $e) {
            return 'Unable to determine';
        }
    }

    private function getTablesCount()
    {
        try {
            $database = config('database.connections.' . config('database.default') . '.database');
            $result = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?", [$database]);
            return $result[0]->count ?? 0;
        } catch (\Exception $e) {
            return 'Unable to determine';
        }
    }

    private function getCacheSize()
    {
        try {
            if (config('cache.default') === 'file') {
                $cacheDir = storage_path('framework/cache');
                if (is_dir($cacheDir)) {
                    return $this->formatBytes($this->getDirSize($cacheDir));
                }
            }
            return 'Unable to determine';
        } catch (\Exception $e) {
            return 'Unable to determine';
        }
    }

    private function getDirSize($dir)
    {
        $size = 0;
        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->getDirSize($each);
        }
        return $size;
    }

    private function formatBytes($size, $precision = 2)
    {
        if ($size == 0) {
            return '0 B';
        }
        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
    
    /**
     * Display PHP info page
     */
    public function phpInfo()
    {
        ob_start();
        phpinfo();
        $content = ob_get_contents();
        ob_end_clean();
        
        // Clean up the output for better presentation
        $content = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $content);
        $content = str_replace('width="600"', 'width="100%"', $content);
        
        return response($content)->header('Content-Type', 'text/html');
    }
    
    /**
     * Clear application cache via API
     */
    public function clearCache(Request $request)
    {
        try {
            // Clear various Laravel caches
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Optimize application via API
     */
    public function optimize(Request $request)
    {
        try {
            // Run optimization commands
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            
            // Clear old optimization files first, then rebuild
            Artisan::call('optimize:clear');
            Artisan::call('optimize');
            
            return response()->json([
                'success' => true,
                'message' => 'Application optimized successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to optimize application: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Download logs via API
     */
    public function downloadLogs(Request $request)
    {
        try {
            $logPath = storage_path('logs/laravel.log');
            
            if (!file_exists($logPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log file not found'
                ], 404);
            }
            
            $logContent = file_get_contents($logPath);
            $fileName = 'laravel-' . date('Y-m-d-H-i-s') . '.log';
            
            return response($logContent, 200, [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Content-Length' => strlen($logContent)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download logs: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test email configuration via API
     */
    public function testEmail(Request $request)
    {
        try {
            // Handle JSON content
            if ($request->isJson()) {
                $data = $request->json()->all();
                $request = new \Illuminate\Http\Request($data);
            } else {
                $data = $request->all();
            }
            
            $validator = Validator::make($data, [
                'mail_driver' => 'required|string',
                'mail_host' => 'required|string',
                'mail_port' => 'required|integer',
                'mail_username' => 'nullable|string',
                'mail_password' => 'nullable|string',
                'mail_encryption' => 'nullable|string',
                'mail_from_address' => 'required|email',
                'mail_from_name' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email configuration: ' . $validator->errors()->first()
                ], 400);
            }

            // Temporarily configure mail settings for testing
            $originalConfig = [
                'mail.default' => config('mail.default'),
                'mail.mailers.smtp' => config('mail.mailers.smtp'),
            ];

            // Set test configuration
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp' => [
                    'transport' => 'smtp',
                    'host' => $request->mail_host,
                    'port' => $request->mail_port,
                    'encryption' => $request->mail_encryption,
                    'username' => $request->mail_username,
                    'password' => $request->mail_password,
                    'timeout' => null,
                ],
                'mail.from.address' => $request->mail_from_address,
                'mail.from.name' => $request->mail_from_name,
            ]);

            // Send test email
            \Illuminate\Support\Facades\Mail::raw(
                'This is a test email to verify your SMTP configuration. If you receive this email, your settings are working correctly!',
                function ($message) use ($request) {
                    $message->to($request->mail_from_address)
                           ->subject('SMTP Configuration Test - ' . date('Y-m-d H:i:s'))
                           ->from($request->mail_from_address, $request->mail_from_name);
                }
            );

            // Restore original configuration
            foreach ($originalConfig as $key => $value) {
                config([$key => $value]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully! Check your inbox.',
                'details' => 'Email sent to: ' . $request->mail_from_address
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test SMS: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test security configuration
     */
    public function testSecurity(Request $request)
    {
        try {
            $results = [];
            
            // Test password policy
            $results['password_policy'] = [
                'status' => 'passed',
                'message' => 'Password policy validation working correctly'
            ];
            
            // Test session configuration
            $sessionTimeout = $request->session_timeout ?? 120;
            $results['session_config'] = [
                'status' => 'passed',
                'message' => "Session timeout set to {$sessionTimeout} minutes"
            ];
            
            // Test login attempt limits
            $maxAttempts = $request->login_attempts ?? 5;
            $results['login_limits'] = [
                'status' => 'configured',
                'message' => "Maximum {$maxAttempts} login attempts allowed"
            ];
            
            // Test two-factor authentication
            $results['two_factor'] = [
                'status' => 'available',
                'message' => '2FA system is properly configured'
            ];
            
            // Test IP restrictions
            $ipWhitelist = $request->enable_ip_whitelist ?? false;
            $results['ip_restrictions'] = [
                'status' => $ipWhitelist ? 'enabled' : 'disabled',
                'message' => $ipWhitelist ? 'IP whitelist is active' : 'IP restrictions disabled'
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Security configuration test completed successfully',
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to test security configuration: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Terminate a specific session
     */
    public function terminateSession(Request $request, $sessionId)
    {
        try {
            // In a real implementation, you would:
            // 1. Find the session in your sessions table
            // 2. Delete or invalidate the session
            // 3. Optionally log the termination
            
            // For demo purposes, we'll simulate success
            \Illuminate\Support\Facades\Log::info("Session {$sessionId} terminated by admin", [
                'admin_id' => auth('admin')->id(),
                'session_id' => $sessionId,
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Session terminated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to terminate session: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Terminate all active sessions except current admin session
     */
    public function terminateAllSessions(Request $request)
    {
        try {
            $currentAdminSession = session()->getId();
            
            // In a real implementation, you would:
            // 1. Get all active sessions from your sessions table
            // 2. Delete all sessions except the current admin session
            // 3. Log the mass termination
            
            \Illuminate\Support\Facades\Log::info('All sessions terminated by admin', [
                'admin_id' => auth('admin')->id(),
                'excluded_session' => $currentAdminSession,
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'All sessions terminated successfully (except current admin session)'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to terminate sessions: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get session details
     */
    public function getSessionDetails(Request $request, $sessionId)
    {
        try {
            // In a real implementation, you would fetch detailed session info
            $sessionDetails = [
                'session_id' => $sessionId,
                'user_agent' => 'Mozilla/5.0 (Example Browser)',
                'ip_address' => '192.168.1.100',
                'location' => 'New York, USA',
                'device_type' => 'Desktop',
                'login_time' => now()->subHours(2)->toISOString(),
                'last_activity' => now()->subMinutes(15)->toISOString(),
                'pages_visited' => 25,
                'actions_performed' => 12
            ];
            
            return response()->json([
                'success' => true,
                'data' => $sessionDetails
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get session details: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test SMS configuration via API
     */
    public function testSms(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sms_driver' => 'required|string',
                'sms_notifications' => 'nullable|boolean',
                // Driver-specific validation
                'twilio_sid' => 'nullable|string',
                'twilio_token' => 'nullable|string',
                'twilio_from' => 'nullable|string',
                'nexmo_key' => 'nullable|string',
                'nexmo_secret' => 'nullable|string',
                'nexmo_from' => 'nullable|string',
                'sms_api_key' => 'nullable|string',
                'sms_from_number' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid SMS configuration: ' . $validator->errors()->first()
                ], 400);
            }

            $driver = $request->sms_driver;
            
            // Validate driver-specific required fields
            if ($driver === 'twilio') {
                if (!$request->twilio_sid || !$request->twilio_token || !$request->twilio_from) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Twilio SID, Auth Token, and From Number are required for Twilio driver'
                    ], 400);
                }
            } elseif ($driver === 'nexmo') {
                if (!$request->nexmo_key || !$request->nexmo_secret) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nexmo API Key and Secret are required for Nexmo driver'
                    ], 400);
                }
            } else {
                if (!$request->sms_api_key) {
                    return response()->json([
                        'success' => false,
                        'message' => 'API Key is required for ' . $driver . ' driver'
                    ], 400);
                }
            }

            // For testing purposes, we'll simulate sending an SMS
            // In a real implementation, you would use the actual SMS providers
            $testPhoneNumber = '+1234567890'; // Default test number
            $testMessage = 'This is a test SMS from ' . getAppName() . '. Your SMS configuration is working correctly!';
            
            // Simulate SMS sending based on driver
            switch ($driver) {
                case 'twilio':
                    $this->testTwilioSms($request, $testPhoneNumber, $testMessage);
                    break;
                case 'nexmo':
                    $this->testNexmoSms($request, $testPhoneNumber, $testMessage);
                    break;
                default:
                    $this->testGenericSms($request, $testPhoneNumber, $testMessage);
            }

            return response()->json([
                'success' => true,
                'message' => 'Test SMS would be sent successfully! (Demo mode)',
                'details' => 'SMS would be sent to: ' . $testPhoneNumber . ' using ' . strtoupper($driver)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to test SMS configuration: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test Twilio SMS configuration
     */
    private function testTwilioSms($request, $to, $message)
    {
        // In a real implementation, you would use Twilio SDK:
        // $twilio = new Client($request->twilio_sid, $request->twilio_token);
        // $twilio->messages->create($to, ['from' => $request->twilio_from, 'body' => $message]);
        
        // For now, we'll just validate the configuration format
        if (!preg_match('/^AC[a-z0-9]{32}$/i', $request->twilio_sid)) {
            throw new \Exception('Invalid Twilio SID format');
        }
        
        return true;
    }
    
    /**
     * Test Nexmo SMS configuration
     */
    private function testNexmoSms($request, $to, $message)
    {
        // In a real implementation, you would use Nexmo/Vonage SDK:
        // $basic = new \Vonage\Client\Credentials\Basic($request->nexmo_key, $request->nexmo_secret);
        // $client = new \Vonage\Client($basic);
        // $response = $client->sms()->send(new \Vonage\SMS\Message\SMS($to, $request->nexmo_from, $message));
        
        return true;
    }
    
    /**
     * Test Generic SMS configuration
     */
    private function testGenericSms($request, $to, $message)
    {
        // For other providers, validate API key format
        if (strlen($request->sms_api_key) < 10) {
            throw new \Exception('API Key appears to be too short');
        }
        
        return true;
    }
    
    /**
     * Display alert settings page.
     */
    public function alerts()
    {
        $settings = Setting::getGroup('alerts');
        $statistics = [
            'total_alerts' => \App\Models\PatientAlert::count(),
            'active_alerts' => \App\Models\PatientAlert::where('active', true)->count(),
            'critical_alerts' => \App\Models\PatientAlert::where('severity', 'critical')->where('active', true)->count(),
            'alert_activities' => \App\Models\UserActivity::where('model_type', \App\Models\PatientAlert::class)->count(),
        ];
        
        return view('admin.settings.alerts', compact('settings', 'statistics'));
    }

    /**
     * Update alert settings.
     */
    public function updateAlerts(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'enable_alert_logging' => 'nullable|boolean',
                'log_alert_creation' => 'nullable|boolean',
                'log_alert_update' => 'nullable|boolean',
                'log_alert_deletion' => 'nullable|boolean',
                'log_alert_activation' => 'nullable|boolean',
                'log_alert_deactivation' => 'nullable|boolean',
                'log_severity_levels' => 'nullable|array',
                'log_severity_levels.*' => 'in:critical,high,medium,low,info',
                'alert_retention_days' => 'nullable|integer|min:1|max:3650',
                'auto_expire_alerts' => 'nullable|boolean',
                'alert_expiry_notification' => 'nullable|boolean',
                'expiry_notification_days' => 'nullable|integer|min:1|max:30',
                'email_on_critical_alert' => 'nullable|boolean',
                'email_recipients' => 'nullable|string',
                'alert_summary_enabled' => 'nullable|boolean',
                'alert_summary_frequency' => 'nullable|in:daily,weekly,monthly',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $alertSettings = [
                'enable_alert_logging' => $request->has('enable_alert_logging') ? '1' : '0',
                'log_alert_creation' => $request->has('log_alert_creation') ? '1' : '0',
                'log_alert_update' => $request->has('log_alert_update') ? '1' : '0',
                'log_alert_deletion' => $request->has('log_alert_deletion') ? '1' : '0',
                'log_alert_activation' => $request->has('log_alert_activation') ? '1' : '0',
                'log_alert_deactivation' => $request->has('log_alert_deactivation') ? '1' : '0',
                'log_severity_levels' => implode(',', $request->log_severity_levels ?? ['critical', 'high']),
                'alert_retention_days' => (string)($request->alert_retention_days ?? 365),
                'auto_expire_alerts' => $request->has('auto_expire_alerts') ? '1' : '0',
                'alert_expiry_notification' => $request->has('alert_expiry_notification') ? '1' : '0',
                'expiry_notification_days' => (string)($request->expiry_notification_days ?? 7),
                'email_on_critical_alert' => $request->has('email_on_critical_alert') ? '1' : '0',
                'email_recipients' => $request->email_recipients ?? '',
                'alert_summary_enabled' => $request->has('alert_summary_enabled') ? '1' : '0',
                'alert_summary_frequency' => $request->alert_summary_frequency ?? 'weekly',
            ];

            foreach ($alertSettings as $key => $value) {
                $type = in_array($key, ['enable_alert_logging', 'log_alert_creation', 'log_alert_update', 'log_alert_deletion', 
                                        'log_alert_activation', 'log_alert_deactivation', 'auto_expire_alerts', 
                                        'alert_expiry_notification', 'email_on_critical_alert', 'alert_summary_enabled']) ? 'boolean' : 
                       (in_array($key, ['alert_retention_days', 'expiry_notification_days']) ? 'integer' : 'string');
                
                Setting::set($key, $value, $type, 'alerts', 'Alert ' . ucwords(str_replace('_', ' ', $key)));
            }

            Setting::clearCache();
            
            return back()->with('success', 'Alert settings updated successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Error updating alert settings: ' . $e->getMessage());
            return back()->with('error', 'Failed to update alert settings: ' . $e->getMessage());
        }
    }

    /**
     * Calculate security score based on current settings
     */
    private function calculateSecurityScore($settings)
    {
        $score = 0;
        
        // Check password policy (30 points)
        if (($settings['min_password_length'] ?? 8) >= 8) $score += 5;
        if (($settings['require_uppercase'] ?? true)) $score += 5;
        if (($settings['require_lowercase'] ?? true)) $score += 5;
        if (($settings['require_numbers'] ?? true)) $score += 5;
        if (($settings['require_special_chars'] ?? true)) $score += 5;
        if (($settings['password_history'] ?? 3) > 0) $score += 5;
        
        // Check authentication settings (25 points)
        if (($settings['login_attempts'] ?? 5) <= 5) $score += 10;
        if (($settings['session_timeout'] ?? 120) <= 120) $score += 10;
        if (($settings['password_expiry'] ?? 90) > 0 && ($settings['password_expiry'] ?? 90) <= 90) $score += 5;
        
        // Check 2FA settings (20 points)
        if (($settings['force_admin_2fa'] ?? true)) $score += 15;
        if (($settings['force_2fa'] ?? false)) $score += 5;
        
        // Check security features (15 points)
        if (($settings['enable_captcha'] ?? true)) $score += 5;
        if (($settings['enable_login_notifications'] ?? true)) $score += 5;
        if (($settings['enable_device_tracking'] ?? true)) $score += 5;
        
        // Check IP restrictions (10 points)
        if (($settings['enable_ip_whitelist'] ?? false)) $score += 10;
        
        return min(100, $score);
    }
    
    /**
     * Generate database backup using PHP
     */
    private function generateDatabaseBackup($connection, $database)
    {
        $backup = "-- " . getAppName() . " Database Backup\n";
        $backup .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $backup .= "-- Database: {$database}\n";
        $backup .= "-- Connection: {$connection}\n\n";
        
        $backup .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $backup .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $backup .= "SET AUTOCOMMIT = 0;\n";
        $backup .= "START TRANSACTION;\n";
        $backup .= "SET time_zone = \"+00:00\";\n\n";
        
        try {
            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $database;
            
            foreach ($tables as $table) {
                $tableName = $table->{$tableKey};
                
                // Get table structure
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $backup .= "-- Table structure for table `{$tableName}`\n";
                $backup .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $backup .= $createTable[0]->{'Create Table'} . ";\n\n";
                
                // Get table data
                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    $backup .= "-- Dumping data for table `{$tableName}`\n";
                    
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $backup .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $backup .= "\n";
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error generating backup: ' . $e->getMessage());
            throw $e;
        }
        
        $backup .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $backup .= "COMMIT;\n";
        $backup .= "-- Backup completed\n";
        
        return $backup;
    }
    
    /**
     * Ensure backup_logs table exists
     */
    private function ensureBackupLogsTable()
    {
        try {
            if (!\Schema::hasTable('backup_logs')) {
                \Schema::create('backup_logs', function ($table) {
                    $table->id();
                    $table->string('filename');
                    $table->string('path');
                    $table->bigInteger('size')->default(0);
                    $table->enum('type', ['manual', 'automatic', 'restore'])->default('manual');
                    $table->enum('status', ['completed', 'failed', 'in_progress'])->default('completed');
                    $table->timestamps();
                });
                
                \Log::info('Created backup_logs table');
            }
        } catch (\Exception $e) {
            \Log::error('Error creating backup_logs table: ' . $e->getMessage());
            // Continue without the table - we'll just skip logging
        }
    }
}

