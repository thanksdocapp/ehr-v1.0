<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Models\ThemeSetting;
use App\Models\FrontendTemplate;
use App\Models\Department;
use App\Models\User;
use App\Services\HospitalEmailNotificationService;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index()
    {
        $data = [
            'site_settings' => SiteSetting::getSettings(),
            'theme_settings' => ThemeSetting::getActive(),
            'frontend_template' => FrontendTemplate::getActive(),
            'departments' => Department::active()->ordered()->get(),
        ];
        
        return view('contact', $data);
    }
    
    public function store(Request $request, HospitalEmailNotificationService $emailService)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'consent' => 'required|accepted'
        ]);
        
        // Store the message in the database
        $contactMessage = ContactMessage::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'consent' => $request->consent
        ]);
        
        // Send auto-reply email to the person who contacted us
        if (config('hospital.notifications.contact_auto_reply.enabled', true)) {
            try {
                $emailService->sendContactAutoReply($contactMessage);
            } catch (\Exception $e) {
                \Log::error('Failed to send contact auto-reply email', [
                    'contact_message_id' => $contactMessage->id,
                    'email' => $contactMessage->email,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Send notification to staff about new contact message
        if (config('hospital.staff_notifications.new_contact_message.enabled', true)) {
            try {
                // Get staff members who should be notified about contact messages
                $staffRoles = config('hospital.staff_notifications.new_contact_message.roles', ['admin', 'receptionist']);
                $staffMembers = User::whereIn('role', $staffRoles)
                    ->where('is_active', true)
                    ->whereNotNull('email')
                    ->get();
                    
                foreach ($staffMembers as $staff) {
                    $emailService->notifyStaffNewContactMessage($contactMessage, $staff);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send staff notification for new contact message', [
                    'contact_message_id' => $contactMessage->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Get success message from settings
        $site_settings = SiteSetting::getSettings();
        $success_message = $site_settings['contact_form_success_message'] ?? 'Thank you for your message! We will get back to you within 24 hours.';
        
        // For AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $success_message
            ]);
        }
        
        // For regular form submissions
        return redirect()->back()->with('success', $success_message);
    }
}
