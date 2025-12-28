<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\FormRequest;
use App\Models\SiteSetting;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class FormRequestsController extends Controller
{
    protected $emailService;

    public function __construct(EmailNotificationService $emailService)
    {
        $this->emailService = $emailService;
    }
    /**
     * Display a listing of form requests.
     */
    public function index(Request $request)
    {
        $query = FormRequest::with(['template', 'patient', 'requester'])
            ->where('requested_by', auth()->id());

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%");
                })
                ->orWhereHas('template', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            });
        }

        $formRequests = $query->latest()->paginate(20)->appends($request->query());

        return view('staff.form-requests.index', compact('formRequests'));
    }

    /**
     * Display the specified form request.
     */
    public function show(FormRequest $formRequest)
    {
        // Ensure the user can only view their own form requests
        if ($formRequest->requested_by !== auth()->id()) {
            abort(403, 'Access denied. You can only view form requests that you created.');
        }

        $formRequest->load(['template', 'patient', 'requester', 'generatedDocument']);

        return view('staff.form-requests.show', compact('formRequest'));
    }

    /**
     * Resend the form request.
     */
    public function resend(FormRequest $formRequest)
    {
        // Ensure the user can only resend their own form requests
        if ($formRequest->requested_by !== auth()->id()) {
            abort(403, 'Access denied. You can only resend form requests that you created.');
        }

        // Only resend if not completed
        if ($formRequest->isCompleted()) {
            return back()->with('error', 'Cannot resend a completed form.');
        }

        // Generate new token and reset status
        $formRequest->update([
            'token' => Str::random(64),
            'status' => FormRequest::STATUS_PENDING,
            'expires_at' => now()->addDays(30),
            'sent_at' => now(),
            'opened_at' => null,
        ]);

        // Reload relationships after update
        $formRequest->load(['template', 'patientDocument']);

        // Configure SMTP settings from database before sending
        $settings = SiteSetting::getSettings();
        if (isset($settings['smtp_host']) && $settings['smtp_host']) {
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $settings['smtp_host']);
            Config::set('mail.mailers.smtp.port', $settings['smtp_port'] ?? 587);
            Config::set('mail.mailers.smtp.username', $settings['smtp_username'] ?? '');
            Config::set('mail.mailers.smtp.password', $settings['smtp_password'] ?? '');
            $encryption = $settings['smtp_encryption'] ?? 'tls';
            Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);
            if (isset($settings['from_email']) && $settings['from_email']) {
                Config::set('mail.from.address', $settings['from_email']);
                Config::set('mail.from.name', $settings['from_name'] ?? $settings['hospital_name'] ?? config('app.name'));
            }
        }

        // Force synchronous sending
        $originalQueueConnection = config('queue.default');
        Config::set('queue.default', 'sync');

        // Send email
        try {
            $subject = 'Please Complete: ' . ($formRequest->template->name ?? $formRequest->patientDocument->title ?? 'Form');
            $emailBody = view('emails.forms.form-request', [
                'formRequest' => $formRequest,
                'customMessage' => null,
            ])->render();

            Mail::send('emails.forms.form-request', [
                'formRequest' => $formRequest,
                'customMessage' => null,
            ], function ($mail) use ($formRequest, $subject) {
                $mail->to($formRequest->recipient_email)
                    ->subject($subject);
            });

            // Log email to email logs
            $this->emailService->logRawEmail(
                $subject,
                $emailBody,
                $formRequest->recipient_email,
                $formRequest->patient->full_name ?? null,
                [
                    'email_type' => 'form_request',
                    'event' => 'form_request_resend',
                    'patient_id' => $formRequest->patient_id,
                ]
            );

            return back()->with('success', 'Form request resent successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to resend form request', [
                'form_request_id' => $formRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to resend form: ' . $e->getMessage());
        } finally {
            // Restore original queue connection
            Config::set('queue.default', $originalQueueConnection);
        }
    }
}
