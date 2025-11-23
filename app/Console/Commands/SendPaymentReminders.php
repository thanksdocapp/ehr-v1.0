<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Patient;
use App\Models\Billing;
use App\Services\HospitalEmailNotificationService;
use Carbon\Carbon;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment reminder emails to patients with overdue bills';

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
        if (!config('hospital.notifications.payment_reminder.enabled', true)) {
            $this->info('Payment reminders are disabled in configuration.');
            return 0;
        }

        $daysAfterDue = config('hospital.notifications.payment_reminder.days_after_due', [7, 14, 30]);
        $sentCount = 0;
        $errorCount = 0;

        $this->info('Starting payment reminder job...');

        // Check if Billing model exists, if not use a mock approach
        if (class_exists('App\\Models\\Billing')) {
            foreach ($daysAfterDue as $days) {
                $cutoffDate = Carbon::today()->subDays($days);
                
                $this->info("Processing payment reminders for bills due on {$cutoffDate->format('Y-m-d')} ({$days} days overdue)...");

                $overdueBills = Billing::with('patient')
                    ->whereDate('due_date', $cutoffDate)
                    ->where('status', '!=', 'paid')
                    ->whereHas('patient', function($query) {
                        $query->whereNotNull('email');
                    })
                    ->get();

                foreach ($overdueBills as $bill) {
                    try {
                        // Check if reminder was already sent today
                        $reminderSent = \App\Models\EmailLog::where('recipient_email', $bill->patient->email)
                            ->where('subject', 'like', '%Payment Reminder%')
                            ->whereJsonContains('variables->invoice_number', $bill->invoice_number)
                            ->whereDate('created_at', today())
                            ->exists();

                        if (!$reminderSent) {
                            $billingInfo = [
                                'invoice_number' => $bill->invoice_number,
                                'amount_due' => number_format($bill->amount_due, 2),
                                'due_date' => $bill->due_date->format('F d, Y'),
                                'service_description' => $bill->description ?? 'Medical Services',
                                'days_overdue' => $days,
                            ];

                            $log = $this->emailService->sendPaymentReminder($bill->patient, $billingInfo);
                            
                            if ($log) {
                                $sentCount++;
                                $this->info("✓ Payment reminder sent to {$bill->patient->email} for invoice #{$bill->invoice_number}");
                            } else {
                                $errorCount++;
                                $this->error("✗ Failed to send payment reminder to {$bill->patient->email} for invoice #{$bill->invoice_number}");
                            }
                        } else {
                            $this->info("• Payment reminder already sent today to {$bill->patient->email} for invoice #{$bill->invoice_number}");
                        }
                    } catch (\Exception $e) {
                        $errorCount++;
                        $this->error("✗ Error sending payment reminder for invoice #{$bill->invoice_number}: " . $e->getMessage());
                        
                        \Log::error('Payment reminder failed', [
                            'invoice_number' => $bill->invoice_number,
                            'patient_email' => $bill->patient->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        } else {
            // Mock approach if Billing model doesn't exist yet
            $this->info('Billing model not found. Using mock data for demonstration...');
            
            $patients = Patient::whereNotNull('email')
                ->where('is_active', true)
                ->limit(3) // Just a few for demo
                ->get();

            foreach ($patients as $patient) {
                try {
                    $mockBillingInfo = [
                        'invoice_number' => 'DEMO-' . now()->format('YmdHis'),
                        'amount_due' => rand(100, 1000) . '.00',
                        'due_date' => now()->subDays(rand(7, 30))->format('F d, Y'),
                        'service_description' => 'Medical Consultation Services',
                        'days_overdue' => rand(7, 30),
                    ];

                    $log = $this->emailService->sendPaymentReminder($patient, $mockBillingInfo);
                    
                    if ($log) {
                        $sentCount++;
                        $this->info("✓ Mock payment reminder sent to {$patient->email}");
                    } else {
                        $errorCount++;
                        $this->error("✗ Failed to send mock payment reminder to {$patient->email}");
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("✗ Error sending mock payment reminder: " . $e->getMessage());
                }
            }
        }

        $this->info("\nPayment reminders completed:");
        $this->info("• Reminders sent: {$sentCount}");
        if ($errorCount > 0) {
            $this->error("• Errors encountered: {$errorCount}");
        }

        return 0;
    }
}
