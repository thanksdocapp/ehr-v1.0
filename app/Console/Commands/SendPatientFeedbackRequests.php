<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\PatientFeedbackSurvey;
use App\Services\HospitalEmailNotificationService;
use App\Services\PatientFeedbackService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class SendPatientFeedbackRequests extends Command
{
    protected $signature = 'appointments:send-feedback-requests {--days=2 : Days after completion to send feedback form}';

    protected $description = 'Send patient feedback forms after completed consultations';

    public function __construct(
        protected PatientFeedbackService $feedbackService,
        protected HospitalEmailNotificationService $emailService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (!config('hospital.notifications.patient_feedback.enabled', true)) {
            $this->info('Patient feedback requests are disabled in configuration.');
            return 0;
        }

        $daysAfter = (int) $this->option('days');
        $targetDate = Carbon::today()->subDays($daysAfter);

        $this->info("Looking for completed appointments on {$targetDate->format('Y-m-d')} to send feedback...");

        // Completed based on check_out_time; fallback to updated_at for records without check_out_time
        $appointments = Appointment::with(['patient', 'doctor', 'department'])
            ->where('status', 'completed')
            ->where(function ($q) use ($targetDate) {
                $q->whereDate('check_out_time', $targetDate)
                  ->orWhere(function ($q2) use ($targetDate) {
                      $q2->whereNull('check_out_time')
                         ->whereDate('updated_at', $targetDate);
                  });
            })
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No completed appointments found.');
            return 0;
        }

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($appointments as $appointment) {
            try {
                // Require patient email to send
                if (!$appointment->patient || empty($appointment->patient->email)) {
                    $skipped++;
                    continue;
                }

                // Create or fetch survey + ensure snapshot exists
                $survey = $this->feedbackService->createSurveyForAppointment($appointment);
                if (!$survey) {
                    $skipped++;
                    continue;
                }

                // Skip if already submitted (don’t bother patients)
                if ($survey->submitted_at) {
                    $skipped++;
                    continue;
                }

                // Skip if already sent
                if ($survey->sent_at) {
                    $skipped++;
                    continue;
                }

                $this->sendSurveyEmail($survey);
                $survey->sent_at = now();
                $survey->save();

                $sent++;
            } catch (\Exception $e) {
                $failed++;
                Log::error('Failed to send feedback request', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Done. Sent: {$sent}, Skipped: {$skipped}, Failed: {$failed}");
        return 0;
    }

    protected function sendSurveyEmail(PatientFeedbackSurvey $survey): void
    {
        $token = Crypt::decryptString($survey->token_encrypted);
        $feedbackUrl = url('/feedback/' . $token);

        $appointment = $survey->appointment()->with(['doctor', 'department', 'patient'])->first();
        if (!$appointment || !$appointment->patient || empty($appointment->patient->email)) {
            return;
        }

        $this->emailService->sendPatientFeedbackRequest($appointment, $feedbackUrl);
    }
}


