<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\LabReport;
use App\Models\Billing;
use App\Services\NotificationService;

class TestWorkflowNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test-workflows';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test workflow notification triggers for appointments, prescriptions, lab reports, and billing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing workflow notification triggers...');

        $notificationService = app(NotificationService::class);
        
        // Test appointment notifications
        $this->testAppointmentNotifications($notificationService);
        
        // Test prescription notifications
        $this->testPrescriptionNotifications($notificationService);
        
        // Test lab report notifications
        $this->testLabReportNotifications($notificationService);
        
        // Test billing notifications
        $this->testBillingNotifications($notificationService);

        $this->info('All workflow notification tests completed!');
        
        return Command::SUCCESS;
    }

    private function testAppointmentNotifications(NotificationService $notificationService)
    {
        $this->info('Testing appointment notifications...');
        
        $appointment = Appointment::with(['patient', 'doctor'])->first();
        
        if ($appointment) {
            try {
                $notificationService->sendAppointmentNotification($appointment, 'confirmed');
                $this->info('✅ Appointment confirmed notification sent');
            } catch (\Exception $e) {
                $this->error('❌ Appointment notification failed: ' . $e->getMessage());
            }
        } else {
            $this->warn('No appointments found for testing');
        }
    }

    private function testPrescriptionNotifications(NotificationService $notificationService)
    {
        $this->info('Testing prescription notifications...');
        
        $prescription = Prescription::with(['patient', 'doctor'])->first();
        
        if ($prescription) {
            try {
                $notificationService->sendPrescriptionNotification($prescription, 'created');
                $this->info('✅ Prescription created notification sent');
            } catch (\Exception $e) {
                $this->error('❌ Prescription notification failed: ' . $e->getMessage());
            }
        } else {
            $this->warn('No prescriptions found for testing');
        }
    }

    private function testLabReportNotifications(NotificationService $notificationService)
    {
        $this->info('Testing lab report notifications...');
        
        $labReport = LabReport::with(['patient', 'doctor'])->first();
        
        if ($labReport) {
            try {
                $notificationService->sendLabResultNotification($labReport, 'completed');
                $this->info('✅ Lab report completed notification sent');
            } catch (\Exception $e) {
                $this->error('❌ Lab report notification failed: ' . $e->getMessage());
            }
        } else {
            $this->warn('No lab reports found for testing');
        }
    }

    private function testBillingNotifications(NotificationService $notificationService)
    {
        $this->info('Testing billing notifications...');
        
        $billing = Billing::with(['patient'])->first();
        
        if ($billing) {
            try {
                $notificationService->sendBillingNotification($billing, 'invoice_created');
                $this->info('✅ Billing invoice created notification sent');
            } catch (\Exception $e) {
                $this->error('❌ Billing notification failed: ' . $e->getMessage());
            }
        } else {
            $this->warn('No billing records found for testing');
        }
    }
}
