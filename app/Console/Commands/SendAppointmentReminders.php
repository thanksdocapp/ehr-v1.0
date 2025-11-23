<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Services\HospitalEmailNotificationService;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders {--hours=24 : Hours before appointment to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send appointment reminders to patients and doctors';

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

        $hoursBeforeAppointment = $this->option('hours');
        $daysBefore = (int) ceil($hoursBeforeAppointment / 24); // Convert hours to days
        $targetDate = Carbon::today()->addDays($daysBefore);
        
        $this->info("Looking for appointments on {$targetDate->format('Y-m-d')} ({$daysBefore} days from now)...");
        
        // Get appointments that need reminders
        $appointments = Appointment::with(['patient', 'doctor', 'department'])
            ->whereDate('appointment_date', $targetDate)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereHas('patient', function($query) {
                $query->whereNotNull('email');
            })
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No appointments found for reminder sending.');
            return 0;
        }

        $this->info("Found {$appointments->count()} appointments for reminder sending.");
        
        $sentCount = 0;
        $errorCount = 0;
        
        foreach ($appointments as $appointment) {
            try {
                // Check if reminder was already sent today for this appointment
                $existingReminder = \App\Models\EmailLog::where('recipient_email', $appointment->patient->email)
                    ->where('subject', 'like', '%Appointment Reminder%')
                    ->whereJsonContains('variables->appointment_id', (string)$appointment->id)
                    ->whereDate('created_at', today())
                    ->exists();
                    
                if ($existingReminder) {
                    $this->warn("Reminder already sent today for appointment #{$appointment->id}");
                    continue;
                }
                
                // Send reminder notification
                $log = $this->emailService->sendAppointmentReminder($appointment, $daysBefore);
                
                if ($log) {
                    $sentCount++;
                    $this->info("✓ Reminder sent for appointment #{$appointment->id} to {$appointment->patient->email}");
                } else {
                    $errorCount++;
                    $this->error("✗ Failed to send reminder for appointment #{$appointment->id}");
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("✗ Failed to send reminder for appointment #{$appointment->id}: {$e->getMessage()}");
                
                // Log the error
                \Log::error('Appointment reminder command failed', [
                    'appointment_id' => $appointment->id,
                    'patient_email' => $appointment->patient->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $this->info("\nReminder sending completed:");
        $this->info("✓ Successfully sent: {$sentCount}");
        if ($errorCount > 0) {
            $this->error("✗ Failed: {$errorCount}");
        }
        
        return 0;
    }
}
