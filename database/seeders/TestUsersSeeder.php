<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Safety check: Warn if users already exist (except in local/test)
        $existingUsers = User::count();
        
        if ($existingUsers > 0 && !app()->environment(['local', 'testing'])) {
            $this->command->warn("⚠️  WARNING: Found {$existingUsers} existing user(s) in database!");
            $this->command->info('This seeder uses firstOrCreate() - it will only create if email does not exist.');
            $this->command->info('Existing users will NOT be deleted or modified.');
        }

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@hospital.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
                'phone' => '+1234567890',
                'role' => 'admin',
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (method_exists($admin, 'assignRole')) {
            try {
                $admin->assignRole('admin');
            } catch (\Exception $e) {
                // Role system not available, ignore
            }
        }

        // Create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@hospital.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'phone' => '+1234567891',
                'role' => 'admin',
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (method_exists($superAdmin, 'assignRole')) {
            try {
                $superAdmin->assignRole('super_admin');
            } catch (\Exception $e) {
                // Role system not available, ignore
            }
        }

        // Create doctor user
        $doctor = User::firstOrCreate(
            ['email' => 'doctor@hospital.com'],
            [
                'name' => 'Dr. John Smith',
                'password' => Hash::make('password123'),
                'phone' => '+1234567892',
                'role' => 'doctor',
                'is_admin' => false,
                'is_active' => true,
                'specialization' => 'Cardiology',
                'email_verified_at' => now(),
            ]
        );
        if (method_exists($doctor, 'assignRole')) {
            try {
                $doctor->assignRole('doctor');
            } catch (\Exception $e) {
                // Role system not available, ignore
            }
        }

        // Create nurse user
        $nurse = User::firstOrCreate(
            ['email' => 'nurse@hospital.com'],
            [
                'name' => 'Jane Doe',
                'password' => Hash::make('password123'),
                'phone' => '+1234567893',
                'role' => 'nurse',
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (method_exists($nurse, 'assignRole')) {
            try {
                $nurse->assignRole('nurse');
            } catch (\Exception $e) {
                // Role system not available, ignore
            }
        }

        // Create receptionist user
        $receptionist = User::firstOrCreate(
            ['email' => 'receptionist@hospital.com'],
            [
                'name' => 'Alice Johnson',
                'password' => Hash::make('password123'),
                'phone' => '+1234567894',
                'role' => 'receptionist',
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (method_exists($receptionist, 'assignRole')) {
            try {
                $receptionist->assignRole('receptionist');
            } catch (\Exception $e) {
                // Role system not available, ignore
            }
        }

        $this->command->info('Test users created successfully!');
        $this->command->info('Admin: admin@hospital.com / password123');
        $this->command->info('Super Admin: superadmin@hospital.com / password123');
        $this->command->info('Doctor: doctor@hospital.com / password123');
        $this->command->info('Nurse: nurse@hospital.com / password123');
        $this->command->info('Receptionist: receptionist@hospital.com / password123');
    }
}
