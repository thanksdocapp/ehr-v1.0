<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Super Admin Seeder - Creates essential admin users
        // This should run in all environments (with environment protection built-in)
        $this->call([
            SuperAdminSeeder::class,
        ]);
        
        // Patient Portal Test Data Seeders (Local Environment Only)
        // These seeders will only run in local environment and provide
        // comprehensive test data for the patient portal functionality
        if (app()->environment('local')) {
            // Safety check: Warn before seeding
            $this->command->info('⚠️  Safety Check: Testing for existing data...');
            
            try {
                $hasData = \App\Services\DatabaseSafetyService::hasData();
                if ($hasData) {
                    $stats = \App\Services\DatabaseSafetyService::getDatabaseStats();
                    $this->command->warn('Found existing data in database:');
                    foreach ($stats as $label => $count) {
                        $this->command->line("  - {$label}: {$count}");
                    }
                    $this->command->info('');
                    $this->command->info('Note: Seeders are designed to be safe - they will:');
                    $this->command->info('  - Skip if data already exists (TestPatientSeeder)');
                    $this->command->info('  - Use firstOrCreate() to avoid duplicates (TestUsersSeeder)');
                    $this->command->info('  - NOT delete or modify existing data');
                    $this->command->info('');
                } else {
                    $this->command->info('✅ No existing data found - safe to seed.');
                    $this->command->info('');
                }
            } catch (\Exception $e) {
                $this->command->warn('Could not check for existing data: ' . $e->getMessage());
            }

            $this->command->info('Seeding patient portal test data...');
            
            $this->call([
                TestPatientSeeder::class,        // Create test patients first
                MedicalRecordSeeder::class,
                PrescriptionSeeder::class,
                LabReportSeeder::class,
                InvoiceSeeder::class,
                PaymentSeeder::class,
                HospitalSeoSeeder::class,        // SEO pages for hospital
                SeoSettingsSeeder::class,        // SEO settings configuration
                EmailTemplateSeeder::class,      // Email templates for notifications
                SmsTemplateSeeder::class,        // SMS templates for notifications
            ]);
            
            $this->command->info('Patient portal test data seeded successfully!');
            $this->command->warn('Note: This test data can be removed with "php artisan migrate:fresh" when ready for production.');
        }
        
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
