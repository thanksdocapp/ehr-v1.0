<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\EmailLog;

class HospitalQueueManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hospital:queue 
                          {action : The action to perform (start|stop|status|clear|restart|stats)}
                          {--queue= : Specific queue to manage}
                          {--timeout=60 : Queue worker timeout}
                          {--tries=3 : Number of retry attempts}
                          {--memory=512 : Memory limit in MB}
                          {--sleep=3 : Sleep seconds when no job available}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage hospital email notification queues';

    /**
     * Available hospital queues
     *
     * @var array
     */
    protected $hospitalQueues = [
        'emails' => 'Regular email notifications',
        'high-priority' => 'Urgent notifications (emergencies, critical results)',
        'reminders' => 'Appointment and payment reminders',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'start':
                return $this->startQueues();
            case 'stop':
                return $this->stopQueues();
            case 'status':
                return $this->showStatus();
            case 'clear':
                return $this->clearQueues();
            case 'restart':
                return $this->restartQueues();
            case 'stats':
                return $this->showStats();
            default:
                $this->error("Unknown action: {$action}");
                $this->info("Available actions: start, stop, status, clear, restart, stats");
                return 1;
        }
    }

    /**
     * Start queue workers
     */
    protected function startQueues()
    {
        $specificQueue = $this->option('queue');
        
        if ($specificQueue) {
            if (!array_key_exists($specificQueue, $this->hospitalQueues)) {
                $this->error("Unknown queue: {$specificQueue}");
                return 1;
            }
            return $this->startSingleQueue($specificQueue);
        }

        $this->info("Starting all hospital email queues...");
        
        foreach ($this->hospitalQueues as $queueName => $description) {
            $this->startSingleQueue($queueName);
        }
        
        $this->info("All hospital email queues started successfully!");
        $this->showQueueCommands();
        
        return 0;
    }

    /**
     * Start a single queue worker
     */
    protected function startSingleQueue($queueName)
    {
        $timeout = $this->option('timeout');
        $tries = $this->option('tries');
        $memory = $this->option('memory');
        $sleep = $this->option('sleep');
        
        $this->info("Starting queue worker for: {$queueName}");
        
        // Kill existing workers for this queue
        $this->killQueueWorkers($queueName);
        
        // Start new worker in background
        $command = sprintf(
            'nohup php artisan queue:work database --queue=%s --timeout=%d --tries=%d --memory=%d --sleep=%d > storage/logs/queue-%s.log 2>&1 &',
            $queueName,
            $timeout,
            $tries,
            $memory,
            $sleep,
            $queueName
        );
        
        exec($command);
        
        $this->line("✓ Queue worker started for: {$queueName}");
        return 0;
    }

    /**
     * Stop queue workers
     */
    protected function stopQueues()
    {
        $specificQueue = $this->option('queue');
        
        if ($specificQueue) {
            return $this->killQueueWorkers($specificQueue);
        }
        
        $this->info("Stopping all hospital email queue workers...");
        
        foreach ($this->hospitalQueues as $queueName => $description) {
            $this->killQueueWorkers($queueName);
        }
        
        $this->info("All hospital email queue workers stopped!");
        return 0;
    }

    /**
     * Kill queue workers for a specific queue
     */
    protected function killQueueWorkers($queueName = null)
    {
        if ($queueName) {
            $command = "pkill -f 'queue:work.*--queue={$queueName}'";
            $this->line("Stopping queue worker for: {$queueName}");
        } else {
            $command = "pkill -f 'queue:work'";
            $this->line("Stopping all queue workers...");
        }
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->line("✓ Queue workers stopped");
        } else {
            $this->line("- No running queue workers found");
        }
        
        return 0;
    }

    /**
     * Show queue status
     */
    protected function showStatus()
    {
        $this->info("Hospital Email Queue Status");
        $this->line(str_repeat('=', 50));
        
        // Check running processes
        $this->info("\nRunning Queue Workers:");
        exec("ps aux | grep 'queue:work' | grep -v grep", $processes);
        
        if (empty($processes)) {
            $this->error("No queue workers are currently running");
        } else {
            foreach ($processes as $process) {
                if (strpos($process, 'queue:work') !== false) {
                    // Extract queue name from process
                    preg_match('/--queue=([^\s]+)/', $process, $matches);
                    $queue = $matches[1] ?? 'default';
                    
                    // Extract PID
                    preg_match('/^\w+\s+(\d+)/', $process, $pidMatches);
                    $pid = $pidMatches[1] ?? 'unknown';
                    
                    $this->line("✓ Queue: {$queue} (PID: {$pid})");
                }
            }
        }
        
        // Show queue sizes
        $this->info("\nQueue Sizes:");
        foreach ($this->hospitalQueues as $queueName => $description) {
            $count = DB::table('jobs')->where('queue', $queueName)->count();
            $this->line("• {$queueName}: {$count} jobs pending");
        }
        
        // Show recent failures
        $this->info("\nRecent Failed Jobs:");
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(5)
            ->get(['queue', 'failed_at', 'exception']);
        
        if ($failedJobs->isEmpty()) {
            $this->line("No recent failed jobs");
        } else {
            foreach ($failedJobs as $job) {
                $failedAt = \Carbon\Carbon::parse($job->failed_at)->diffForHumans();
                $error = substr($job->exception, 0, 100) . '...';
                $this->error("• {$job->queue}: Failed {$failedAt} - {$error}");
            }
        }
        
        return 0;
    }

    /**
     * Clear queues
     */
    protected function clearQueues()
    {
        $specificQueue = $this->option('queue');
        
        if ($specificQueue) {
            if (!array_key_exists($specificQueue, $this->hospitalQueues)) {
                $this->error("Unknown queue: {$specificQueue}");
                return 1;
            }
            
            $count = DB::table('jobs')->where('queue', $specificQueue)->delete();
            $this->info("Cleared {$count} jobs from {$specificQueue} queue");
            return 0;
        }
        
        if (!$this->confirm('This will clear ALL pending jobs from hospital queues. Continue?')) {
            $this->info('Operation cancelled');
            return 0;
        }
        
        $totalCleared = 0;
        foreach ($this->hospitalQueues as $queueName => $description) {
            $count = DB::table('jobs')->where('queue', $queueName)->delete();
            $this->line("• Cleared {$count} jobs from {$queueName}");
            $totalCleared += $count;
        }
        
        $this->info("Total jobs cleared: {$totalCleared}");
        return 0;
    }

    /**
     * Restart queues
     */
    protected function restartQueues()
    {
        $this->info("Restarting hospital email queues...");
        
        $this->stopQueues();
        sleep(2); // Wait for processes to stop
        $this->startQueues();
        
        return 0;
    }

    /**
     * Show queue statistics
     */
    protected function showStats()
    {
        $this->info("Hospital Email Queue Statistics");
        $this->line(str_repeat('=', 50));
        
        // Email statistics from last 30 days
        $stats = EmailLog::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('
                COUNT(*) as total_emails,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent_emails,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_emails,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_emails,
                AVG(CASE WHEN sent_at IS NOT NULL THEN TIMESTAMPDIFF(SECOND, created_at, sent_at) END) as avg_processing_time
            ')
            ->first();
        
        if ($stats && $stats->total_emails > 0) {
            $successRate = round(($stats->sent_emails / $stats->total_emails) * 100, 2);
            $avgTime = round($stats->avg_processing_time, 2);
            
            $this->info("\nLast 30 Days Performance:");
            $this->line("• Total Emails: {$stats->total_emails}");
            $this->line("• Successfully Sent: {$stats->sent_emails}");
            $this->line("• Failed: {$stats->failed_emails}");
            $this->line("• Pending: {$stats->pending_emails}");
            $this->line("• Success Rate: {$successRate}%");
            $this->line("• Average Processing Time: {$avgTime} seconds");
        } else {
            $this->line("No email statistics available");
        }
        
        // Current queue sizes
        $this->info("\nCurrent Queue Status:");
        foreach ($this->hospitalQueues as $queueName => $description) {
            $pending = DB::table('jobs')->where('queue', $queueName)->count();
            $this->line("• {$queueName}: {$pending} jobs pending");
        }
        
        // System resources
        $this->info("\nSystem Resources:");
        $loadAvg = sys_getloadavg();
        $this->line("• Load Average: " . implode(', ', array_map(fn($load) => round($load, 2), $loadAvg)));
        $this->line("• Memory Usage: " . round(memory_get_usage(true) / 1024 / 1024, 2) . " MB");
        
        return 0;
    }

    /**
     * Show helpful queue management commands
     */
    protected function showQueueCommands()
    {
        $this->info("\nUseful Queue Management Commands:");
        $this->line("• Monitor queues: php artisan hospital:queue status");
        $this->line("• View queue stats: php artisan hospital:queue stats");
        $this->line("• Stop all queues: php artisan hospital:queue stop");
        $this->line("• Clear failed jobs: php artisan queue:flush");
        $this->line("• Retry failed jobs: php artisan queue:retry all");
        $this->line("• Real-time monitoring: watch -n 5 'php artisan hospital:queue status'");
    }
}
