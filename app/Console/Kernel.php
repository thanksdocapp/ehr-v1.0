<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Hospital Email Notifications Schedule
        
        // Send appointment reminders 24 hours before appointments
        $schedule->command('appointments:send-reminders --hours=24')
                 ->dailyAt('09:00')
                 ->timezone(config('app.timezone', 'UTC'))
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Send appointment reminders 2 hours before appointments
        $schedule->command('appointments:send-reminders --hours=2')
                 ->hourly()
                 ->between('07:00', '18:00')
                 ->timezone(config('app.timezone', 'UTC'))
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Send payment reminders for overdue bills
        $schedule->command('payments:send-reminders')
                 ->dailyAt('10:00')
                 ->timezone(config('app.timezone', 'UTC'))
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Process email queue (ensure emails are sent)
        $schedule->command('queue:work emails --stop-when-empty --max-time=300')
                 ->everyFifteenMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Clean up old email logs (keep only last 3 months)
        $schedule->call(function () {
            \App\Models\EmailLog::where('created_at', '<', now()->subMonths(3))->delete();
        })->monthly()->name('cleanup-old-email-logs');
        
        // Database Backup Schedule
        // Daily backup at 1 AM (earlier than before)
        $schedule->command('db:backup --compress --keep=7')
                 ->dailyAt('01:00')
                 ->timezone(config('app.timezone', 'UTC'))
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/backup.log'));
        
        // Weekly backup on Sundays at 3 AM (keep for 4 weeks)
        $schedule->command('db:backup --compress --keep=28')
                 ->weeklyOn(0, '03:00')
                 ->timezone(config('app.timezone', 'UTC'))
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/backup.log'));
        
        // Send follow-up reminders 7 days before due date (if medical records exist)
        $schedule->command('medical-records:send-follow-up-reminders --days=7')
                 ->dailyAt('11:00')
                 ->timezone(config('app.timezone', 'UTC'))
                 ->withoutOverlapping()
                 ->skip(function () {
                     // Skip if command doesn't exist
                     return !class_exists('App\Console\Commands\SendMedicalRecordFollowUps');
                 });
        
        // Send follow-up reminders 1 day before due date (if medical records exist)
        $schedule->command('medical-records:send-follow-up-reminders --days=1')
                 ->dailyAt('09:30')
                 ->timezone(config('app.timezone', 'UTC'))
                 ->withoutOverlapping()
                 ->skip(function () {
                     // Skip if command doesn't exist
                     return !class_exists('App\Console\Commands\SendMedicalRecordFollowUps');
                 });

        // Notification Digest Emails
        // Send daily digest at 6 AM
        $schedule->command('notifications:digest daily')
                 ->dailyAt('06:00')
                 ->timezone(config('app.timezone', 'UTC'))
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/notification-digest.log'));

        // Send weekly digest on Mondays at 7 AM
        $schedule->command('notifications:digest weekly')
                 ->weeklyOn(1, '07:00')
                 ->timezone(config('app.timezone', 'UTC'))
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/notification-digest.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
