<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class CommunicationController extends Controller
{
    /**
     * Display email configuration page
     */
    public function emailConfig()
    {
        // Get current email settings
        $settings = SiteSetting::whereIn('key', [
            'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
            'smtp_encryption', 'from_email', 'from_name'
        ])->pluck('value', 'key')->toArray();
        
        return view('admin.communication.email-config', compact('settings'));
    }

    /**
     * Update email configuration
     */
    public function updateEmailConfig(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer|min:1|max:65535',
            'smtp_username' => 'required|string|max:255',
            'smtp_password' => 'required|string|max:255',
            'smtp_encryption' => 'required|in:tls,ssl,none',
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
            'test_email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Handle different actions
            if ($request->action === 'test') {
                return $this->sendTestEmail($request);
            }

            // Save configuration
            SiteSetting::set('smtp_host', $request->smtp_host);
            SiteSetting::set('smtp_port', $request->smtp_port);
            SiteSetting::set('smtp_username', $request->smtp_username);
            SiteSetting::set('smtp_password', $request->smtp_password);
            SiteSetting::set('smtp_encryption', $request->smtp_encryption);
            SiteSetting::set('from_email', $request->from_email);
            SiteSetting::set('from_name', $request->from_name);

            return redirect()->route('admin.email-config')
                ->with('success', 'Email configuration saved successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to save email configuration. Please try again.')
                ->withInput();
        }
    }

    /**
     * Send test email
     */
    private function sendTestEmail(Request $request)
    {
        if (!$request->test_email) {
            return redirect()->back()
                ->with('error', 'Please enter a test email address.')
                ->withInput();
        }

        try {
            // Configure mail settings temporarily
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $request->smtp_host);
            Config::set('mail.mailers.smtp.port', $request->smtp_port);
            Config::set('mail.mailers.smtp.username', $request->smtp_username);
            Config::set('mail.mailers.smtp.password', $request->smtp_password);
            Config::set('mail.mailers.smtp.encryption', $request->smtp_encryption === 'none' ? null : $request->smtp_encryption);
            Config::set('mail.from.address', $request->from_email);
            Config::set('mail.from.name', $request->from_name);

            // Send test email
            Mail::raw('This is a test email from your hospital management system. If you received this, your email configuration is working correctly!', function ($message) use ($request) {
                $message->to($request->test_email)
                        ->subject('Test Email - Hospital Management System');
            });

            return redirect()->back()
                ->with('success', 'Test email sent successfully to ' . $request->test_email)
                ->withInput();

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send test email: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display SMS configuration page
     */
    public function smsConfig()
    {
        // Get current SMS settings
        $settings = SiteSetting::whereIn('key', [
            'sms_provider', 'sms_api_key', 'sms_api_secret',
            'sms_sender_id', 'sms_api_url'
        ])->pluck('value', 'key')->toArray();
        
        return view('admin.communication.sms-config', compact('settings'));
    }

    /**
     * Update SMS configuration
     */
    public function updateSmsConfig(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sms_provider' => 'required|in:twilio,nexmo,textlocal,custom',
            'sms_api_key' => 'required|string|max:255',
            'sms_api_secret' => 'nullable|string|max:255',
            'sms_sender_id' => 'required|string|max:50',
            'sms_api_url' => 'nullable|url|max:500',
            'test_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Handle different actions
            if ($request->action === 'test') {
                return $this->sendTestSms($request);
            }

            // Save configuration
            SiteSetting::set('sms_provider', $request->sms_provider);
            SiteSetting::set('sms_api_key', $request->sms_api_key);
            SiteSetting::set('sms_api_secret', $request->sms_api_secret);
            SiteSetting::set('sms_sender_id', $request->sms_sender_id);
            SiteSetting::set('sms_api_url', $request->sms_api_url);

            return redirect()->route('admin.sms-config')
                ->with('success', 'SMS configuration saved successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to save SMS configuration. Please try again.')
                ->withInput();
        }
    }

    /**
     * Send test SMS
     */
    private function sendTestSms(Request $request)
    {
        if (!$request->test_phone) {
            return redirect()->back()
                ->with('error', 'Please enter a test phone number.')
                ->withInput();
        }

        try {
            // This is a placeholder for SMS functionality
            // In a real implementation, you would integrate with SMS providers
            return redirect()->back()
                ->with('info', 'SMS testing functionality will be implemented based on your selected provider.')
                ->withInput();

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send test SMS: ' . $e->getMessage())
                ->withInput();
        }
    }
}
