<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates super admin and test admin users with environment variable support.
     */
    public function run(): void
    {
        // Environment protection - only run in development environments
        if (!app()->environment(['local', 'testing', 'staging'])) {
            $this->command->error('⚠️  WARNING: SuperAdminSeeder is only for development environments!');
            $this->command->error('Current environment: ' . app()->environment());
            
            if (!$this->command->confirm('Do you want to proceed anyway?', false)) {
                $this->command->info('Seeder cancelled.');
                return;
            }
        }
        
        // Safety check: Warn if admin users already exist
        $existingAdmins = User::where('is_admin', true)->count();
        
        if ($existingAdmins > 0 && !app()->environment(['local', 'testing'])) {
            $this->command->warn("⚠️  WARNING: Found {$existingAdmins} existing admin user(s) in database!");
            $this->command->info('This seeder uses firstOrCreate() - it will only create if email does not exist.');
            $this->command->info('Existing users will NOT be deleted or modified unless they are missing required fields.');
        }
        
        // Get credentials from environment variables with fallback defaults
        // Use safe defaults first, then try to override with env() if available
        // This prevents issues if .env file has syntax errors
        $superAdminEmail = 'kelvin@newwaves.com';
        $superAdminPassword = 'NewWaves2024!';
        $superAdminName = 'Kelvin NewWaves';
        
        $testAdminEmail = 'admin@hospital.com';
        $testAdminPassword = 'admin123';
        $testAdminName = 'Hospital Admin';
        
        // Try to override with environment variables if they exist and .env is parseable
        // We use a try-catch to handle cases where .env has syntax errors
        try {
            if (function_exists('env')) {
                $envSuperEmail = env('SUPER_ADMIN_EMAIL');
                $envSuperPassword = env('SUPER_ADMIN_PASSWORD');
                $envSuperName = env('SUPER_ADMIN_NAME');
                
                $envTestEmail = env('TEST_ADMIN_EMAIL');
                $envTestPassword = env('TEST_ADMIN_PASSWORD');
                $envTestName = env('TEST_ADMIN_NAME');
                
                // Only use env values if they're not null/empty (env() returns null on failure)
                if (!empty($envSuperEmail)) $superAdminEmail = $envSuperEmail;
                if (!empty($envSuperPassword)) $superAdminPassword = $envSuperPassword;
                if (!empty($envSuperName)) $superAdminName = $envSuperName;
                
                if (!empty($envTestEmail)) $testAdminEmail = $envTestEmail;
                if (!empty($envTestPassword)) $testAdminPassword = $envTestPassword;
                if (!empty($envTestName)) $testAdminName = $envTestName;
            }
        } catch (\Exception $e) {
            // If env() fails, just use defaults (already set above)
            $this->command->warn('⚠️  Note: Could not read environment variables, using defaults. This is okay if .env has issues.');
        }
        
        $this->command->info('Seeding Super Admin users...');
        
        // Create/Update Super Admin using firstOrCreate pattern
        $superAdmin = User::firstOrCreate(
            ['email' => $superAdminEmail],
            [
                'name' => $superAdminName,
                'password' => Hash::make($superAdminPassword),
                'role' => 'admin',
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        
        // Update missing required fields if user already existed
        $needsUpdate = false;
        $updateData = [];
        
        if (!$superAdmin->role || $superAdmin->role !== 'admin') {
            $updateData['role'] = 'admin';
            $needsUpdate = true;
        }
        
        if (!isset($superAdmin->is_active) || !$superAdmin->is_active) {
            $updateData['is_active'] = true;
            $needsUpdate = true;
        }
        
        if (!$superAdmin->is_admin) {
            $updateData['is_admin'] = true;
            $needsUpdate = true;
        }
        
        if (!$superAdmin->email_verified_at) {
            $updateData['email_verified_at'] = now();
            $needsUpdate = true;
        }
        
        // Only update password if user was just created (to avoid overwriting existing passwords)
        if ($superAdmin->wasRecentlyCreated) {
            // User was just created, password is already set
            $this->command->info("✅ Created Super Admin: {$superAdminEmail}");
        } else {
            // User already exists - only update missing fields, not password
            if ($needsUpdate) {
                $superAdmin->update($updateData);
                $this->command->info("✅ Updated Super Admin: {$superAdminEmail} (missing fields added)");
            } else {
                $this->command->info("ℹ️  Super Admin already exists: {$superAdminEmail}");
            }
        }
        
        // Assign role if role system is available
        if (method_exists($superAdmin, 'assignRole')) {
            try {
                $superAdmin->assignRole('admin');
            } catch (\Exception $e) {
                // Role system not available, ignore
            }
        }
        
        // Create/Update Test Admin using firstOrCreate pattern
        $testAdmin = User::firstOrCreate(
            ['email' => $testAdminEmail],
            [
                'name' => $testAdminName,
                'password' => Hash::make($testAdminPassword),
                'role' => 'admin',
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        
        // Update missing required fields if user already existed
        $needsUpdate = false;
        $updateData = [];
        
        if (!$testAdmin->role || $testAdmin->role !== 'admin') {
            $updateData['role'] = 'admin';
            $needsUpdate = true;
        }
        
        if (!isset($testAdmin->is_active) || !$testAdmin->is_active) {
            $updateData['is_active'] = true;
            $needsUpdate = true;
        }
        
        if (!$testAdmin->is_admin) {
            $updateData['is_admin'] = true;
            $needsUpdate = true;
        }
        
        if (!$testAdmin->email_verified_at) {
            $updateData['email_verified_at'] = now();
            $needsUpdate = true;
        }
        
        // Only update password if user was just created
        if ($testAdmin->wasRecentlyCreated) {
            // User was just created, password is already set
            $this->command->info("✅ Created Test Admin: {$testAdminEmail}");
        } else {
            // User already exists - only update missing fields, not password
            if ($needsUpdate) {
                $testAdmin->update($updateData);
                $this->command->info("✅ Updated Test Admin: {$testAdminEmail} (missing fields added)");
            } else {
                $this->command->info("ℹ️  Test Admin already exists: {$testAdminEmail}");
            }
        }
        
        // Assign role if role system is available
        if (method_exists($testAdmin, 'assignRole')) {
            try {
                $testAdmin->assignRole('admin');
            } catch (\Exception $e) {
                // Role system not available, ignore
            }
        }
        
        $this->command->info('');
        $this->command->info('==============================================');
        $this->command->info('Super Admin users processed successfully!');
        $this->command->info('==============================================');
        
        // Only show credentials in local/testing environments for security
        if (app()->environment(['local', 'testing'])) {
            $this->command->info('Super Admin Credentials:');
            $this->command->info("  Email: {$superAdminEmail}");
            $this->command->info("  Password: {$superAdminPassword}");
            $this->command->info('');
            $this->command->info('Test Admin Credentials:');
            $this->command->info("  Email: {$testAdminEmail}");
            $this->command->info("  Password: {$testAdminPassword}");
            $this->command->info('');
            $this->command->warn('⚠️  Note: Change these passwords in production!');
            $this->command->info('    Use environment variables to customize credentials:');
            $this->command->info('    SUPER_ADMIN_EMAIL, SUPER_ADMIN_PASSWORD, etc.');
        } else {
            $this->command->info('Credentials configured via environment variables.');
            $this->command->warn('⚠️  Passwords not shown for security reasons.');
        }
        
        $this->command->info('==============================================');
    }
}
