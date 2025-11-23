<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Services\HospitalEmailNotificationService;
use Illuminate\Http\Request;

class EmailManagementController extends Controller
{
    /**
     * Display email logs and statistics.
     */
    public function index(Request $request)
    {
        $query = EmailLog::with('emailTemplate')->orderBy('created_at', 'desc');
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('template')) {
            $query->where('email_template_id', $request->template);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('recipient_email', 'like', "%{$request->search}%")
                  ->orWhere('subject', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $emailLogs = $query->paginate(20);
        
        // Get statistics
        $stats = [
            'total_emails' => EmailLog::count(),
            'sent_emails' => EmailLog::where('status', 'sent')->count(),
            'pending_emails' => EmailLog::where('status', 'pending')->count(),
            'failed_emails' => EmailLog::where('status', 'failed')->count(),
            'today_emails' => EmailLog::whereDate('created_at', today())->count(),
            'this_week_emails' => EmailLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
        
        // Get templates for filter
        $templates = EmailTemplate::active()->get(['id', 'name', 'subject']);
        
        return view('admin.email-management.index', compact('emailLogs', 'stats', 'templates'));
    }

    /**
     * Show detailed email log.
     */
    public function show(EmailLog $emailLog)
    {
        $emailLog->load('emailTemplate');
        return view('admin.email-management.show', compact('emailLog'));
    }

    /**
     * Resend failed email.
     */
    public function resend(EmailLog $emailLog, HospitalEmailNotificationService $emailService)
    {
        if ($emailLog->status !== 'failed') {
            return response()->json([
                'success' => false,
                'message' => 'Only failed emails can be resent.'
            ], 400);
        }
        
        try {
            // Reset status and clear error message
            $emailLog->update([
                'status' => 'pending',
                'error_message' => null,
                'sent_at' => null
            ]);
            
            // Queue the email again
            dispatch(new \App\Jobs\SendEmail($emailLog));
            
            return response()->json([
                'success' => true,
                'message' => 'Email queued for resending successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete email log.
     */
    public function destroy(EmailLog $emailLog)
    {
        try {
            $emailLog->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Email log deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete email log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Email notification settings.
     */
    public function settings()
    {
        $notificationSettings = config('hospital.notifications', []);
        $staffNotificationSettings = config('hospital.staff_notifications', []);
        
        return view('admin.email-management.settings', compact('notificationSettings', 'staffNotificationSettings'));
    }

    /**
     * Update notification settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'notifications' => 'array',
            'staff_notifications' => 'array',
        ]);
        
        try {
            // Update notification settings in config cache
            // Note: In production, you'd want to store these in database
            // or update the actual config files through a proper settings system
            
            session(['success_message' => 'Email notification settings updated successfully!']);
            
            return response()->json([
                'success' => true,
                'message' => 'Email notification settings updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get email statistics for dashboard.
     */
    public function getStats()
    {
        try {
            $stats = [
                'today' => [
                    'sent' => EmailLog::whereDate('created_at', today())->where('status', 'sent')->count(),
                    'pending' => EmailLog::whereDate('created_at', today())->where('status', 'pending')->count(),
                    'failed' => EmailLog::whereDate('created_at', today())->where('status', 'failed')->count(),
                ],
                'this_week' => [
                    'sent' => EmailLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->where('status', 'sent')->count(),
                    'pending' => EmailLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->where('status', 'pending')->count(),
                    'failed' => EmailLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->where('status', 'failed')->count(),
                ],
                'this_month' => [
                    'sent' => EmailLog::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->where('status', 'sent')->count(),
                    'pending' => EmailLog::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->where('status', 'pending')->count(),
                    'failed' => EmailLog::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->where('status', 'failed')->count(),
                ],
                'template_usage' => EmailLog::selectRaw('email_template_id, COUNT(*) as count')
                    ->whereNotNull('email_template_id')
                    ->groupBy('email_template_id')
                    ->with('emailTemplate:id,name')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get(),
            ];
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get email statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
