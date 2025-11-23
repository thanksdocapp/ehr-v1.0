<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Transaction;
use App\Models\VirtualCard;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class QuickTestSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample users that match existing table structure
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
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $createdUsers[] = User::create($userData);
        }

        // Create sample transactions
        $transactionTypes = ['deposit', 'withdrawal', 'transfer_send', 'transfer_receive', 'card_payment'];
        $statuses = ['pending', 'completed', 'failed'];

        for ($i = 0; $i < 20; $i++) {
            $user = $createdUsers[array_rand($createdUsers)];
            $type = $transactionTypes[array_rand($transactionTypes)];
            $status = $statuses[array_rand($statuses)];
            $amount = rand(10, 1000) + (rand(0, 99) / 100);
            $fee = $amount * 0.01;
            $finalAmount = $type === 'deposit' ? $amount + $fee : $amount - $fee;

            Transaction::create([
                'transaction_id' => 'TXN' . strtoupper(uniqid()),
                'user_id' => $user->id,
                'recipient_user_id' => in_array($type, ['transfer_send', 'transfer_receive']) ? $createdUsers[array_rand($createdUsers)]->id : null,
                'type' => $type,
                'amount' => $amount,
                'fee' => $fee,
                'final_amount' => $finalAmount,
                'currency' => 'USD',
                'description' => ucfirst(str_replace('_', ' ', $type)) . ' transaction #' . ($i + 1),
                'status' => $status,
                'reference' => 'REF' . time() . $i,
                'payment_method' => $type === 'deposit' ? 'bank_transfer' : null,
                'created_at' => Carbon::now()->subDays(rand(0, 7)),
            ]);
        }

        $this->command->info('Quick test data created successfully!');
        $this->command->info('Created ' . count($createdUsers) . ' users and 20 transactions');
    }
}

