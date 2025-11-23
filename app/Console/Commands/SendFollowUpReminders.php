<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MedicalRecord;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Carbon\Carbon;

class SendFollowUpReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medical-records:send-follow-up-reminders {--days=7 : Days before follow-up date to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send follow-up reminders to patients for upcoming follow-up appointments';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysBeforeFollowUp = $this->option('days');
        $reminderDate = Carbon::now()->addDays($daysBeforeFollowUp)->toDateString();
        
        $this->info("Looking for follow-ups due on {$reminderDate} (in {$daysBeforeFollowUp} days)...");
        
        // Get medical records with follow-up dates that need reminders
        $medicalRecords = MedicalRecord::with(['patient.user', 'doctor.user'])
            ->whereDate('follow_up_date', $reminderDate)
            ->whereNotNull('follow_up_date')
            ->get();

        if ($medicalRecords->isEmpty()) {
            $this->info('No follow-up reminders to send.');
            return;
        }

        $this->info("Found {$medicalRecords->count()} follow-up reminders to send.");
        
        $sentCount = 0;
        $errorCount = 0;
        
        foreach ($medicalRecords as $medicalRecord) {
            try {
                // Check if follow-up reminder was already sent (to avoid duplicates)
                $existingReminder = UserNotification::where('user_id', $medicalRecord->patient->user->id)
                    ->where('type', UserNotification::TYPE_MEDICAL_RECORD)
                    ->where('title', 'Follow-up Appointment Reminder')
                    ->where('related_appointment_id', $medicalRecord->id)
                    ->whereDate('created_at', today())
                    ->exists();
                    
                if ($existingReminder) {
                    $this->warn("Follow-up reminder already sent for medical record #{$medicalRecord->id}");
                    continue;
                }
                
                // Send follow-up reminder notification
                $this->sendFollowUpReminder($medicalRecord);
                
                $sentCount++;
                $this->info("✓ Follow-up reminder sent for {$medicalRecord->patient->user->name} (Record #{$medicalRecord->id})");
                
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("✗ Failed to send follow-up reminder for record #{$medicalRecord->id}: {$e->getMessage()}");
            }
        }
        
        $this->info("\nFollow-up reminder sending completed:");
        $this->info("✓ Successfully sent: {$sentCount}");
        if ($errorCount > 0) {
            $this->error("✗ Failed: {$errorCount}");
        }
    }

    /**
     * Send a follow-up reminder notification
     */
    protected function sendFollowUpReminder(MedicalRecord $medicalRecord)
    {
        $patient = $medicalRecord->patient->user;
        $doctorName = $medicalRecord->doctor ? $medicalRecord->doctor->user->name : 'Your healthcare provider';
        
        $this->notificationService->createNotification($patient, [
            'type' => UserNotification::TYPE_MEDICAL_RECORD,
            'category' => UserNotification::CATEGORY_MEDICAL,
            'title' => 'Follow-up Appointment Reminder',
            'message' => "Reminder: You have a follow-up appointment recommended by Dr. {$doctorName} due by {$medicalRecord->follow_up_date->format('F j, Y')}. Please schedule your appointment soon.",
            'action_url' => route('patient.appointments.create'),
            'priority' => 'high',
            'data' => [
                'medical_record_id' => $medicalRecord->id,
                'follow_up_date' => $medicalRecord->follow_up_date->format('Y-m-d'),
                'doctor_name' => $doctorName,
                'reminder_type' => 'follow_up'
            ],
            'related_appointment_id' => $medicalRecord->id,
            'related_patient_id' => $medicalRecord->patient_id,
            'related_doctor_id' => $medicalRecord->doctor_id,
        ]);

        // Also notify the doctor/staff
        if ($medicalRecord->doctor && $medicalRecord->doctor->user) {
            $this->notificationService->createNotification($medicalRecord->doctor->user, [
                'type' => UserNotification::TYPE_MEDICAL_RECORD,
                'category' => UserNotification::CATEGORY_MEDICAL,
                'title' => 'Patient Follow-up Due',
                'message' => "Follow-up reminder sent to {$patient->name} for follow-up due {$medicalRecord->follow_up_date->format('F j, Y')}.",
                'action_url' => route('staff.patients.show', $medicalRecord->patient_id),
                'priority' => 'medium',
                'data' => [
                    'medical_record_id' => $medicalRecord->id,
                    'patient_name' => $patient->name,
                    'follow_up_date' => $medicalRecord->follow_up_date->format('Y-m-d')
                ],
                'related_appointment_id' => $medicalRecord->id,
                'related_patient_id' => $medicalRecord->patient_id,
                'related_doctor_id' => $medicalRecord->doctor_id,
            ]);
        }
    }
}
