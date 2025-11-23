<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@newwavesbank.com',
            'username' => 'superadmin',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
            'permissions' => [
                'manage_users',
                'manage_transactions', 
                'manage_deposits',
                'manage_loans',
                'manage_kyc',
                'manage_cards',
                'manage_email',
                'manage_sms',
                'manage_frontend',
                'manage_seo',
                'manage_settings',
                'manage_admins'
            ]
        ]);

        // Create regular admin
        Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
            'permissions' => [
                'manage_users',
                'manage_transactions',
                'manage_deposits',
                'manage_loans',
                'manage_kyc',
                'manage_cards'
            ]
        ]);
    }
}
