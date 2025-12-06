<?php

namespace App\Console\Commands;

use App\Services\NotificationDigestService;
use Illuminate\Console\Command;

class SendNotificationDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:digest
                            {type=daily : The type of digest to send (daily or weekly)}
                            {--patients : Send digest to patients only}
                            {--staff : Send digest to staff only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification digest emails to patients and staff';

    /**
     * Execute the console command.
     */
    public function handle(NotificationDigestService $digestService): int
    {
        $type = $this->argument('type');
        $patientsOnly = $this->option('patients');
        $staffOnly = $this->option('staff');

        if (!in_array($type, ['daily', 'weekly'])) {
            $this->error("Invalid digest type. Use 'daily' or 'weekly'.");
            return 1;
        }

        $this->info("Starting {$type} notification digest...");

        $totalStats = ['sent' => 0, 'skipped' => 0, 'errors' => 0];

        // Send to patients
        if (!$staffOnly) {
            $this->info('Processing patient digests...');

            $stats = $type === 'daily'
                ? $digestService->sendDailyPatientDigests()
                : $digestService->sendWeeklyPatientDigests();

            $this->info("Patients: {$stats['sent']} sent, {$stats['skipped']} skipped, {$stats['errors']} errors");

            $totalStats['sent'] += $stats['sent'];
            $totalStats['skipped'] += $stats['skipped'];
            $totalStats['errors'] += $stats['errors'];
        }

        // Send to staff
        if (!$patientsOnly) {
            $this->info('Processing staff digests...');

            $stats = $digestService->sendDailyStaffDigests();

            $this->info("Staff: {$stats['sent']} sent, {$stats['skipped']} skipped, {$stats['errors']} errors");

            $totalStats['sent'] += $stats['sent'];
            $totalStats['skipped'] += $stats['skipped'];
            $totalStats['errors'] += $stats['errors'];
        }

        $this->newLine();
        $this->info("Digest complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Emails Sent', $totalStats['sent']],
                ['Skipped (no notifications)', $totalStats['skipped']],
                ['Errors', $totalStats['errors']],
            ]
        );

        return $totalStats['errors'] > 0 ? 1 : 0;
    }
}
