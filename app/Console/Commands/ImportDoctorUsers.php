<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Department;

class ImportDoctorUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:doctor-users 
                            {file : Path to CSV file with user data}
                            {--doctors-file= : Path to CSV file with doctor data}
                            {--skip-duplicates : Skip users that already exist (by email)}
                            {--dry-run : Show what would be imported without actually importing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import doctor users and their doctor records from old system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $doctorsFile = $this->option('doctors-file');
        $skipDuplicates = $this->option('skip-duplicates');
        $dryRun = $this->option('dry-run');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info('Starting import process...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be imported');
        }

        // Read users CSV
        $users = $this->readCsv($file);
        $this->info("Found " . count($users) . " users to import");

        // Read doctors CSV if provided
        $doctors = [];
        if ($doctorsFile && file_exists($doctorsFile)) {
            $doctors = $this->readCsv($doctorsFile);
            $this->info("Found " . count($doctors) . " doctors to import");
        }

        // Import users
        $importedUsers = [];
        $skippedUsers = [];
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($users as $index => $userData) {
                $rowNumber = $index + 2; // +2 because CSV has header and 0-indexed

                // Validate required fields
                if (empty($userData['email'])) {
                    $errors[] = "Row {$rowNumber}: Missing email";
                    continue;
                }

                // Check for duplicates (by email or employee_id)
                $existingUser = User::where('email', $userData['email'])->first();
                if (!$existingUser && !empty($userData['employee_id'])) {
                    $existingUser = User::where('employee_id', $userData['employee_id'])->first();
                }
                
                if ($existingUser) {
                    if ($skipDuplicates) {
                        $skippedUsers[] = $userData['email'];
                        $this->warn("Skipping duplicate: {$userData['email']} (email or employee_id already exists)");
                        continue;
                    } else {
                        $errors[] = "Row {$rowNumber}: User already exists: {$userData['email']} (email or employee_id duplicate)";
                        continue;
                    }
                }

                // Validate password hash
                if (empty($userData['password'])) {
                    $errors[] = "Row {$rowNumber}: Missing password";
                    continue;
                }

                // Check if password is already hashed (bcrypt starts with $2y$)
                $password = $userData['password'];
                if (!str_starts_with($password, '$2y$') && !str_starts_with($password, '$2a$') && !str_starts_with($password, '$2b$')) {
                    // Password is not hashed, hash it
                    $password = Hash::make($password);
                    $this->warn("Row {$rowNumber}: Password was not hashed, hashing it now");
                }

                // Map department ID if needed
                $departmentId = null;
                if (!empty($userData['department_id']) || !empty($userData['old_department_id'])) {
                    $oldDeptId = $userData['department_id'] ?? $userData['old_department_id'];
                    // You may need to map old department IDs to new ones
                    $departmentId = $this->mapDepartmentId($oldDeptId);
                    
                    // Verify department exists, if not set to null
                    if ($departmentId && !\App\Models\Department::find($departmentId)) {
                        $this->warn("Row {$rowNumber}: Department ID {$departmentId} does not exist, setting to NULL");
                        $departmentId = null;
                    }
                }

                // Prepare user data
                $userDataToInsert = [
                    'name' => $userData['name'] ?? 'Unknown',
                    'email' => $userData['email'],
                    'password' => $password,
                    'phone' => $userData['phone'] ?? null,
                    'role' => 'doctor', // Force role to doctor
                    'is_admin' => isset($userData['is_admin']) ? (bool)$userData['is_admin'] : false,
                    'is_active' => isset($userData['is_active']) ? (bool)$userData['is_active'] : true,
                    'department_id' => $departmentId,
                    'avatar' => $userData['avatar'] ?? null,
                    'bio' => $userData['bio'] ?? null,
                    'specialization' => $userData['specialization'] ?? null,
                    'employee_id' => $userData['employee_id'] ?? null,
                    'hire_date' => $this->parseHireDate($userData['hire_date'] ?? null),
                    'last_login_at' => !empty($userData['last_login_at']) ? $userData['last_login_at'] : null,
                    'email_verified_at' => !empty($userData['email_verified_at']) ? $userData['email_verified_at'] : now(),
                    'remember_token' => $userData['remember_token'] ?? null,
                    'created_at' => !empty($userData['created_at']) ? $userData['created_at'] : now(),
                    'updated_at' => !empty($userData['updated_at']) ? $userData['updated_at'] : now(),
                    'password_change_required' => $this->parsePasswordChangeRequired($userData['password_change_required'] ?? '0'),
                    'two_factor_enabled' => isset($userData['two_factor_enabled']) ? (bool)$userData['two_factor_enabled'] : false,
                    'two_factor_secret' => $userData['two_factor_secret'] ?? null,
                    'two_factor_recovery_codes' => $userData['two_factor_recovery_codes'] ?? null,
                    'two_factor_confirmed_at' => !empty($userData['two_factor_confirmed_at']) ? $userData['two_factor_confirmed_at'] : null,
                ];

                if ($dryRun) {
                    $this->line("Would import user: {$userData['email']}");
                    $importedUsers[] = ['email' => $userData['email'], 'old_id' => $userData['old_user_id'] ?? null];
                } else {
                    $user = User::create($userDataToInsert);
                    $importedUsers[] = [
                        'id' => $user->id,
                        'email' => $user->email,
                        'old_id' => $userData['old_user_id'] ?? $userData['id'] ?? null
                    ];
                    $this->info("Imported user: {$user->email} (ID: {$user->id})");
                }
            }

            // Import doctors if file provided
            if (!empty($doctors) && !$dryRun) {
                $this->info("\nImporting doctors...");
                foreach ($doctors as $index => $doctorData) {
                    $rowNumber = $index + 2;

                    // Find corresponding user
                    $oldUserId = $doctorData['old_user_id'] ?? $doctorData['user_id'] ?? null;
                    $userEmail = $doctorData['email'] ?? null;

                    $user = null;
                    if ($oldUserId) {
                        // Find user by old ID mapping
                        $mappedUser = collect($importedUsers)->firstWhere('old_id', $oldUserId);
                        if ($mappedUser) {
                            $user = User::find($mappedUser['id']);
                        }
                    }

                    if (!$user && $userEmail) {
                        $user = User::where('email', $userEmail)->first();
                    }

                    if (!$user) {
                        $errors[] = "Row {$rowNumber}: Could not find user for doctor";
                        continue;
                    }

                    // Map department ID
                    $departmentId = null;
                    if (!empty($doctorData['department_id']) || !empty($doctorData['old_department_id'])) {
                        $oldDeptId = $doctorData['department_id'] ?? $doctorData['old_department_id'];
                        $departmentId = $this->mapDepartmentId($oldDeptId);
                        
                        // Verify department exists, if not set to null
                        if ($departmentId && !\App\Models\Department::find($departmentId)) {
                            $this->warn("Row {$rowNumber}: Department ID {$departmentId} does not exist for doctor, setting to NULL");
                            $departmentId = null;
                        }
                    }

                    // Check if doctor already exists
                    $existingDoctor = Doctor::where('user_id', $user->id)->first();
                    if ($existingDoctor) {
                        $this->warn("Doctor already exists for user: {$user->email}");
                        continue;
                    }

                    // Prepare doctor data
                    $doctorDataToInsert = [
                        'user_id' => $user->id,
                        'title' => $doctorData['title'] ?? 'Dr.',
                        'first_name' => $doctorData['first_name'] ?? explode(' ', $user->name)[0] ?? 'Unknown',
                        'last_name' => $doctorData['last_name'] ?? explode(' ', $user->name)[1] ?? '',
                        'slug' => $doctorData['slug'] ?? \Illuminate\Support\Str::slug($user->name),
                        'specialization' => $doctorData['specialization'] ?? $user->specialization ?? 'General Medicine',
                        'department_id' => $departmentId ?? $user->department_id,
                        'bio' => $doctorData['bio'] ?? $user->bio ?? null,
                        'photo' => $doctorData['photo'] ?? null,
                        'qualification' => $doctorData['qualification'] ?? null,
                        'experience_years' => $doctorData['experience_years'] ?? 0,
                        'languages' => !empty($doctorData['languages']) ? json_decode($doctorData['languages'], true) : null,
                        'specialties' => !empty($doctorData['specialties']) ? json_decode($doctorData['specialties'], true) : null,
                        'email' => $user->email,
                        'phone' => $doctorData['phone'] ?? $user->phone,
                        'room_number' => $doctorData['room_number'] ?? null,
                        'consultation_fee' => $doctorData['consultation_fee'] ?? null,
                        'availability' => !empty($doctorData['availability']) ? json_decode($doctorData['availability'], true) : null,
                        'is_available_online' => isset($doctorData['is_available_online']) ? (bool)$doctorData['is_available_online'] : false,
                        'sort_order' => $doctorData['sort_order'] ?? 0,
                        'is_featured' => isset($doctorData['is_featured']) ? (bool)$doctorData['is_featured'] : false,
                        'is_active' => isset($doctorData['is_active']) ? (bool)$doctorData['is_active'] : true,
                        'created_at' => !empty($doctorData['created_at']) ? $doctorData['created_at'] : now(),
                        'updated_at' => !empty($doctorData['updated_at']) ? $doctorData['updated_at'] : now(),
                    ];

                    $doctor = Doctor::create($doctorDataToInsert);
                    $this->info("Imported doctor: {$doctor->first_name} {$doctor->last_name} (User ID: {$user->id})");
                }
            }

            if ($dryRun) {
                DB::rollBack();
                $this->info("\nDRY RUN completed. No data was imported.");
            } else {
                DB::commit();
                $this->info("\n✅ Import completed successfully!");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error during import: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        // Summary
        $this->info("\n=== Import Summary ===");
        $this->info("Imported: " . count($importedUsers));
        $this->info("Skipped: " . count($skippedUsers));
        $this->info("Errors: " . count($errors));

        if (!empty($errors)) {
            $this->error("\nErrors:");
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
        }

        if (!empty($skippedUsers)) {
            $this->warn("\nSkipped users:");
            foreach ($skippedUsers as $email) {
                $this->warn("  - {$email}");
            }
        }

        return 0;
    }

    /**
     * Read CSV file
     */
    private function readCsv($file)
    {
        $data = [];
        if (($handle = fopen($file, 'r')) !== false) {
            $headers = fgetcsv($handle); // Read header row
            if ($headers === false) {
                fclose($handle);
                return $data;
            }
            
            // Clean headers (remove BOM if present)
            $headers = array_map(function($header) {
                return trim(str_replace("\xEF\xBB\xBF", '', $header));
            }, $headers);
            
            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Ensure row has same number of elements as headers
                if (count($row) !== count($headers)) {
                    // Pad or trim row to match headers
                    if (count($row) < count($headers)) {
                        $row = array_pad($row, count($headers), '');
                    } else {
                        $row = array_slice($row, 0, count($headers));
                    }
                }
                
                $data[] = array_combine($headers, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    /**
     * Map old department ID to new department ID
     * You may need to customize this based on your department mapping
     */
    private function mapDepartmentId($oldId)
    {
        // TODO: Implement your department ID mapping logic
        // For now, just return the old ID (assuming they match)
        // You might need a mapping table or array
        
        // Example mapping:
        // $mapping = [
        //     1 => 5,  // Old dept ID 1 -> New dept ID 5
        //     2 => 6,  // Old dept ID 2 -> New dept ID 6
        // ];
        // return $mapping[$oldId] ?? $oldId;
        
        return $oldId;
    }

    /**
     * Parse hire_date from varchar to date format
     * Handles old system where hire_date was stored as varchar
     */
    private function parseHireDate($hireDate)
    {
        if (empty($hireDate) || $hireDate === 'NULL' || $hireDate === '') {
            return null;
        }

        try {
            // If it's already a date string, try to parse it
            if (is_string($hireDate)) {
                // Remove time portion if present (e.g., '2025-11-02 00:00:00' -> '2025-11-02')
                $hireDate = trim($hireDate);
                if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $hireDate, $matches)) {
                    return $matches[1]; // Return just the date part
                }
                
                // Try Carbon parsing
                return \Carbon\Carbon::parse($hireDate)->format('Y-m-d');
            }
            
            return $hireDate;
        } catch (\Exception $e) {
            $this->warn("Could not parse hire_date: {$hireDate}");
            return null;
        }
    }

    /**
     * Parse password_change_required from string to boolean
     * Old system stored as '0'/'1' string, new system uses boolean
     */
    private function parsePasswordChangeRequired($value)
    {
        if (empty($value)) {
            return false;
        }

        // Handle string values
        if (is_string($value)) {
            $value = trim(strtolower($value));
            return in_array($value, ['1', 'true', 'yes', 'on']);
        }

        // Handle numeric values
        return (bool)$value;
    }
}

