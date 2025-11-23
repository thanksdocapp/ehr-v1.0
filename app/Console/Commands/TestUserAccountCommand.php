<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestUserAccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-account {--cleanup : Clean up test data after testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test UserAccount model functionality by creating and retrieving accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting UserAccount model test...');
        
        DB::beginTransaction();
        
        try {
            // Step 1: Find or create a test user
            $testUser = $this->findOrCreateTestUser();
            $this->info("Test user created/found: {$testUser->name} (ID: {$testUser->id})");
            
            // Step 2: Create a test account
            $testAccount = $this->createTestAccount($testUser);
            $this->info("Test account created successfully!");
            
            // Step 3: Display account details
            $this->displayAccountDetails($testAccount);
            
            // Step 4: Test account methods
            $this->testAccountMethods($testAccount);
            
            // Decide whether to commit or rollback based on --cleanup option
            if ($this->option('cleanup')) {
                DB::rollBack();
                $this->info('Test data cleaned up (transaction rolled back)');
            } else {
                DB::commit();
                $this->info('Test data committed to database');
            }
            
            $this->info('UserAccount model test completed successfully!');
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Test failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
    
    /**
     * Find an existing test user or create a new one
     */
    private function findOrCreateTestUser()
    {
        $testUser = User::where('email', 'test_account@example.com')->first();
        
        if (!$testUser) {
            $testUser = User::create([
                'name' => 'Test User',
                'first_name' => 'Test',
                'last_name' => 'User',
                'username' => 'testuser_' . Str::random(5),
                'email' => 'test_account@example.com',
                'password' => bcrypt('password123'),
                'pin_code' => '1234',
                'country_code' => 'US',
                'phone' => '5551234567',
                'account_number' => 'ACC' . mt_rand(1000000000, 9999999999),
                'status' => 'active',
                'is_verified' => true
            ]);
        }
        
        return $testUser;
    }
    
    /**
     * Create a test account for the given user
     */
    private function createTestAccount(User $user)
    {
        return UserAccount::create([
            'user_id' => $user->id,
            'account_number' => 'ACC' . mt_rand(1000000000, 9999999999),
            'account_name' => 'Test Savings Account',
            'account_type' => 'savings',
            'balance' => 5000.00,
            'available_balance' => 4500.00,
            'pending_balance' => 500.00,
            'currency' => 'USD',
            'daily_limit' => 1000.00,
            'monthly_limit' => 10000.00,
            'interest_rate' => 0.0325,
            'status' => 'active',
            'metadata' => json_encode([
                'source' => 'test_command',
                'purpose' => 'testing'
            ]),
            'opened_at' => now(),
        ]);
    }
    
    /**
     * Display account details in a formatted table
     */
    private function displayAccountDetails(UserAccount $account)
    {
        $this->info("\nAccount Details:");
        
        $headers = ['Property', 'Value'];
        $rows = [
            ['ID', $account->id],
            ['Account Number', $account->account_number],
            ['Account Name', $account->account_name],
            ['Account Type', $account->account_type],
            ['Balance', $account->formatted_balance],
            ['Available Balance', $account->formatted_available_balance],
            ['Pending Balance', $account->formatted_pending_balance],
            ['Currency', $account->currency],
            ['Daily Limit', '$' . number_format($account->daily_limit, 2)],
            ['Monthly Limit', '$' . number_format($account->monthly_limit, 2)],
            ['Interest Rate', $account->interest_rate * 100 . '%'],
            ['Status', $account->status],
            ['Opened At', $account->opened_at->format('Y-m-d H:i:s')],
            ['Account Age (days)', $account->account_age],
        ];
        
        $this->table($headers, $rows);
    }
    
    /**
     * Test various account methods and display results
     */
    private function testAccountMethods(UserAccount $account)
    {
        $this->info("\nTesting Account Methods:");
        
        $methodTests = [
            'isActive()' => $account->isActive(),
            'isSuspended()' => $account->isSuspended(),
            'isClosed()' => $account->isClosed(),
            'hasSufficientBalance(1000)' => $account->hasSufficientBalance(1000),
            'hasSufficientBalance(10000)' => $account->hasSufficientBalance(10000),
            'hasReachedDailyLimit(500)' => $account->hasReachedDailyLimit(500),
            'hasReachedDailyLimit(1500)' => $account->hasReachedDailyLimit(1500),
        ];
        
        $rows = [];
        foreach ($methodTests as $method => $result) {
            $rows[] = [
                $method,
                $result ? 'true' : 'false'
            ];
        }
        
        $this->table(['Method', 'Result'], $rows);
    }
}
