<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormRequestsController extends Controller
{
    /**
     * Display a listing of form requests.
     */
    public function index(Request $request)
    {
        $query = FormRequest::with(['template', 'patient', 'requester'])
            ->requestedBy(Auth::user());

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by patient
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
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

        return view('admin.form-requests.index', compact('formRequests'));
    }

    /**
     * Display the specified form request.
     */
    public function show(FormRequest $formRequest)
    {
        $formRequest->load(['template', 'patient', 'requester', 'generatedDocument']);

        return view('admin.form-requests.show', compact('formRequest'));
    }

    /**
     * Resend the form request.
     */
    public function resend(FormRequest $formRequest)
    {
        // Only resend if not completed
        if ($formRequest->isCompleted()) {
            return back()->with('error', 'Cannot resend a completed form.');
        }

        // Generate new token and reset status
        $formRequest->update([
            'token' => \Illuminate\Support\Str::random(64),
            'status' => FormRequest::STATUS_PENDING,
            'expires_at' => now()->addDays(30),
            'sent_at' => now(),
            'opened_at' => null,
        ]);

        // Send email
        try {
            \Mail::send('emails.forms.form-request', [
                'formRequest' => $formRequest,
                'customMessage' => null,
            ], function ($mail) use ($formRequest) {
                $mail->to($formRequest->recipient_email)
                    ->subject('Please Complete: ' . ($formRequest->template->name ?? $formRequest->patientDocument->title ?? 'Form'));
            });

            return back()->with('success', 'Form request resent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to resend form: ' . $e->getMessage());
        }
    }

    /**
     * Delete the form request.
     */
    public function destroy(FormRequest $formRequest)
    {
        $formRequest->delete();

        return redirect()->route('admin.form-requests.index')
            ->with('success', 'Form request deleted successfully.');
    }
}
