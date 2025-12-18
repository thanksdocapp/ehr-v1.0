<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Setting;
use App\Models\PatientFeedbackSurvey;
use App\Services\HospitalEmailNotificationService;
use App\Services\PatientFeedbackService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class SendPatientFeedbackRequests extends Command
{
    protected $signature = 'appointments:send-feedback-requests
                            {--delay-minutes= : Minutes after completion to send feedback form (1..4320)}
                            {--days= : Days after completion (deprecated; use --delay-minutes)}';

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

        $delayMinutes = $this->resolveDelayMinutes();
        $cutoff = Carbon::now()->subMinutes($delayMinutes);

        $this->info("Looking for completed appointments on/before {$cutoff->format('Y-m-d H:i')} (delay: {$delayMinutes} minutes) to send feedback...");

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        // Completed based on check_out_time; fallback to updated_at for records without check_out_time.
        // Only consider appointments that haven't had a submitted/sent survey yet.
        $query = Appointment::with(['patient', 'doctor', 'department', 'feedbackSurvey'])
            ->where('status', 'completed')
            ->where(function ($q) use ($cutoff) {
                $q->where(function ($q1) use ($cutoff) {
                    $q1->whereNotNull('check_out_time')
                       ->where('check_out_time', '<=', $cutoff);
                })->orWhere(function ($q2) use ($cutoff) {
                    $q2->whereNull('check_out_time')
                       ->where('updated_at', '<=', $cutoff);
                });
            })
            ->where(function ($q) {
                $q->whereDoesntHave('feedbackSurvey')
                  ->orWhereHas('feedbackSurvey', function ($sq) {
                      $sq->whereNull('sent_at')
                         ->whereNull('submitted_at');
                  });
            })
            ->orderBy('id');

        $foundAny = false;
        $query->chunkById(200, function ($appointments) use (&$sent, &$skipped, &$failed, &$foundAny) {
            $foundAny = $foundAny || $appointments->isNotEmpty();

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
        });

        if (!$foundAny) {
            $this->info('No eligible completed appointments found.');
            return 0;
        }

        $this->info("Done. Sent: {$sent}, Skipped: {$skipped}, Failed: {$failed}");
        return 0;
    }

    protected function resolveDelayMinutes(): int
    {
        $optDelay = $this->option('delay-minutes');
        if ($optDelay !== null && $optDelay !== '') {
            $m = (int) $optDelay;
            return max(1, min(4320, $m));
        }

        $optDays = $this->option('days');
        if ($optDays !== null && $optDays !== '') {
            $d = (int) $optDays;
            $m = max(1, $d) * 1440;
            return max(1, min(4320, $m));
        }

        // Admin setting (DB) overrides env/config
        try {
            $settings = Setting::getGroup('patient_feedback');
            $m = (int) ($settings['patient_feedback_delay_minutes'] ?? 0);
            if ($m > 0) {
                return max(1, min(4320, $m));
            }
        } catch (\Exception $e) {
            // Ignore DB issues; fall back to config
        }

        $m = (int) config('hospital.notifications.patient_feedback.delay_minutes', 0);
        if ($m > 0) {
            return max(1, min(4320, $m));
        }

        $days = (int) config('hospital.notifications.patient_feedback.days_after_completion', 2);
        $m = ($days > 0 ? $days : 2) * 1440;
        return max(1, min(4320, $m));
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


