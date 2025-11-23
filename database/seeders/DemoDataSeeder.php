<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Transaction;
use App\Models\UserDeposit;
use App\Models\LoanApplication;
use App\Models\VirtualCard;
use App\Models\KycDocument;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Create demo users
        for ($i = 1; $i <= 20; $i++) {
            $user = User::create([
                'name' => $faker->firstName() . ' ' . $faker->lastName(),
                'first_name' => $faker->firstName(),
                'middle_name' => $faker->optional()->firstName(),
                'last_name' => $faker->lastName(),
                'username' => $faker->unique()->userName(),
                'email' => $faker->unique()->email(),
                'country_code' => $faker->randomElement(['+1', '+44', '+33', '+49', '+81', '+86']),
                'phone' => $faker->phoneNumber(),
                'account_type' => $faker->randomElement(['personal', 'business']),
                'password' => Hash::make('password123'),
                'pin_code' => Hash::make('1234'),
                'balance' => $faker->randomFloat(2, 100, 50000),
                'status' => $faker->randomElement(['pending', 'active', 'active', 'active']), // More actives
                'kyc_verified' => $faker->boolean(70), // 70% verified
                'email_verified_at' => now(),
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
            ]);

            // Create transactions for each user
            for ($j = 1; $j <= rand(1, 8); $j++) {
                $amount = $faker->randomFloat(2, 10, 5000);
                $fee = $faker->randomFloat(2, 0, 50);
                
                Transaction::create([
                    'transaction_id' => 'TXN' . strtoupper($faker->bothify('??##??##')),
                    'user_id' => $user->id,
                    'type' => $faker->randomElement(['deposit', 'withdrawal', 'transfer_send', 'transfer_receive']),
                    'amount' => $amount,
                    'fee' => $fee,
                    'final_amount' => $amount - $fee,
                    'currency' => 'USD',
                    'status' => $faker->randomElement(['pending', 'completed', 'completed', 'completed']), // More completed
                    'description' => $faker->sentence(),
                    'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                ]);
            }

            // Create deposits
            if (rand(1, 3) == 1) { // 33% chance
                UserDeposit::create([
                    'user_id' => $user->id,
                    'deposit_id' => 'DEP' . strtoupper($faker->bothify('??##??##')),
                    'amount' => $faker->randomFloat(2, 100, 10000),
                    'currency' => 'USD',
                    'method' => $faker->randomElement(['bank_transfer', 'credit_card', 'debit_card', 'paypal']),
                    'status' => $faker->randomElement(['pending', 'completed', 'completed']),
                    'reference_number' => $faker->uuid(),
                    'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                ]);
            }

            // Create loan applications
            if (rand(1, 4) == 1) { // 25% chance
                LoanApplication::create([
                    'user_id' => $user->id,
                    'application_id' => 'LOAN' . strtoupper($faker->bothify('??##??##')),
                    'requested_amount' => $faker->randomFloat(2, 1000, 100000),
                    'loan_type' => $faker->randomElement(['personal', 'business', 'auto', 'home']),
                    'term_months' => $faker->randomElement([12, 24, 36, 48, 60]),
                    'interest_rate' => $faker->randomFloat(2, 3, 15),
                    'status' => $faker->randomElement(['pending', 'under_review', 'approved', 'rejected']),
                    'purpose' => $faker->sentence(),
                    'monthly_income' => $faker->randomFloat(2, 2000, 15000),
                    'employment_status' => $faker->randomElement(['employed', 'self_employed', 'unemployed']),
                    'employer_name' => $faker->optional()->company(),
                    'created_at' => $faker->dateTimeBetween('-2 months', 'now'),
                ]);
            }

            // Create virtual cards
            if (rand(1, 3) == 1) { // 33% chance
                VirtualCard::create([
                    'user_id' => $user->id,
                    'card_number' => $faker->creditCardNumber('Visa'),
                    'card_holder_name' => $user->name,
                    'expiry_month' => $faker->month(),
                    'expiry_year' => $faker->year('+5 years'),
                    'cvv' => $faker->randomNumber(3, true),
                    'card_type' => $faker->randomElement(['visa', 'mastercard']),
                    'balance' => $faker->randomFloat(2, 0, 2000),
                    'spending_limit' => $faker->randomFloat(2, 1000, 10000),
                    'status' => $faker->randomElement(['active', 'active', 'blocked']), // More active
                    'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                ]);
            }

            // Create KYC documents
            if (rand(1, 2) == 1) { // 50% chance
                KycDocument::create([
                    'user_id' => $user->id,
                    'document_type' => $faker->randomElement(['passport', 'driver_license', 'national_id', 'utility_bill']),
                    'document_number' => $faker->optional()->regexify('[A-Z0-9]{8,12}'),
                    'file_path' => 'kyc/' . $faker->uuid() . '.jpg',
                    'original_filename' => $faker->word() . '.jpg',
                    'mime_type' => 'image/jpeg',
                    'file_size' => $faker->numberBetween(100000, 2000000),
                    'status' => $faker->randomElement(['pending', 'approved', 'approved', 'rejected']), // More approved
                    'expiry_date' => $faker->optional()->dateTimeBetween('now', '+10 years'),
                    'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                ]);
            }
        }

        $this->command->info('Demo data seeded successfully!');
    }
}
