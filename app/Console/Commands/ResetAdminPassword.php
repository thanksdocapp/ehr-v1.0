<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ResetAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset-password 
                            {email? : The email of the admin user}
                            {--password= : The new password (if not provided, will use default: admin123)}
                            {--create : Create a new admin if none exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset admin user password or create a new admin account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->option('password') ?: 'admin123';
        $create = $this->option('create');

        // If no email provided, list existing admins
        if (!$email) {
            $this->info('Existing Admin Users:');
            
            // Check which columns exist
            $hasRole = DB::getSchemaBuilder()->hasColumn('users', 'role');
            $hasIsAdmin = DB::getSchemaBuilder()->hasColumn('users', 'is_admin');
            
            $query = User::query();
            if ($hasRole) {
                $query->where('role', 'admin');
            }
            if ($hasIsAdmin) {
                if ($hasRole) {
                    $query->orWhere('is_admin', true);
                } else {
                    $query->where('is_admin', true);
                }
            }
            
            $admins = $query->get(['id', 'name', 'email']);
            
            if ($admins->isEmpty()) {
                $this->warn('No admin users found.');
                if ($create) {
                    $this->createAdmin('admin@hospital.com', $password);
                } else {
                    $this->info('Run with --create flag to create a new admin: php artisan admin:reset-password admin@hospital.com --create');
                }
                return 0;
            }

            foreach ($admins as $admin) {
                $this->line("  - {$admin->email} ({$admin->name})");
            }
            
            $this->newLine();
            $this->info('To reset password, run:');
            $this->line('  php artisan admin:reset-password <email>');
            return 0;
        }

        // Find or create admin user
        $user = User::where('email', $email)->first();

        if (!$user) {
            if ($create) {
                $this->createAdmin($email, $password);
            } else {
                $this->error("User with email '{$email}' not found.");
                $this->info('Use --create flag to create a new admin: php artisan admin:reset-password ' . $email . ' --create');
                return 1;
            }
        } else {
            // Reset password for existing user
            $user->password = Hash::make($password);
            
            // Set role if column exists
            if (DB::getSchemaBuilder()->hasColumn('users', 'role')) {
                $user->role = 'admin';
            }
            
            // Ensure is_admin is set if column exists
            if (DB::getSchemaBuilder()->hasColumn('users', 'is_admin')) {
                $user->is_admin = true;
            }
            
            // Set is_active if column exists
            if (DB::getSchemaBuilder()->hasColumn('users', 'is_active')) {
                $user->is_active = true;
            }
            
            $user->email_verified_at = now();
            $user->save();

            $this->info("✅ Password reset successfully for: {$email}");
            $this->newLine();
            $this->info('Login Credentials:');
            $this->line("  Email: {$email}");
            $this->line("  Password: {$password}");
        }

        return 0;
    }

    /**
     * Create a new admin user
     */
    private function createAdmin(string $email, string $password)
    {
        $userData = [
            'name' => 'Admin User',
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ];

        // Add role if column exists
        if (DB::getSchemaBuilder()->hasColumn('users', 'role')) {
            $userData['role'] = 'admin';
        }

        // Add is_admin if column exists
        if (DB::getSchemaBuilder()->hasColumn('users', 'is_admin')) {
            $userData['is_admin'] = true;
        }

        // Add is_active if column exists
        if (DB::getSchemaBuilder()->hasColumn('users', 'is_active')) {
            $userData['is_active'] = true;
        }

        User::create($userData);

        $this->info("✅ Admin user created successfully!");
        $this->newLine();
        $this->info('Login Credentials:');
        $this->line("  Email: {$email}");
        $this->line("  Password: {$password}");
    }
}
