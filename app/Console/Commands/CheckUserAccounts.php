<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Support\Facades\DB;

class CheckUserAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-user-accounts {--detailed : Show detailed information for each user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and audit user accounts to identify potential issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking user accounts...');
        $detailed = $this->option('detailed');
        
        // Get statistics
        $totalUsers = User::count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $usersWithAccounts = User::has('accounts')->count();
        $usersWithoutAccounts = User::whereDoesntHave('accounts')->count();
        $totalAccounts = UserAccount::count();
        
        // Account type breakdown
        $savingsAccounts = UserAccount::where('account_type', 'savings')->count();
        $checkingAccounts = UserAccount::where('account_type', 'checking')->count();
        
        // Status breakdown
        $activeAccounts = UserAccount::where('status', 'active')->count();
        $suspendedAccounts = UserAccount::where('status', 'suspended')->count();
        $closedAccounts = UserAccount::where('status', 'closed')->count();
        
        // Balance statistics
        $totalBalance = UserAccount::sum('balance');
        $avgBalance = UserAccount::avg('balance');
        $maxBalance = UserAccount::max('balance');
        $minBalance = UserAccount::min('balance');
        
        // Display summary
        $this->info('\n=== USER ACCOUNTS AUDIT SUMMARY ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Users', number_format($totalUsers)],
                ['Verified Users', number_format($verifiedUsers)],
                ['Users with Bank Accounts', number_format($usersWithAccounts)],
                ['Users without Bank Accounts', number_format($usersWithoutAccounts)],
                ['Total Bank Accounts', number_format($totalAccounts)],
            ]
        );
        
        $this->info('\n=== ACCOUNT TYPE BREAKDOWN ===');
        $this->table(
            ['Account Type', 'Count'],
            [
                ['Savings Accounts', number_format($savingsAccounts)],
                ['Checking Accounts', number_format($checkingAccounts)],
            ]
        );
        
        $this->info('\n=== ACCOUNT STATUS BREAKDOWN ===');
        $this->table(
            ['Status', 'Count'],
            [
                ['Active', number_format($activeAccounts)],
                ['Suspended', number_format($suspendedAccounts)],
                ['Closed', number_format($closedAccounts)],
            ]
        );
        
        $this->info('\n=== BALANCE STATISTICS ===');
        $this->table(
            ['Metric', 'Amount (USD)'],
            [
                ['Total Balance', '$' . number_format($totalBalance, 2)],
                ['Average Balance', '$' . number_format($avgBalance, 2)],
                ['Highest Balance', '$' . number_format($maxBalance, 2)],
                ['Lowest Balance', '$' . number_format($minBalance, 2)],
            ]
        );
        
        // Check for potential issues
        $this->info('\n=== POTENTIAL ISSUES ===');
        $issues = [];
        
        if ($usersWithoutAccounts > 0) {
            $issues[] = "⚠️  {$usersWithoutAccounts} users don't have bank accounts";
        }
        
        $negativeBalances = UserAccount::where('balance', '<', 0)->count();
        if ($negativeBalances > 0) {
            $issues[] = "⚠️  {$negativeBalances} accounts have negative balances";
        }
        
        $duplicateNumbers = DB::table('user_accounts')
            ->select('account_number')
            ->groupBy('account_number')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        if ($duplicateNumbers > 0) {
            $issues[] = "⚠️  {$duplicateNumbers} duplicate account numbers found";
        }
        
        if (empty($issues)) {
            $this->info('✅ No issues detected!');
        } else {
            foreach ($issues as $issue) {
                $this->warn($issue);
            }
        }
        
        // Detailed view if requested
        if ($detailed) {
            $this->info('\n=== DETAILED USER INFORMATION ===');
            
            if ($usersWithoutAccounts > 0) {
                $this->warn('\nUsers without bank accounts:');
                $usersWithoutAccountsList = User::whereDoesntHave('accounts')
                    ->select('id', 'first_name', 'last_name', 'email', 'email_verified_at', 'account_balance')
                    ->get();
                    
                $this->table(
                    ['ID', 'Name', 'Email', 'Verified', 'Main Balance'],
                    $usersWithoutAccountsList->map(function ($user) {
                        return [
                            $user->id,
                            $user->first_name . ' ' . $user->last_name,
                            $user->email,
                            $user->email_verified_at ? 'Yes' : 'No',
                            '$' . number_format($user->account_balance ?? 0, 2)
                        ];
                    })->toArray()
                );
            }
            
            if ($negativeBalances > 0) {
                $this->warn('\nAccounts with negative balances:');
                $negativeAccounts = UserAccount::where('balance', '<', 0)
                    ->with('user')
                    ->get();
                    
                $this->table(
                    ['Account Number', 'User', 'Type', 'Balance'],
                    $negativeAccounts->map(function ($account) {
                        return [
                            $account->account_number,
                            $account->user->first_name . ' ' . $account->user->last_name,
                            ucfirst($account->account_type),
                            '$' . number_format($account->balance, 2)
                        ];
                    })->toArray()
                );
            }
        }
        
        $this->info('\nAudit completed!');
    }
}
