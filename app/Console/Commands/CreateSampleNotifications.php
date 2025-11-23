<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Patient;
use App\Models\UserNotification;
use App\Models\PatientNotification;

class CreateSampleNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:create-samples';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample notifications for testing the bell notification system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating sample notifications...');

        // Create admin notifications
        $this->createAdminNotifications();
        
        // Create staff notifications
        $this->createStaffNotifications();
        
        // Create patient notifications
        $this->createPatientNotifications();

        $this->info('Sample notifications created successfully!');
        
        return Command::SUCCESS;
    }

    private function createAdminNotifications()
    {
        $admins = User::where('is_admin', true)->get();
        
        if ($admins->isEmpty()) {
            $this->warn('No admin users found. Creating admin notifications skipped.');
            return;
        }

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => UserNotification::TYPE_SYSTEM,
                'title' => 'System Status Alert',
                'message' => 'Server performance is optimal. All systems running normally.',
                'priority' => 'medium',
                'category' => UserNotification::CATEGORY_SYSTEM,
            ]);

            UserNotification::create([
                'user_id' => $admin->id,
                'type' => UserNotification::TYPE_ALERT,
                'title' => 'Pending Appointments Review',
                'message' => 'There are 3 pending appointments that require approval.',
                'priority' => 'high',
                'category' => UserNotification::CATEGORY_ADMINISTRATIVE,
                'action_url' => '/admin/appointments?status=pending',
            ]);
        }

        $this->info('Admin notifications created.');
    }

    private function createStaffNotifications()
    {
        $staff = User::where('is_admin', false)->get();
        
        if ($staff->isEmpty()) {
            $this->warn('No staff users found. Creating staff notifications skipped.');
            return;
        }

        foreach ($staff as $user) {
            UserNotification::create([
                'user_id' => $user->id,
                'type' => UserNotification::TYPE_APPOINTMENT,
                'title' => 'Today\'s Schedule Update',
                'message' => 'You have 4 appointments scheduled for today. Next appointment at 2:00 PM.',
                'priority' => 'medium',
                'category' => UserNotification::CATEGORY_APPOINTMENT,
                'action_url' => '/staff/appointments',
            ]);

            UserNotification::create([
                'user_id' => $user->id,
                'type' => UserNotification::TYPE_LAB_RESULT,
                'title' => 'Lab Results Available',
                'message' => 'Blood test results for patient are ready for review.',
                'priority' => 'high',
                'category' => UserNotification::CATEGORY_MEDICAL,
                'action_url' => '/staff/lab-reports',
            ]);
        }

        $this->info('Staff notifications created.');
    }

    private function createPatientNotifications()
    {
        $patients = Patient::limit(5)->get();
        
        if ($patients->isEmpty()) {
            $this->warn('No patients found. Creating patient notifications skipped.');
            return;
        }

        foreach ($patients as $patient) {
            PatientNotification::create([
                'patient_id' => $patient->id,
                'type' => PatientNotification::TYPE_APPOINTMENT,
                'title' => 'Appointment Reminder',
                'message' => 'Your appointment is scheduled for tomorrow at 10:00 AM with Dr. Smith.',
                'priority' => 'high',
                'category' => PatientNotification::CATEGORY_APPOINTMENT,
                'action_url' => '/patient/appointments',
            ]);

            PatientNotification::create([
                'patient_id' => $patient->id,
                'type' => PatientNotification::TYPE_BILLING,
                'title' => 'Invoice Available',
                'message' => 'Your invoice for the recent consultation is now available for payment.',
                'priority' => 'medium',
                'category' => PatientNotification::CATEGORY_BILLING,
                'action_url' => '/patient/billing',
            ]);
        }

        $this->info('Patient notifications created.');
    }
}
