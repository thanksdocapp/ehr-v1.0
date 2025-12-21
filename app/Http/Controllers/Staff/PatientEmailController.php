<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use App\Mail\PatientEmail;
use App\Models\EmailLog;
use App\Traits\ConfiguresSmtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class PatientEmailController extends Controller
{
    use ConfiguresSmtp;
    
    /**
     * Display a listing of emails sent by the current doctor.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        
        if (!$doctor) {
            return redirect()->route('staff.dashboard')
                ->with('error', 'Doctor profile not found.');
        }

        // Get emails sent by this doctor (filter by doctor_id in metadata)
        // Use whereRaw for JSON path query that works across MySQL versions
        $query = EmailLog::where('email_type', 'patient_communication')
            ->whereRaw('JSON_EXTRACT(metadata, "$.doctor_id") = ?', [$doctor->id])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('recipient_email', 'like', "%{$request->search}%")
                  ->orWhere('subject', 'like', "%{$request->search}%")
                  ->orWhere('recipient_name', 'like', "%{$request->search}%");
            });
        }

        $emailLogs = $query->with('patient')->paginate(15)->appends($request->query());

        // Get statistics
        $stats = [
            'total_emails' => EmailLog::where('email_type', 'patient_communication')
                ->whereRaw('JSON_EXTRACT(metadata, "$.doctor_id") = ?', [$doctor->id])->count(),
            'sent_emails' => EmailLog::where('email_type', 'patient_communication')
                ->whereRaw('JSON_EXTRACT(metadata, "$.doctor_id") = ?', [$doctor->id])
                ->where('status', 'sent')->count(),
            'failed_emails' => EmailLog::where('email_type', 'patient_communication')
                ->whereRaw('JSON_EXTRACT(metadata, "$.doctor_id") = ?', [$doctor->id])
                ->where('status', 'failed')->count(),
        ];

        // Get patients for filter
        $patients = Patient::active()
            ->visibleTo($user)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('staff.patient-email.index', compact('emailLogs', 'stats', 'patients', 'doctor'));
    }

    /**
     * Display the specified email.
     */
    public function show($id)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        
        if (!$doctor) {
            return redirect()->route('staff.dashboard')
                ->with('error', 'Doctor profile not found.');
        }

        $emailLog = EmailLog::with('patient')
            ->where('email_type', 'patient_communication')
            ->whereRaw('JSON_EXTRACT(metadata, "$.doctor_id") = ?', [$doctor->id])
            ->findOrFail($id);

        return view('staff.patient-email.show', compact('emailLog', 'doctor'));
    }

    /**
     * Render the exact HTML that was sent to the patient (for iframe preview).
     * IMPORTANT: this is staff-authenticated and must NOT count as an "open".
     */
    public function preview($id)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            abort(403);
        }

        $emailLog = EmailLog::with('patient')
            ->where('email_type', 'patient_communication')
            ->whereRaw('JSON_EXTRACT(metadata, "$.doctor_id") = ?', [$doctor->id])
            ->findOrFail($id);

        $metadata = $emailLog->metadata ?? [];
        $html = null;

        // Prefer stored preview HTML (frozen at send-time, without tracking pixel)
        if (!empty($metadata['rendered_html_preview']) && is_string($metadata['rendered_html_preview'])) {
            $html = $metadata['rendered_html_preview'];
        } elseif (!empty($metadata['rendered_html']) && is_string($metadata['rendered_html'])) {
            // Backward compat: if only sent HTML exists, use it (we will strip pixel below).
            $html = $metadata['rendered_html'];
        } else {
            // Fallback: re-render using current template + stored payload,
            // then freeze it so future views are consistent.
            $departmentName = $metadata['department_name'] ?? null;
            $departmentLogo = $metadata['department_logo'] ?? null;
            $departmentId = $metadata['department_id'] ?? null;
            if ((!$departmentName || !$departmentLogo) && $departmentId) {
                $dept = Department::find($departmentId);
                if ($dept) {
                    $departmentName = $departmentName ?: $dept->name;
                    $departmentLogo = $departmentLogo ?: ($dept->logo_url ?? null);
                }
            }

            $clinicName = $metadata['clinic_name']
                ?? \App\Models\SiteSetting::where('key', 'hospital_name')->value('value')
                ?? config('app.name', 'Clinic');

            $emailData = [
                'subject' => $emailLog->subject,
                'body' => $emailLog->body,
                'doctor_name' => $metadata['doctor_name'] ?? ($doctor->name ?? $user->name),
                'doctor_specialization' => $metadata['doctor_specialization'] ?? ($doctor->specialization ?? 'General Practitioner'),
                'clinic_name' => $clinicName,
                'department_name' => $departmentName,
                'department_logo' => $departmentLogo,
                'date_sent' => $metadata['date_sent'] ?? ($emailLog->sent_at ? $emailLog->sent_at->format('F j, Y') : $emailLog->created_at->format('F j, Y')),
            ];

            // Do not include tracking pixel in staff preview
            $html = (string) view('emails.patient-email', [
                'emailData' => $emailData,
                'trackingToken' => null,
                'emailLogId' => null,
            ])->render();

            // Freeze preview HTML for emails that were sent before we stored it.
            try {
                $emailLog->update([
                    'metadata' => array_merge($metadata, [
                        'rendered_html_preview' => $html,
                    ]),
                ]);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Safety: ensure staff preview never triggers open tracking even if the stored HTML contains a pixel.
        $html = preg_replace('/<img\\b[^>]*\\bsrc\\s*=\\s*([\"\\\'])[^\"\\\']*\\/track\\/email\\/open\\/[^\"\\\']*\\1[^>]*>/i', '', (string) $html) ?? (string) $html;

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    /**
     * Show the email composer form.
     */
    public function compose(Request $request)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        
        if (!$doctor) {
            return redirect()->route('staff.dashboard')
                ->with('error', 'Doctor profile not found.');
        }

        // Get patients visible to this doctor
        $patients = Patient::active()
            ->visibleTo($user)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Pre-select patient if provided
        $selectedPatientId = $request->get('patient_id');

        return view('staff.patient-email.compose', compact('patients', 'doctor', 'selectedPatientId'));
    }

    /**
     * Send email to patient.
     */
    public function send(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        try {
            $user = Auth::user();
            $doctor = Doctor::where('user_id', $user->id)->first();
            
            if (!$doctor) {
                return back()->withErrors(['error' => 'Doctor profile not found.'])->withInput();
            }

            $patient = Patient::findOrFail($request->patient_id);

            if (!$patient->email) {
                return back()->withErrors(['patient_id' => 'Patient does not have an email address.'])->withInput();
            }

            // Get primary department
            $department = $doctor->primaryDepartment();
            
            // Get clinic name (hospital name from settings)
            $clinicName = \App\Models\SiteSetting::where('key', 'hospital_name')
                ->value('value') ?? config('app.name', 'Clinic');

            // Generate tracking token
            $trackingToken = \Illuminate\Support\Str::random(32);

            // Create email log first to get ID for tracking
            $emailLog = EmailLog::create([
                'recipient_email' => $patient->email,
                'recipient_name' => $patient->full_name,
                'subject' => $request->subject,
                'body' => $request->body,
                'status' => 'pending',
                'patient_id' => $patient->id,
                'metadata' => [
                    'doctor_id' => $doctor->id,
                    'tracking_token' => $trackingToken,
                ],
                'email_type' => 'patient_communication',
            ]);

            // Prepare email data (including tracking info)
            $emailData = [
                'subject' => $request->subject,
                'body' => $request->body,
                'doctor_name' => $doctor->name ?? $user->name,
                'doctor_specialization' => $doctor->specialization ?? 'General Practitioner',
                'clinic_name' => $clinicName,
                'department_name' => $department ? $department->name : null,
                'department_logo' => $department ? $department->logo_url : null,
                'date_sent' => now()->format('F j, Y'),
                'tracking_token' => $trackingToken,
                'email_log_id' => $emailLog->id,
            ];

            // Configure SMTP from database before sending
            try {
                $this->configureMailFromDatabase();
            } catch (Exception $smtpConfigException) {
                Log::warning('Failed to configure SMTP from database, using default config', [
                    'error' => $smtpConfigException->getMessage(),
                ]);
                // Continue with default mail configuration
            }

            // Send email using Mailable
            try {
                // Render and store the exact HTML we are about to send (for future viewing/audit)
                $renderedHtml = view('emails.patient-email', [
                    'emailData' => $emailData,
                    'trackingToken' => $trackingToken,
                    'emailLogId' => $emailLog->id,
                ])->render();

                // Also store a preview-safe version (no tracking pixel) for staff viewing.
                $renderedHtmlPreview = view('emails.patient-email', [
                    'emailData' => $emailData,
                    'trackingToken' => null,
                    'emailLogId' => null,
                ])->render();

                Mail::to($patient->email, $patient->full_name)
                    ->send(new PatientEmail($emailData));

                // Update email log with full metadata and mark as sent
                $emailLog->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'metadata' => array_merge($emailLog->metadata ?? [], [
                        'doctor_id' => $doctor->id,
                        'doctor_name' => $emailData['doctor_name'],
                        'doctor_specialization' => $emailData['doctor_specialization'],
                        'clinic_name' => $emailData['clinic_name'],
                        'department_name' => $emailData['department_name'],
                        'department_id' => $department ? $department->id : null,
                        'department_logo' => $emailData['department_logo'] ?? null,
                        'date_sent' => $emailData['date_sent'],
                        'tracking_token' => $trackingToken,
                        'rendered_html' => $renderedHtml,
                        'rendered_html_preview' => $renderedHtmlPreview,
                    ]),
                ]);

            Log::info('Patient email sent successfully', [
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'email_log_id' => $emailLog->id,
            ]);

                return redirect()->route('staff.patient-email.compose')
                    ->with('success', 'Email sent successfully to ' . $patient->full_name . '.');

            } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $transportException) {
                // SMTP connection error
                $errorMessage = $transportException->getMessage();
                
                // Provide user-friendly error message
                if (str_contains($errorMessage, 'Connection could not be established') || 
                    str_contains($errorMessage, 'getaddrinfo') ||
                    str_contains($errorMessage, 'No such host')) {
                    $userMessage = 'Email sending failed: Unable to connect to the email server. Please check your SMTP settings in Admin > Settings > Email Configuration and ensure the SMTP host is correct and accessible.';
                } else {
                    $userMessage = 'Failed to send email: ' . $errorMessage;
                }

                // Log failed email attempt
                try {
                    EmailLog::create([
                        'recipient_email' => $patient->email,
                        'recipient_name' => $patient->full_name,
                        'subject' => $request->subject,
                        'body' => $request->body,
                        'status' => 'failed',
                        'error_message' => $errorMessage,
                        'patient_id' => $patient->id,
                        'metadata' => [
                            'doctor_id' => $doctor->id,
                            'error_type' => 'transport_exception',
                        ],
                        'email_type' => 'patient_communication',
                    ]);
                } catch (Exception $logException) {
                    // Ignore logging errors
                }

                Log::error('Failed to send patient email - SMTP transport error', [
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'error' => $errorMessage,
                    'trace' => $transportException->getTraceAsString(),
                ]);

                return back()->withErrors(['error' => $userMessage])->withInput();
            }

        } catch (Exception $e) {
            Log::error('Failed to send patient email', [
                'patient_id' => $request->patient_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Log failed email attempt
            try {
                EmailLog::create([
                    'recipient_email' => $patient->email ?? null,
                    'recipient_name' => $patient->full_name ?? null,
                    'subject' => $request->subject,
                    'body' => $request->body,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'patient_id' => $request->patient_id,
                    'metadata' => [
                        'doctor_id' => $doctor->id ?? null,
                    ],
                    'email_type' => 'patient_communication',
                ]);
            } catch (Exception $logException) {
                // Ignore logging errors
            }

            return back()->withErrors(['error' => 'Failed to send email: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Track email open (called by tracking pixel).
     */
    public function track($token, $id)
    {
        try {
            $emailLog = EmailLog::find($id);
            
            if (!$emailLog) {
                // Return 1x1 transparent pixel even if email log not found
                return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 200)
                    ->header('Content-Type', 'image/gif')
                    ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            }

            // Verify tracking token matches
            $metadata = $emailLog->metadata ?? [];
            if (isset($metadata['tracking_token']) && $metadata['tracking_token'] === $token) {
                // Mark as opened if not already opened
                if (!$emailLog->wasOpened()) {
                    $emailLog->markAsOpened();
                }
            }

            // Return 1x1 transparent GIF pixel
            return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 200)
                ->header('Content-Type', 'image/gif')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        } catch (\Exception $e) {
            // Return pixel even on error to prevent breaking email rendering
            return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 200)
                ->header('Content-Type', 'image/gif')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }
    }
}
