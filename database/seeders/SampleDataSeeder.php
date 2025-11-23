<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Transaction;
use App\Models\VirtualCard;
use App\Models\UserDeposit;
use App\Models\LoanApplication;
use App\Models\KycDocument;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample users
        $users = [
            [
                'name' => 'John Smith',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'username' => 'johnsmith',
                'email' => 'john.smith@example.com',
                'country_code' => '+1',
                'phone' => '234567890',
                'password' => Hash::make('password123'),
                'pin_code' => Hash::make('1234'),
                'account_type' => 'personal',
                'balance' => 5250.75,
                'status' => 'active',
                'kyc_verified' => true,
            ],
            [
                'name' => 'Sarah Johnson',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'username' => 'sarahjohnson',
                'email' => 'sarah.johnson@example.com',
                'country_code' => '+1',
                'phone' => '234567891',
                'password' => Hash::make('password123'),
                'pin_code' => Hash::make('5678'),
                'account_type' => 'business',
                'balance' => 12500.00,
                'status' => 'active',
                'kyc_verified' => true,
            ],
            [
                'name' => 'Michael Brown',
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'username' => 'michaelbrown',
                'email' => 'michael.brown@example.com',
                'country_code' => '+1',
                'phone' => '234567892',
                'password' => Hash::make('password123'),
                'pin_code' => Hash::make('9876'),
                'account_type' => 'personal',
                'balance' => 25000.00,
                'status' => 'active',
                'kyc_verified' => false,
            ],
            [
                'name' => 'Emily Davis',
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'username' => 'emilydavis',
                'email' => 'emily.davis@example.com',
                'country_code' => '+1',
                'phone' => '234567893',
                'password' => Hash::make('password123'),
                'pin_code' => Hash::make('4321'),
                'account_type' => 'personal',
                'balance' => 1250.50,
                'status' => 'suspended',
                'kyc_verified' => false,
            ],
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $createdUsers[] = User::create($userData);
        }

        // Create sample transactions
        $transactionTypes = ['deposit', 'withdrawal', 'transfer', 'fee'];
        $statuses = ['pending', 'processing', 'completed', 'failed', 'cancelled'];

        for ($i = 0; $i < 50; $i++) {
            $user = $createdUsers[array_rand($createdUsers)];
            $type = $transactionTypes[array_rand($transactionTypes)];
            $status = $statuses[array_rand($statuses)];
            $amount = rand(10, 5000) + (rand(0, 99) / 100);

            Transaction::create([
                'transaction_id' => 'TXN' . strtoupper(uniqid()),
                'user_id' => $user->id,
                'recipient_user_id' => $type === 'transfer' ? $createdUsers[array_rand($createdUsers)]->id : null,
                'type' => $type,
                'amount' => $amount,
                'fee' => $amount * 0.02, // 2% fee
                'description' => ucfirst($type) . ' transaction #' . ($i + 1),
                'status' => $status,
                'reference' => 'REF' . time() . $i,
                'admin_notes' => $status === 'failed' ? 'Insufficient funds' : null,
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
            ]);
        }

        // Create sample virtual cards
        foreach ($createdUsers as $user) {
            if (rand(0, 1)) { // 50% chance of having a virtual card
                VirtualCard::create([
                    'user_id' => $user->id,
                    'card_number' => '4' . str_pad(rand(0, 999999999999999), 15, '0', STR_PAD_LEFT),
                    'card_holder_name' => strtoupper($user->first_name . ' ' . $user->last_name),
                    'expiry_month' => rand(1, 12),
                    'expiry_year' => rand(2024, 2030),
                    'cvv' => str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT),
                    'card_type' => ['visa', 'mastercard', 'amex'][array_rand(['visa', 'mastercard', 'amex'])],
                    'balance' => rand(100, 5000),
                    'spending_limit' => rand(1000, 10000),
                    'status' => ['active', 'blocked'][array_rand(['active', 'blocked'])],
                    'online_payments' => (bool)rand(0, 1),
                    'atm_withdrawals' => (bool)rand(0, 1),
                    'contactless_payments' => (bool)rand(0, 1),
                    'usage_restrictions' => json_encode(['international' => (bool)rand(0, 1)]),
                ]);
            }
        }

        // Create sample deposits
        foreach ($createdUsers as $user) {
            for ($i = 0; $i < rand(1, 5); $i++) {
                UserDeposit::create([
                    'user_id' => $user->id,
                    'amount' => rand(100, 2000) + (rand(0, 99) / 100),
                    'payment_method' => ['bank_transfer', 'credit_card', 'paypal'][array_rand(['bank_transfer', 'credit_card', 'paypal'])],
                    'status' => ['pending', 'completed', 'failed'][array_rand(['pending', 'completed', 'failed'])],
                    'reference' => 'DEP' . time() . $i,
                    'admin_notes' => null,
                    'created_at' => Carbon::now()->subDays(rand(0, 60)),
                ]);
            }
        }

        // Create sample loan applications
        foreach ($createdUsers as $user) {
            if (rand(0, 2) === 0) { // 33% chance of having a loan application
                LoanApplication::create([
                    'user_id' => $user->id,
                    'loan_type' => ['personal', 'business', 'mortgage', 'auto'][array_rand(['personal', 'business', 'mortgage', 'auto'])],
                    'amount' => rand(5000, 100000),
                    'term_months' => [12, 24, 36, 48, 60][array_rand([12, 24, 36, 48, 60])],
                    'interest_rate' => rand(5, 15) + (rand(0, 99) / 100),
                    'purpose' => 'Sample loan application for testing purposes',
                    'employment_status' => 'employed',
                    'annual_income' => $user->annual_income,
                    'status' => ['pending', 'approved', 'rejected'][array_rand(['pending', 'approved', 'rejected'])],
                    'admin_notes' => null,
                    'created_at' => Carbon::now()->subDays(rand(0, 90)),
                ]);
            }
        }

        // Create sample KYC documents
        foreach ($createdUsers as $user) {
            if ($user->kyc_status !== 'approved') {
                KycDocument::create([
                    'user_id' => $user->id,
                    'document_type' => ['passport', 'drivers_license', 'national_id'][array_rand(['passport', 'drivers_license', 'national_id'])],
                    'document_number' => 'DOC' . rand(100000, 999999),
                    'file_path' => 'kyc/sample_document_' . $user->id . '.pdf',
                    'status' => $user->kyc_status,
                    'admin_notes' => $user->kyc_status === 'rejected' ? 'Document quality is poor' : null,
                    'created_at' => Carbon::now()->subDays(rand(0, 30)),
                ]);
            }
        }

        $this->command->info('Sample data created successfully!');
        $this->command->info('Created ' . count($createdUsers) . ' users');
        $this->command->info('Created 50 transactions');
        $this->command->info('Created virtual cards for some users');
        $this->command->info('Created deposits for all users');
        $this->command->info('Created loan applications for some users');
        $this->command->info('Created KYC documents for users pending verification');
    }
}

