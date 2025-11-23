<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\Transaction;
use App\Services\EmailTemplateService;
use Illuminate\Http\Request;

class EmailTemplateTestController extends Controller
{
    private $emailTemplateService;

    public function __construct(EmailTemplateService $emailTemplateService)
    {
        $this->emailTemplateService = $emailTemplateService;
    }

    /**
     * Display email template test page
     */
    public function index()
    {
        $templates = EmailTemplate::where('status', 'active')->orderBy('category')->orderBy('name')->get();
        $users = User::take(10)->get();
        $transactions = Transaction::with('user', 'recipientUser')->take(10)->get();

        return view('admin.email-templates.test', compact('templates', 'users', 'transactions'));
    }

    /**
     * Preview email template with real data
     */
    public function preview(Request $request)
    {
        $templateName = $request->get('template');
        $userId = $request->get('user_id');
        $transactionId = $request->get('transaction_id');

        $user = User::findOrFail($userId);
        $transaction = $transactionId ? Transaction::find($transactionId) : null;

        try {
            $processed = $this->emailTemplateService->processTemplate($templateName, $user, $transaction);
            
            return response()->json([
                'success' => true,
                'subject' => $processed['subject'],
                'body' => $processed['body'],
                'sender_name' => $processed['sender_name'],
                'sender_email' => $processed['sender_email']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Send test email
     */
    public function sendTest(Request $request)
    {
        $templateName = $request->get('template');
        $userId = $request->get('user_id');
        $transactionId = $request->get('transaction_id');
        $testEmail = $request->get('test_email');

        $user = User::findOrFail($userId);
        $transaction = $transactionId ? Transaction::find($transactionId) : null;

        try {
            $processed = $this->emailTemplateService->processTemplate($templateName, $user, $transaction);
            
            // Here you would normally send the email using Laravel's Mail facade
            // For this demo, we'll just return the processed template
            
            return response()->json([
                'success' => true,
                'message' => 'Test email would be sent to: ' . $testEmail,
                'processed_template' => $processed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show available template variables
     */
    public function variables(Request $request)
    {
        $templateName = $request->get('template');
        $template = EmailTemplate::where('name', $templateName)->first();

        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found'], 404);
        }

        return response()->json([
            'success' => true,
            'variables' => $template->variables,
            'category' => $template->category
        ]);
    }

    /**
     * Show template details for preview
     */
    public function showTemplate($templateId)
    {
        $template = EmailTemplate::find($templateId);

        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found'], 404);
        }

        return response()->json([
            'success' => true,
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'formatted_name' => $template->formatted_name ?? ucwords(str_replace('_', ' ', $template->name)),
                'subject' => $template->subject,
                'body' => $template->body,
                'category' => $template->category ?? 'general',
                'status' => $template->status,
                'variables' => $template->variables ?? [],
                'sender_name' => $template->sender_name,
                'sender_email' => $template->sender_email,
                'created_at' => $template->created_at->format('M d, Y h:i A'),
                'updated_at' => $template->updated_at->format('M d, Y h:i A')
            ]
        ]);
    }
}
