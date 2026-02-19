<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\EmailTemplate;
use App\Models\PatientFeedbackResponse;
use App\Models\PatientFeedbackSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Services\PatientFeedbackService;
use App\Services\EmailNotificationService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PatientFeedbackController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('patient_feedback_surveys')) {
            abort(500, 'Patient Feedback is not installed on this database yet. Please run migrations.');
        }

        $surveys = PatientFeedbackSurvey::query()
            ->whereNotNull('submitted_at')
            ->with(['doctor', 'patient', 'appointment'])
            // Exclude "Not Applicable" (stored as score=0) from averages
            ->withAvg(['responses as responses_avg_score' => function ($q) {
                $q->where('score', '>', 0);
            }], 'score')
            ->orderByDesc('submitted_at')
            ->paginate(25);

        return view('admin.patient-feedback.index', compact('surveys'));
    }

    public function show(PatientFeedbackSurvey $survey)
    {
        if (!Schema::hasTable('patient_feedback_surveys')) {
            abort(500, 'Patient Feedback is not installed on this database yet. Please run migrations.');
        }

        $survey->load(['doctor', 'patient', 'appointment', 'questions', 'responses.surveyQuestion']);

        return view('admin.patient-feedback.show', compact('survey'));
    }

    /**
     * Admin-only: reset a submitted survey so the same link can be filled again (testing).
     * This deletes existing responses and clears submitted fields.
     */
    public function resetSubmission(PatientFeedbackSurvey $survey)
    {
        if (!Schema::hasTable('patient_feedback_surveys')) {
            abort(500, 'Patient Feedback is not installed on this database yet. Please run migrations.');
        }

        // Ensure appointment relation available for restoring patient_id safely
        $survey->loadMissing('appointment');

        if (!$survey->submitted_at) {
            return redirect()->route('admin.patient-feedback.show', $survey)
                ->with('error', 'This survey has not been submitted yet, so there is nothing to reset.');
        }

        DB::transaction(function () use ($survey) {
            // Delete stored responses
            PatientFeedbackResponse::where('survey_id', $survey->id)->delete();

            // Reset survey state
            $survey->submitted_at = null;
            $survey->additional_comments = null;

            // Reset anonymity choice so patient can choose again on re-submit
            $survey->is_anonymous = false;
            $survey->patient_id = $survey->appointment?->patient_id ?? $survey->patient_id;

            $survey->save();
        });

        return redirect()->route('admin.patient-feedback.show', $survey)
            ->with('success', 'Feedback submission reset. The patient can submit again using the same link.');
    }

    public function testEmailForm()
    {
        if (!Schema::hasTable('patient_feedback_surveys')) {
            abort(500, 'Patient Feedback is not installed on this database yet. Please run migrations.');
        }

        $appointments = Appointment::with(['patient', 'doctor', 'department'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('id')
            ->limit(150)
            ->get();

        return view('admin.patient-feedback.test-email', compact('appointments'));
    }

    public function sendTestEmail(Request $request, PatientFeedbackService $feedbackService, EmailNotificationService $emailService)
    {
        if (!Schema::hasTable('patient_feedback_surveys')) {
            abort(500, 'Patient Feedback is not installed on this database yet. Please run migrations.');
        }

        $data = $request->validate([
            'appointment_id' => 'required|integer|exists:appointments,id',
            'test_email' => 'required|email|max:255',
        ]);

        // Ensure the email template exists in DB (seeders may not have been run yet)
        $templateExists = EmailTemplate::where('name', 'patient_feedback_request')->exists();
        if (!$templateExists) {
            return redirect()->route('admin.patient-feedback.test-email.form')
                ->with('error', "Email template 'patient_feedback_request' was not found. Please run the Email Template Seeder (Admin → Developer Tools → Email Template Seeder) or run: php artisan seed:email-templates --force");
        }

        $appointment = Appointment::with(['patient', 'doctor', 'department'])->findOrFail($data['appointment_id']);

        // Create or fetch the survey for this appointment (requires completed status)
        $survey = $feedbackService->createSurveyForAppointment($appointment);
        if (!$survey) {
            return back()->with('error', 'Selected appointment is not eligible (must be completed). Please select a completed appointment.')->withInput();
        }

        $token = Crypt::decryptString($survey->token_encrypted);
        $feedbackUrl = url('/feedback/' . $token);

        $patientName = $appointment->patient?->full_name
            ?? trim(($appointment->patient->first_name ?? '') . ' ' . ($appointment->patient->last_name ?? ''))
            ?: 'Patient';

        $doctorName = $appointment->doctor?->name ?? 'Clinician';
        $appointmentTime = $appointment->appointment_time;
        if ($appointmentTime) {
            try {
                $appointmentTime = \Carbon\Carbon::parse($appointmentTime)->format('g:i A');
            } catch (\Exception $e) {
                // Keep original
            }
        }

        $variables = [
            'patient_name' => $patientName,
            'doctor_name' => $doctorName,
            'appointment_date' => $appointment->appointment_date ? formatDateUkLong($appointment->appointment_date) : '',
            'appointment_time' => $appointmentTime,
            'department' => $appointment->department ? $appointment->department->name : 'General',
            'hospital_name' => config('app.name', 'Hospital'),
            'feedback_url' => $feedbackUrl,
        ];

        try {
            $emailLog = $emailService->sendTemplateEmail(
                'patient_feedback_request',
                [$data['test_email'] => 'Test Recipient'],
                $variables,
                [
                    // NOTE: email_logs.email_type is restricted (enum on some installs). Use a known-safe value.
                    'email_type' => 'general',
                    'event' => 'patient_feedback_test',
                    'patient_id' => $appointment->patient_id ?? null,
                    'metadata' => [
                        'appointment_id' => $appointment->id,
                        'survey_id' => $survey->id,
                        'is_test' => true,
                    ],
                    // Allow surfacing the real exception for this admin test tool
                    'debug_throw' => true,
                ]
            );
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $msg = Str::limit($msg, 280);
            return redirect()->route('admin.patient-feedback.test-email.form')
                ->with('error', 'Test email failed before logging. Error: ' . $msg);
        }

        if (!$emailLog) {
            return redirect()->route('admin.patient-feedback.test-email.form')
                ->with('error', 'Email was not sent (no Email Log was created). Please check Email Configuration and try again.');
        }

        if ($emailLog->status !== 'sent') {
            $reason = $emailLog->error_message ? (' Reason: ' . $emailLog->error_message) : '';
            return redirect()->route('admin.patient-feedback.test-email.form')
                ->with('error', 'Test email FAILED (status: ' . $emailLog->status . ').' . $reason)
                ->with('email_log_id', $emailLog->id);
        }

        return redirect()->route('admin.patient-feedback.test-email.form')
            ->with('success', 'Test feedback email SENT to ' . $data['test_email'] . '.')
            ->with('email_log_id', $emailLog->id);
    }
}


