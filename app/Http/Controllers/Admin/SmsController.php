<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function index()
    {
        // Get real SMS statistics from database
        $totalTemplates = \App\Models\SmsTemplate::count();
        $activeTemplates = \App\Models\SmsTemplate::where('is_active', true)->count();
        
        // Note: In a real implementation, you'd have SMS logs table
        // For now using placeholder values since SMS logs table doesn't exist yet
        $stats = [
            'templates' => $totalTemplates,
            'total_sent' => 0, // Would come from SMS logs table
            'pending' => 0,    // Would come from SMS queue table  
            'delivery_rate' => $totalTemplates > 0 ? round(($activeTemplates / $totalTemplates) * 100) : 0
        ];

        // Get recent SMS templates (since we don't have SMS logs table yet)
        // In a real app, this would be recent SMS logs
        $recentSms = collect(); // Empty collection until SMS logs table is created

        // Get SMS templates from database with pagination
        $templates = \App\Models\SmsTemplate::latest()->take(5)->get();

        return view('admin.sms.index', compact('stats', 'recentSms', 'templates'));
    }

    public function templates()
    {
        $templates = \App\Models\SmsTemplate::latest()->paginate(10);
        return view('admin.sms.templates', compact('templates'));
    }

    public function createTemplate()
    {
        return view('admin.sms.create');
    }

    public function storeTemplate(Request $request)
    {
        \App\Models\SmsTemplate::create($request->all());
        return back()->with('success', 'SMS template created successfully');
    }

    public function editTemplate($id)
    {
        $template = \App\Models\SmsTemplate::findOrFail($id);
        return view('admin.sms.edit', compact('template'));
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = \App\Models\SmsTemplate::findOrFail($id);
        $template->update($request->all());
        return back()->with('success', 'SMS template updated successfully');
    }

    public function destroyTemplate($id)
    {
        $template = \App\Models\SmsTemplate::findOrFail($id);
        $template->delete();
        return back()->with('success', 'SMS template deleted successfully');
    }

    public function sendSms()
    {
        $templates = \App\Models\SmsTemplate::where('is_active', true)->get();
        $users = \App\Models\User::select('id', 'first_name', 'last_name', 'phone', 'username')->whereNotNull('phone')->get();
        
        return view('admin.sms.send', compact('templates', 'users'));
    }

    public function sendSmsPost(Request $request)
    {
        $request->validate([
            'recipients' => 'required|array',
            'recipients.*' => 'string',
            'message' => 'required|string|max:160',
            'template_id' => 'nullable|exists:sms_templates,id'
        ]);
        
        foreach ($request->recipients as $phone) {
            \App\Models\SmsLog::create([
                'sms_template_id' => $request->template_id,
                'recipient_phone' => $phone,
                'message' => $request->message,
                'status' => 'pending',
                'sent_at' => now()
            ]);
        }
        
        return back()->with('success', 'SMS(s) queued for sending successfully');
    }

    public function logs()
    {
        $logs = \App\Models\SmsLog::with('template')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        $totalSent = \App\Models\SmsLog::where('status', 'sent')->count();
        $totalSms = \App\Models\SmsLog::count();
        
        $stats = [
            'total_sent' => $totalSent,
            'total_failed' => \App\Models\SmsLog::where('status', 'failed')->count(),
            'total_pending' => \App\Models\SmsLog::where('status', 'pending')->count(),
            'delivery_rate' => $totalSms > 0 ? round(($totalSent / $totalSms) * 100) : 0
        ];
        
        return view('admin.sms.logs', compact('logs', 'stats'));
    }
}

