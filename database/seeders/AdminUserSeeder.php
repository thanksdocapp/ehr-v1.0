<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@hospital.com',
            'password' => Hash::make('password'),
            'is_admin' => 1,
            'email_verified_at' => now(),
        ]);
        
        $this->command->info('Admin user created: email=admin@hospital.com, password=password');
    }
}
