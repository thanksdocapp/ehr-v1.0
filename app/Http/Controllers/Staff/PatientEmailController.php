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

            // Prepare email data
            $emailData = [
                'subject' => $request->subject,
                'body' => $request->body,
                'doctor_name' => $doctor->name ?? $user->name,
                'doctor_specialization' => $doctor->specialization ?? 'General Practitioner',
                'clinic_name' => $clinicName,
                'department_name' => $department ? $department->name : null,
                'department_logo' => $department ? $department->logo_url : null,
                'date_sent' => now()->format('F j, Y'),
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
                Mail::to($patient->email, $patient->full_name)
                    ->send(new PatientEmail($emailData));

                // Log email
                $emailLog = EmailLog::create([
                'recipient_email' => $patient->email,
                'recipient_name' => $patient->full_name,
                'subject' => $request->subject,
                'body' => $emailData['body'], // Store the doctor's message body
                'status' => 'sent',
                'sent_at' => now(),
                'patient_id' => $patient->id,
                'metadata' => [
                    'doctor_id' => $doctor->id,
                    'doctor_name' => $emailData['doctor_name'],
                    'doctor_specialization' => $emailData['doctor_specialization'],
                    'clinic_name' => $emailData['clinic_name'],
                    'department_name' => $emailData['department_name'],
                    'department_id' => $department ? $department->id : null,
                    'date_sent' => $emailData['date_sent'],
                ],
                'email_type' => 'patient_communication',
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
}
