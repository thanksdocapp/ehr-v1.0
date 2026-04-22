<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Services\HospitalEmailNotificationService;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    /**
     * @var string
     */
    protected $signature = 'appointments:send-reminders {--hours=24 : Hours before appointment to send reminder}';

    /**
     * @var string
     */
    protected $description = 'Send appointment reminders (patient, optionally doctor/clinic inbox)';

    protected $emailService;

    public function __construct(HospitalEmailNotificationService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!config('hospital.notifications.appointment_reminder.enabled', true)) {
            $this->info('Appointment reminders are disabled in configuration.');

            return 0;
        }

        $hoursBeforeAppointment = (int) $this->option('hours');
        $daysBefore = (int) ceil($hoursBeforeAppointment / 24);
        $targetDate = Carbon::today()->addDays($daysBefore);

        $this->info("Looking for appointments on {$targetDate->format('Y-m-d')} ({$daysBefore} days from now)...");

        $appointments = Appointment::with(['patient.user', 'doctor.user', 'department'])
            ->whereDate('appointment_date', $targetDate)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No appointments found for reminder sending.');

            return 0;
        }

        $this->info('Found '.$appointments->count().' pending/confirmed appointments on the target date (patient/doctor/staff reminders sent per configuration).');

        $cfg = config('hospital.notifications.appointment_reminder', []);
        $sendPatient = $cfg['send_to_patient'] ?? true;
        $sendDoctor = $cfg['send_to_doctor'] ?? true;
        $sendDept = $cfg['send_to_staff'] ?? false;

        $sentPatient = 0;
        $sentDoctor = 0;
        $sentDept = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($appointments as $appointment) {
            try {
                $patient = $appointment->patient;
                $patientEmail = $patient ? $this->emailService->getDeliverablePatientEmail($patient) : null;

                if ($sendPatient && $patientEmail) {
                    $dupLegacy = $this->emailService->patientAppointmentReminderLegacyAlreadySentToday($patientEmail, $appointment->id);
                    $dupEvent = $this->emailService->appointmentReminderAlreadySentToday(
                        $patientEmail,
                        $appointment->id,
                        'appointment.reminder_sent'
                    );
                    if (!$dupLegacy && !$dupEvent) {
                        $log = $this->emailService->sendAppointmentReminder($appointment, $daysBefore);
                        if ($log) {
                            $sentPatient++;
                            $this->info("✓ Patient reminder for appointment #{$appointment->id} → {$patientEmail}");
                        } else {
                            $errors++;
                            $this->error("✗ Patient reminder failed for appointment #{$appointment->id}");
                        }
                    } else {
                        $skipped++;
                        $this->warn("Reminder already sent today (patient) for appointment #{$appointment->id}");
                    }
                }

                $doctor = $appointment->doctor;
                $doctorEmail = $doctor ? $this->emailService->getDeliverableDoctorEmail($doctor) : null;

                if ($sendDoctor && $doctorEmail) {
                    if ($this->emailService->appointmentReminderAlreadySentToday(
                        $doctorEmail,
                        $appointment->id,
                        'appointment.reminder_sent_doctor'
                    )) {
                        $skipped++;
                        $this->warn("Reminder already sent today (doctor) for appointment #{$appointment->id}");
                    } elseif ($patientEmail && strcasecmp($doctorEmail, $patientEmail) === 0) {
                        $skipped++;
                        $this->warn("Skipping doctor reminder — same address as patient for appointment #{$appointment->id}");
                    } else {
                        $log = $this->emailService->sendAppointmentReminderToDoctor($appointment, $daysBefore);
                        if ($log) {
                            $sentDoctor++;
                            $this->info("✓ Doctor reminder for appointment #{$appointment->id} → {$doctorEmail}");
                        } else {
                            $errors++;
                            $this->error("✗ Doctor reminder failed for appointment #{$appointment->id}");
                        }
                    }
                }

                $deptEmail = $this->emailService->getDeliverableDepartmentEmail($appointment->department);

                if ($sendDept && $deptEmail) {
                    if ($patientEmail && strcasecmp($deptEmail, $patientEmail) === 0) {
                        $skipped++;
                        $this->warn("Skipping department reminder — same address as patient for appointment #{$appointment->id}");
                    } elseif ($doctorEmail && strcasecmp($deptEmail, $doctorEmail) === 0) {
                        $skipped++;
                        $this->warn("Skipping department reminder — same address as doctor for appointment #{$appointment->id}");
                    } elseif ($this->emailService->appointmentReminderAlreadySentToday(
                        $deptEmail,
                        $appointment->id,
                        'appointment.reminder_sent_department'
                    )) {
                        $skipped++;
                        $this->warn("Reminder already sent today (department) for appointment #{$appointment->id}");
                    } else {
                        $log = $this->emailService->sendAppointmentReminderToDepartment($appointment, $daysBefore);
                        if ($log) {
                            $sentDept++;
                            $this->info("✓ Department reminder for appointment #{$appointment->id} → {$deptEmail}");
                        } else {
                            $errors++;
                            $this->error("✗ Department reminder failed for appointment #{$appointment->id}");
                        }
                    }
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ Appointment #{$appointment->id}: {$e->getMessage()}");
                \Log::error('Appointment reminder command failed', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("\nReminder sending completed:");
        $this->info("• Patient emails: {$sentPatient}");
        $this->info("• Doctor emails: {$sentDoctor}");
        $this->info("• Department emails: {$sentDept}");
        if ($skipped > 0) {
            $this->info("• Skipped (duplicate or disabled): {$skipped}");
        }
        if ($errors > 0) {
            $this->error("• Errors: {$errors}");
        }

        return 0;
    }
}
