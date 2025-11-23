<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Services\DatabaseSafetyService;

class SafeMigrateFresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:safe-fresh 
                            {--seed : Indicates if the seed task should be re-run}
                            {--force : Force the operation to run when in production}
                            {--backup : Create backup before fresh migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely run migrate:fresh with backup and confirmation prompts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if database has data
        $hasData = DatabaseSafetyService::hasData();

        if ($hasData && !$this->option('force')) {
            $this->error('⚠️  WARNING: This will delete ALL data in your database!');
            $this->newLine();

            // Show current database statistics
            $stats = DatabaseSafetyService::getDatabaseStats();
            $this->info('Current database statistics:');
            foreach ($stats as $label => $count) {
                $this->line("  - {$label}: {$count}");
            }
            $this->newLine();

            // Create backup if requested
            $createBackup = $this->option('backup');
            if (!$createBackup) {
                $createBackup = $this->confirm('Would you like to create a backup before proceeding?', true);
            }

            if ($createBackup) {
                $this->info('Creating backup...');
                Artisan::call('db:backup', ['--keep' => 10]);
                $this->info(Artisan::output());
                $this->newLine();
            }

            // Final confirmation
            if (!$this->confirm('Are you ABSOLUTELY SURE you want to delete all data? This cannot be undone!', false)) {
                $this->info('Operation cancelled.');
                return 1;
            }
        }

        // Build migrate:fresh command
        $options = [];
        if ($this->option('seed')) {
            $options['--seed'] = true;
        }
        if ($this->option('force')) {
            $options['--force'] = true;
        }

        $this->info('Running migrate:fresh...');
        $exitCode = Artisan::call('migrate:fresh', $options);
        $this->info(Artisan::output());

        if ($exitCode === 0) {
            $this->info('✅ Database has been refreshed successfully!');
        }

        return $exitCode;
    }
}

