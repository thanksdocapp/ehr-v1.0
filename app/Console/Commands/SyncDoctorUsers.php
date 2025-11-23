<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Support\Str;

class SyncDoctorUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'doctors:sync-users {--force : Force sync even if doctor record exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users with role doctor to create corresponding doctor records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to sync doctor users...');
        
        // Get all users with role 'doctor'
        $doctorUsers = User::where('role', 'doctor')->get();
        
        if ($doctorUsers->isEmpty()) {
            $this->warn('No users with role "doctor" found.');
            return;
        }
        
        $this->info("Found {$doctorUsers->count()} doctor users.");
        
        $created = 0;
        $updated = 0;
        $skipped = 0;
        
        foreach ($doctorUsers as $user) {
            // Check if doctor record already exists
            $doctor = Doctor::where('user_id', $user->id)->first();
            
            if ($doctor && !$this->option('force')) {
                $this->line("Skipping {$user->name} - Doctor record already exists");
                $skipped++;
                continue;
            }
            
            // Parse name parts
            $nameParts = explode(' ', trim($user->name));
            $firstName = $nameParts[0] ?? 'Doctor';
            $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : $firstName;
            
            // Get default department or use user's department
            $departmentId = $user->department_id;
            if (!$departmentId) {
                $defaultDepartment = Department::first();
                $departmentId = $defaultDepartment ? $defaultDepartment->id : null;
            }
            
            if (!$departmentId) {
                $this->error("Cannot create doctor record for {$user->name} - No department available");
                continue;
            }
            
            $doctorData = [
                'user_id' => $user->id,
                'title' => 'Dr.',
                'first_name' => $firstName,
                'last_name' => $lastName,
                'slug' => Str::slug($user->name . '-' . $user->id),
                'specialization' => $user->specialization ?? 'GP',
                'department_id' => $departmentId,
                'bio' => $user->bio ?? 'Experienced medical professional dedicated to providing quality healthcare.',
                'qualification' => 'MBBS',
                'experience_years' => 5,
                'email' => $user->email,
                'phone' => $user->phone,
                'consultation_fee' => 100.00,
                'is_active' => $user->is_active,
                'status' => $user->is_active ? 'active' : 'inactive'
            ];
            
            if ($doctor) {
                // Update existing record
                $doctor->update($doctorData);
                $this->info("Updated doctor record for {$user->name}");
                $updated++;
            } else {
                // Create new record
                Doctor::create($doctorData);
                $this->info("Created doctor record for {$user->name}");
                $created++;
            }
        }
        
        $this->info("\nSync completed!");
        $this->table(
            ['Action', 'Count'],
            [
                ['Created', $created],
                ['Updated', $updated],
                ['Skipped', $skipped],
                ['Total', $doctorUsers->count()]
            ]
        );
    }
}
