<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index()
    {
        // Get email statistics
        $stats = [
            'total_templates' => \App\Models\EmailTemplate::count() ?? 0,
            'sent_emails' => 0, // Would track from email logs
            'pending_emails' => 0,
            'email_queue' => 0
        ];
        
        // Get paginated email templates
        $templates = \App\Models\EmailTemplate::latest()->paginate(10);
        
        return view('admin.emails.index', compact('stats', 'templates'));
    }

    public function templates()
    {
        $templates = \App\Models\EmailTemplate::latest()->paginate(10);
        return view('admin.emails.templates', compact('templates'));
    }

    public function createTemplate()
    {
        return view('admin.emails.create');
    }

    public function storeTemplate(Request $request)
    {
        \App\Models\EmailTemplate::create($request->all());
        return back()->with('success', 'Email template created successfully');
    }

    public function editTemplate($id)
    {
        $template = \App\Models\EmailTemplate::findOrFail($id);
        return view('admin.emails.edit', compact('template'));
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = \App\Models\EmailTemplate::findOrFail($id);
        $template->update($request->all());
        return back()->with('success', 'Email template updated successfully');
    }

    public function destroyTemplate($id)
    {
        $template = \App\Models\EmailTemplate::findOrFail($id);
        $template->delete();
        return back()->with('success', 'Email template deleted successfully');
    }

    public function sendEmail()
    {
        $templates = \App\Models\EmailTemplate::where('status', 'active')->get();
        $users = \App\Models\User::select('id', 'first_name', 'last_name', 'email', 'username')->get();
        
        return view('admin.emails.send', compact('templates', 'users'));
    }

    public function sendEmailPost(Request $request)
    {
        $request->validate([
            'recipients' => 'required|array',
            'recipients.*' => 'email',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'template_id' => 'nullable|exists:email_templates,id'
        ]);
        
        foreach ($request->recipients as $email) {
            \App\Models\EmailLog::create([
                'email_template_id' => $request->template_id,
                'recipient_email' => $email,
                'subject' => $request->subject,
                'body' => $request->content,
                'status' => 'pending',
                'sent_at' => now()
            ]);
        }
        
        return back()->with('success', 'Email(s) queued for sending successfully');
    }

    public function logs()
    {
        $logs = \App\Models\EmailLog::with('template')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        $stats = [
            'total_sent' => \App\Models\EmailLog::where('status', 'sent')->count(),
            'total_failed' => \App\Models\EmailLog::where('status', 'failed')->count(),
            'total_pending' => \App\Models\EmailLog::where('status', 'pending')->count(),
            'total_opened' => \App\Models\EmailLog::whereNotNull('opened_at')->count()
        ];
        
        return view('admin.emails.logs', compact('logs', 'stats'));
    }
    
    // Universal Template Management Methods
    public function universalTemplates()
    {
        $emailTemplates = \App\Models\EmailTemplate::all();
        $smsTemplates = \App\Models\SmsTemplate::all();
        
        return view('admin.templates.index', compact('emailTemplates', 'smsTemplates'));
    }
    
    public function createUniversalTemplate()
    {
        return view('admin.templates.create');
    }
    
    public function storeUniversalTemplate(Request $request)
    {
        $type = $request->input('type', 'email');
        
        if ($type === 'email') {
            \App\Models\EmailTemplate::create($request->all());
        } else {
            \App\Models\SmsTemplate::create($request->all());
        }
        
        return redirect()->route('admin.templates.index')->with('success', ucfirst($type) . ' template created successfully');
    }
    
    public function showUniversalTemplate($id)
    {
        $type = request('type', 'email');
        
        if ($type === 'email') {
            $template = \App\Models\EmailTemplate::findOrFail($id);
        } else {
            $template = \App\Models\SmsTemplate::findOrFail($id);
        }
        
        return view('admin.templates.show', compact('template', 'type'));
    }
    
    public function editUniversalTemplate($id)
    {
        $type = request('type', 'email');
        
        if ($type === 'email') {
            $template = \App\Models\EmailTemplate::findOrFail($id);
        } else {
            $template = \App\Models\SmsTemplate::findOrFail($id);
        }
        
        return view('admin.templates.edit', compact('template', 'type'));
    }
    
    public function updateUniversalTemplate(Request $request, $id)
    {
        $type = $request->input('type', 'email');
        
        if ($type === 'email') {
            $template = \App\Models\EmailTemplate::findOrFail($id);
        } else {
            $template = \App\Models\SmsTemplate::findOrFail($id);
        }
        
        $template->update($request->all());
        
        return redirect()->route('admin.templates.index')->with('success', ucfirst($type) . ' template updated successfully');
    }
    
    public function destroyUniversalTemplate($id)
    {
        $type = request('type', 'email');
        
        if ($type === 'email') {
            $template = \App\Models\EmailTemplate::findOrFail($id);
        } else {
            $template = \App\Models\SmsTemplate::findOrFail($id);
        }
        
        $template->delete();
        
        return back()->with('success', ucfirst($type) . ' template deleted successfully');
    }
}

