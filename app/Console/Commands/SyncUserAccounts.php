<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncUserAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-user-accounts {--dry-run : Show what would be created without actually creating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users and create default bank accounts for users who don\'t have any';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting user accounts synchronization...');
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get all users without bank accounts
        $usersWithoutAccounts = User::whereDoesntHave('accounts')
            ->where('email_verified_at', '!=', null)
            ->get();

        $this->info("Found {$usersWithoutAccounts->count()} users without bank accounts.");

        if ($usersWithoutAccounts->isEmpty()) {
            $this->info('All users already have bank accounts!');
            return;
        }

        $created = 0;
        $errors = 0;

        foreach ($usersWithoutAccounts as $user) {
            try {
                $this->line("Processing user: {$user->first_name} {$user->last_name} ({$user->email})");
                
                if (!$isDryRun) {
                    DB::beginTransaction();
                    
                    // Create a default savings account
                    UserAccount::create([
                        'user_id' => $user->id,
                        'account_type' => 'savings',
                        'account_number' => $this->generateAccountNumber(),
                        'account_name' => "{$user->first_name} {$user->last_name} - Savings",
                        'balance' => $user->account_balance ?? 0.00,
                        'currency' => 'USD',
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    DB::commit();
                    $this->info("✓ Created savings account for {$user->email}");
                } else {
                    $this->info("[DRY RUN] Would create savings account for {$user->email}");
                }
                
                $created++;
                
            } catch (\Exception $e) {
                if (!$isDryRun) {
                    DB::rollBack();
                }
                $this->error("✗ Failed to create account for {$user->email}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->info("\nSynchronization completed!");
        $this->info("Accounts created: {$created}");
        
        if ($errors > 0) {
            $this->warn("Errors encountered: {$errors}");
        }
        
        if ($isDryRun) {
            $this->warn("This was a dry run. Run without --dry-run to actually create the accounts.");
        }
    }

    /**
     * Generate a unique account number
     */
    private function generateAccountNumber()
    {
        do {
            $accountNumber = '1' . str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
        } while (UserAccount::where('account_number', $accountNumber)->exists());
        
        return $accountNumber;
    }
}
