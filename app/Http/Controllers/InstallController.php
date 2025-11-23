<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Admin;
use App\Services\EnvatoLicenseService;
use Exception;

class InstallController extends Controller
{
    /**
     * Get installation steps configuration
     */
    private function getInstallationSteps(): array
    {
        return [
            'welcome' => 'Welcome to ' . (function_exists('getAppName') ? getAppName() : 'Hospital System'),
            'requirements' => 'System Requirements',
            'database' => 'Database Configuration',
            'admin' => 'Administrator Setup',
            'final' => 'Installation Complete'
        ];
    }

    /**
     * Get installation product info
     */
    private function getProductInfo(): array
    {
        return [
            'name' => function_exists('getAppName') ? getAppName() : config('app.name', 'Hospital System'),
            'version' => function_exists('getAppVersion') ? getAppVersion('1.0.0') : '1.0.0',
            'author' => function_exists('getCompanyName') ? getCompanyName('Your Company') : 'Your Company',
            'envato_item_id' => 'YOUR_ENVATO_ITEM_ID', // Will be set when published
            'min_php_version' => '8.1.0'
        ];
    }

    /**
     * Show CodeCanyon installation welcome page
     */
    public function index()
    {
        // Check if already installed
        if ($this->isInstalled()) {
            return redirect()->route('homepage')->with('error', getAppName() . ' is already installed.');
        }

        // Auto-fix APP_URL if it's still the placeholder
        $this->autoFixAppUrl();

        // Auto-check and fix permissions on start
        $this->autoFixPermissions();

        return view('install.welcome', [
            'title' => $this->getProductInfo()['name'] . ' Installation',
            'productInfo' => $this->getProductInfo(),
            'steps' => $this->getInstallationSteps(),
            'currentStep' => 'welcome'
        ]);
    }

    /**
     * Show installation step
     */
    public function step($step)
    {
        // Allow access to final step even when installed (for completion page)
        if ($this->isInstalled() && $step !== 'final') {
            return redirect()->route('homepage')->with('error', getAppName() . ' is already installed.');
        }

        if (!array_key_exists($step, $this->getInstallationSteps())) {
            return redirect()->route('install.index');
        }

        $method = 'step' . ucfirst($step);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return redirect()->route('install.index');
    }

    /**
     * Process installation step
     */
    public function process(Request $request, $step)
    {
        if ($this->isInstalled()) {
            return response()->json(['success' => false, 'message' => 'System already installed']);
        }

        $method = 'process' . ucfirst($step);
        if (method_exists($this, $method)) {
            return $this->$method($request);
        }

        return response()->json(['success' => false, 'message' => 'Invalid installation step']);
    }

    /**
     * License verification step (CodeCanyon) - Removed for simplified installation
     * Can be re-enabled later when license verification is needed
     */
    protected function stepLicense()
    {
        // Redirect to requirements step instead
        return redirect()->route('install.step', 'requirements');
    }

    /**
     * Requirements check step with auto-fixes
     */
    protected function stepRequirements()
    {
        $requirements = $this->checkRequirements();
        $permissions = $this->checkAndFixPermissions();
        
        $allRequirementsPassed = !in_array(false, array_column($requirements, 'status'));
        $allPermissionsPassed = !in_array(false, array_column($permissions, 'status'));

        return view('install.requirements', [
            'requirements' => $requirements,
            'permissions' => $permissions,
            'allRequirementsPassed' => $allRequirementsPassed,
            'allPermissionsPassed' => $allPermissionsPassed,
            'allPassed' => $allRequirementsPassed && $allPermissionsPassed,
            'step' => 'requirements',
            'steps' => $this->installationSteps,
            'productInfo' => $this->productInfo,
            'currentStep' => 'requirements'
        ]);
    }

    /**
     * Database configuration step
     */
    protected function stepDatabase()
    {
        return view('install.database', [
            'step' => 'database',
            'steps' => $this->installationSteps,
            'productInfo' => $this->productInfo,
            'currentStep' => 'database'
        ]);
    }

    /**
     * Admin account creation step
     */
    protected function stepAdmin()
    {
        return view('install.admin', [
            'step' => 'admin',
            'steps' => $this->installationSteps,
            'productInfo' => $this->productInfo,
            'currentStep' => 'admin'
        ]);
    }

    /**
     * Installation finalization step
     */
    protected function stepFinal()
    {
        return view('install.final', [
            'step' => 'final',
            'steps' => $this->installationSteps,
            'productInfo' => $this->productInfo,
            'currentStep' => 'final'
        ]);
    }

    /**
     * Process license verification (CodeCanyon) - Disabled for simplified installation
     * This feature can be re-enabled later when license verification is needed
     */
    protected function processLicense(Request $request)
    {
        // Skip license verification and proceed directly to requirements
        return response()->json([
            'success' => true,
            'message' => 'Installation proceeding...',
            'next_step' => 'requirements'
        ]);
    }

    /**
     * Process environment configuration
     */
    protected function processEnvironment(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'app_env' => 'required|in:local,production',
            'app_debug' => 'sometimes|boolean',
            'log_level' => 'required|in:debug,info,notice,warning,error,critical,alert,emergency'
        ]);
        
        // Handle checkbox - if not present, default to false
        $request->merge([
            'app_debug' => $request->has('app_debug') ? (bool) $request->app_debug : false
        ]);

        try {
            $envContent = $this->generateEnvContent($request->all());
            File::put(base_path('.env'), $envContent);

            // Generate application key
            Artisan::call('key:generate', ['--force' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Environment configuration saved successfully',
                'next_step' => 'database'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save environment configuration: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process database configuration
     */
    protected function processDatabase(Request $request)
    {
        if ($request->header('X-Test-Only') === 'true') {
            return $this->testDatabaseConnectionOnly($request);
        }

        $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|integer|min:1|max:65535',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        try {
            // 1. Test connection to fail early if creds are wrong
            $this->testDatabaseConnection($request->all());

            // 2. Update .env file for future requests
            $this->updateEnvFile([
                'DB_CONNECTION' => 'mysql',
                'DB_HOST'       => $request->db_host,
                'DB_PORT'       => $request->db_port,
                'DB_DATABASE'   => $request->db_database,
                'DB_USERNAME'   => $request->db_username,
                'DB_PASSWORD'   => $request->db_password,
            ]);

            // 3. Override current config to use new DB settings immediately
            $dbConfig = config('database.connections.mysql');
            $dbConfig['host'] = $request->db_host;
            $dbConfig['port'] = $request->db_port;
            $dbConfig['database'] = $request->db_database;
            $dbConfig['username'] = $request->db_username;
            $dbConfig['password'] = $request->db_password;
            
            config(['database.connections.mysql' => $dbConfig]);
            DB::purge('mysql'); // Force Laravel to use the new config

            // 4. Import the database. This now uses the correct connection.
            $this->importDatabaseWithProgress();

            // 5. Verify import was successful
            if (!$this->verifyDatabaseImport()) {
                throw new Exception('Database import completed, but verification failed. Key tables are missing.');
            }

            return response()->json([
                'success'   => true,
                'message'   => 'Database configured and imported successfully!',
                'next_step' => 'admin'
            ]);

        } catch (Exception $e) {
            Log::error('Installation database step failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->except(['db_password', '_token'])
            ]);
            
            $userMessage = 'An error occurred: ' . $e->getMessage();
            if (str_contains($e->getMessage(), 'Access denied')) {
                $userMessage = 'Database access denied. Please check the username and password.';
            } elseif (str_contains($e->getMessage(), 'Unknown database')) {
                $userMessage = 'The database was not found. Please ensure it has been created.';
            } elseif (str_contains($e->getMessage(), 'Connection refused')) {
                $userMessage = 'Could not connect to the database server. Please check the host and port.';
            } elseif (str_contains($e->getMessage(), 'No database selected')) {
                $userMessage = 'Connection was successful, but no database was selected. Please check your configuration.';
            }

            return response()->json([
                'success' => false,
                'message' => $userMessage
            ]);
        }
    }

    /**
     * Process admin account creation
     */
    protected function processAdmin(Request $request)
    {
        // First, test database connection
        try {
            DB::connection()->getPdo();
            
            // Check if users table exists (Hospital system uses users table for admins)
            if (!DB::connection()->getSchemaBuilder()->hasTable('users')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database tables not found. Please ensure the database was imported correctly in the previous step.'
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ]);
        }

        $request->validate([
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8|confirmed',
            'admin_phone' => 'nullable|string|max:20'
        ]);

        try {
            // Check for existing admin with same email (Hospital system pattern)
            $existingUser = DB::table('users')
                ->where('email', $request->admin_email)
                ->first();
                
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'A user with this email already exists.'
                ]);
            }

            // Check the structure of the users table for debugging
            $connectionType = DB::connection()->getDriverName();
            
            if ($connectionType === 'mysql') {
                $tableColumns = DB::select("DESCRIBE users");
                $columnNames = array_column($tableColumns, 'Field');
            } else if ($connectionType === 'sqlite') {
                $tableColumns = DB::select("PRAGMA table_info(users)");
                $columnNames = array_column($tableColumns, 'name');
            } else {
                // Fallback: assume basic columns exist
                $columnNames = ['id', 'name', 'email', 'password', 'role', 'is_admin', 'is_active', 'created_at', 'updated_at'];
            }
            
            // Create admin user data for Hospital Management System
            $userData = [
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'admin', // Set role as admin for proper access control
                'is_admin' => 1, // Hospital system admin flag
                'is_active' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Add required fields with defaults if they exist in the table
            if (in_array('phone', $columnNames)) {
                $userData['phone'] = $request->admin_phone;
            }
            
            if (in_array('two_factor_enabled', $columnNames)) {
                $userData['two_factor_enabled'] = 0;
            }
            
            if (in_array('two_factor_secret', $columnNames)) {
                $userData['two_factor_secret'] = null;
            }
            
            if (in_array('two_factor_recovery_codes', $columnNames)) {
                $userData['two_factor_recovery_codes'] = null;
            }
            
            if (in_array('two_factor_confirmed_at', $columnNames)) {
                $userData['two_factor_confirmed_at'] = null;
            }
            
            if (in_array('bio', $columnNames)) {
                $userData['bio'] = null;
            }
            
            if (in_array('specialization', $columnNames)) {
                $userData['specialization'] = null;
            }
            
            if (in_array('employee_id', $columnNames)) {
                $userData['employee_id'] = null;
            }
            
            if (in_array('hire_date', $columnNames)) {
                $userData['hire_date'] = null;
            }
            
            if (in_array('last_login_at', $columnNames)) {
                $userData['last_login_at'] = null;
            }
            
            if (in_array('department_id', $columnNames)) {
                $userData['department_id'] = null;
            }
            
            if (in_array('avatar', $columnNames)) {
                $userData['avatar'] = null;
            }
            
            if (in_array('remember_token', $columnNames)) {
                $userData['remember_token'] = null;
            }
            
            // Insert admin user record
            $userId = DB::table('users')->insertGetId($userData);
            
            if (!$userId) {
                throw new Exception('Failed to insert admin user record - no ID returned');
            }
            
            // Assign admin role if roles table exists
            try {
                if (DB::connection()->getSchemaBuilder()->hasTable('roles')) {
                    $adminRole = DB::table('roles')->where('name', 'admin')->first();
                    if ($adminRole && DB::connection()->getSchemaBuilder()->hasTable('user_roles')) {
                        DB::table('user_roles')->insert([
                            'user_id' => $userId,
                            'role_id' => $adminRole->id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            } catch (Exception $roleError) {
                \Log::warning('Could not assign role to admin user: ' . $roleError->getMessage());
                // Don't fail installation if role assignment fails
            }

            // Update system settings with admin info
            $this->updateSystemSettings([
                'admin_name' => $request->admin_name,
                'admin_email' => $request->admin_email,
                'installation_date' => now()->toDateTimeString(),
                'system_version' => '1.0.0'
            ]);

            // Create installation lock file to mark system as installed
            $installationData = [
                'installed_at' => now()->toDateTimeString(),
                'version' => '1.0.0',
                'php_version' => PHP_VERSION,
                'environment' => config('app.env', 'production'),
                'app_name' => getAppName(),
                'admin_created' => true,
                'installation_completed' => true
            ];
            
            File::put(storage_path('installed'), json_encode($installationData, JSON_PRETTY_PRINT));

            // Clear caches for the completed installation
            try {
                Artisan::call('config:clear');
                Artisan::call('cache:clear');
            } catch (Exception $e) {
                // Log but don't fail if cache clearing fails
                \Log::warning('Cache clearing failed after installation: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Administrator account created successfully!',
                'admin_id' => $userId,
                'next_step' => 'final'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database query failed: ' . $e->getMessage(),
                'error_code' => $e->getCode(),
                'sql_state' => $e->errorInfo[0] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create admin account: ' . $e->getMessage(),
                'error_type' => get_class($e)
            ]);
        }
    }

    /**
     * Process installation finalization
     */
    protected function processFinal(Request $request)
    {
        try {
            // Clear all caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            // Create installation lock file with installation details
            $installationData = [
                'installed_at' => now()->toDateTimeString(),
                'version' => '1.0.0',
                'php_version' => PHP_VERSION,
                'environment' => config('app.env', 'production'),
                'app_name' => config('app.name', 'ThankDoc EHR')
            ];
            
            File::put(storage_path('installed'), json_encode($installationData, JSON_PRETTY_PRINT));

            // Update final system settings
            $this->updateSystemSettings([
                'installation_completed' => true,
                'installation_date' => now()->toDateTimeString(),
                'system_initialized' => true
            ]);

            // Optimize application for production
            if (config('app.env') === 'production') {
                Artisan::call('config:cache');
                Artisan::call('route:cache');
                Artisan::call('view:cache');
            }

            return response()->json([
                'success' => true,
                'message' => 'ThankDoc EHR installation completed successfully!',
                'redirect' => route('admin.login')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Installation finalization failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cleanup installation files
     */
    public function cleanup(Request $request)
    {
        try {
            // Security check - only allow if installation is complete
            if (!$this->isInstalled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Installation must be completed before cleanup.'
                ]);
            }

            $cleanupActions = [];

            // Remove installation views (optional - comment out if you want to keep them)
            $installViewsPath = resource_path('views/install');
            if (File::exists($installViewsPath)) {
                File::deleteDirectory($installViewsPath);
                $cleanupActions[] = 'Installation views removed';
            }

            // Remove or disable installation routes
            $routeFile = base_path('routes/install.php');
            if (File::exists($routeFile)) {
                File::delete($routeFile);
                $cleanupActions[] = 'Installation routes removed';
            }

            // Clear caches
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            // Re-cache for production
            if (config('app.env') === 'production') {
                Artisan::call('config:cache');
                Artisan::call('route:cache');
                Artisan::call('view:cache');
            }

            $cleanupActions[] = 'System caches refreshed';

            // Update cleanup status
            $this->updateSystemSettings([
                'installation_cleaned' => true,
                'cleanup_date' => now()->toDateTimeString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Installation cleanup completed successfully.',
                'actions' => $cleanupActions
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cleanup failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check system requirements - Enhanced for Laravel 10 & Banking Application (Shared Hosting Compatible)
     */
    private function checkRequirements()
    {
        // Detect shared hosting environment
        $isSharedHosting = $this->detectSharedHosting();
        
        $requirements = [
            // Environment Detection
            [
                'name' => 'Hosting Environment',
                'status' => true,
                'current' => $isSharedHosting ? 'Shared Hosting Detected' : 'VPS/Dedicated Server',
                'required' => 'Compatible',
                'critical' => false,
                'description' => $isSharedHosting ? 'Shared hosting detected - using compatible settings' : 'Full server environment detected'
            ],
            
            // PHP Version
            [
                'name' => 'PHP Version >= 8.1',
                'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
                'current' => PHP_VERSION,
                'required' => '8.1.0',
                'critical' => true,
                'description' => 'Laravel 10 requires PHP 8.1 or higher'
            ],
            
            // Core Extensions (Critical)
            [
                'name' => 'OpenSSL Extension',
                'status' => extension_loaded('openssl'),
                'current' => extension_loaded('openssl') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => true,
                'description' => 'Required for HTTPS, encryption, and secure communications'
            ],
            [
                'name' => 'PDO Extension',
                'status' => extension_loaded('pdo'),
                'current' => extension_loaded('pdo') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => true,
                'description' => 'Required for database connectivity'
            ],
            [
                'name' => 'PDO MySQL Extension',
                'status' => extension_loaded('pdo_mysql'),
                'current' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => true,
                'description' => 'Required for MySQL database operations'
            ],
            [
                'name' => 'Mbstring Extension',
                'status' => extension_loaded('mbstring'),
                'current' => extension_loaded('mbstring') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => true,
                'description' => 'Required for multi-byte string handling'
            ],
            [
                'name' => 'Tokenizer Extension',
                'status' => extension_loaded('tokenizer'),
                'current' => extension_loaded('tokenizer') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => true,
                'description' => 'Required for PHP token parsing'
            ],
            [
                'name' => 'XML Extension',
                'status' => extension_loaded('xml'),
                'current' => extension_loaded('xml') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => true,
                'description' => 'Required for XML processing'
            ],
            [
                'name' => 'Ctype Extension',
                'status' => extension_loaded('ctype'),
                'current' => extension_loaded('ctype') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => true,
                'description' => 'Required for character type checking'
            ],
            [
                'name' => 'JSON Extension',
                'status' => extension_loaded('json'),
                'current' => extension_loaded('json') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => true,
                'description' => 'Required for JSON data handling'
            ],
            [
                'name' => 'Fileinfo Extension',
                'status' => extension_loaded('fileinfo'),
                'current' => extension_loaded('fileinfo') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => true,
                'description' => 'Required for file type detection'
            ],
            
            // Banking Application Specific Extensions
            [
                'name' => 'BCMath Extension',
                'status' => extension_loaded('bcmath'),
                'current' => extension_loaded('bcmath') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => true,
                'description' => 'Required for precise decimal calculations in financial transactions'
            ],
            [
                'name' => 'cURL Extension',
                'status' => extension_loaded('curl'),
                'current' => extension_loaded('curl') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => true,
                'description' => 'Required for external API communications (payment gateways)'
            ],
            [
                'name' => 'Filter Extension',
                'status' => extension_loaded('filter'),
                'current' => extension_loaded('filter') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => true,
                'description' => 'Required for input validation and sanitization'
            ],
            [
                'name' => 'Hash Extension',
                'status' => extension_loaded('hash'),
                'current' => extension_loaded('hash') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => true,
                'description' => 'Required for password hashing and security'
            ],
            
            // Optional but Recommended Extensions
            [
                'name' => 'GD Extension',
                'status' => extension_loaded('gd'),
                'current' => extension_loaded('gd') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => false,
                'description' => 'Recommended for image processing (avatars, charts)'
            ],
            [
                'name' => 'Intl Extension',
                'status' => extension_loaded('intl'),
                'current' => extension_loaded('intl') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => false,
                'description' => 'Recommended for internationalization support'
            ],
            [
                'name' => 'Zip Extension',
                'status' => extension_loaded('zip'),
                'current' => extension_loaded('zip') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => false,
                'description' => 'Recommended for file compression and exports'
            ],
            [
                'name' => 'Exif Extension',
                'status' => extension_loaded('exif'),
                'current' => extension_loaded('exif') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => false,
                'description' => 'Optional for image metadata processing'
            ]
        ];
        
        // Add PHP configuration checks
        $phpConfig = [
            [
                'name' => 'Memory Limit >= 256M',
                'status' => $this->checkMemoryLimit('256M'),
                'current' => ini_get('memory_limit'),
                'required' => '256M',
                'critical' => true,
                'description' => 'Sufficient memory for banking application operations'
            ],
            [
                'name' => 'Max Execution Time >= 300',
                'status' => ini_get('max_execution_time') == 0 || ini_get('max_execution_time') >= 300,
                'current' => ini_get('max_execution_time') . ' seconds',
                'required' => '300 seconds',
                'critical' => false,
                'description' => 'Sufficient time for database operations and reports'
            ],
            [
                'name' => 'File Uploads Enabled',
                'status' => ini_get('file_uploads'),
                'current' => ini_get('file_uploads') ? 'Enabled' : 'Disabled',
                'required' => 'Enabled',
                'critical' => true,
                'description' => 'Required for document uploads and KYC verification'
            ],
            [
                'name' => 'Max Upload Size >= 10M',
                'status' => $this->parseSize(ini_get('upload_max_filesize')) >= $this->parseSize('10M'),
                'current' => ini_get('upload_max_filesize'),
                'required' => '10M',
                'critical' => false,
                'description' => 'Recommended for document and image uploads'
            ]
        ];
        
        return array_merge($requirements, $phpConfig);
    }
    
    /**
     * Check memory limit
     */
    private function checkMemoryLimit($required)
    {
        $memoryLimit = ini_get('memory_limit');
        if ($memoryLimit == -1) return true; // Unlimited
        
        return $this->parseSize($memoryLimit) >= $this->parseSize($required);
    }
    
    /**
     * Parse size string to bytes
     */
    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        return round($size);
    }

    /**
     * Check file permissions
     */
    private function checkPermissions()
    {
        $directories = [
            'storage/app' => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
            'public/uploads' => public_path('uploads'),
        ];

        $permissions = [];
        foreach ($directories as $name => $path) {
            if (!File::exists($path)) {
                File::makeDirectory($path, 0775, true);
            }
            
            $permissions[] = [
                'name' => $name,
                'path' => $path,
                'status' => is_writable($path),
                'permission' => substr(sprintf('%o', fileperms($path)), -4)
            ];
        }

        return $permissions;
    }

    /**
     * Get .env.example content
     */
    private function getEnvExample()
    {
        $examplePath = base_path('.env.example');
        if (File::exists($examplePath)) {
            return File::get($examplePath);
        }
        return '';
    }

    /**
     * Generate .env file content
     */
    private function generateEnvContent($data)
    {
        $envContent = File::get(base_path('.env.example'));
        
        // Replace placeholders with actual values
        $replacements = [
            'APP_NAME=Laravel' => 'APP_NAME="' . $data['app_name'] . '"',
            'APP_ENV=local' => 'APP_ENV=' . $data['app_env'],
            'APP_DEBUG=true' => 'APP_DEBUG=' . ($data['app_debug'] ? 'true' : 'false'),
            'APP_URL=http://localhost' => 'APP_URL=' . $data['app_url'],
            'LOG_LEVEL=debug' => 'LOG_LEVEL=' . $data['log_level'],
        ];

        foreach ($replacements as $search => $replace) {
            $envContent = str_replace($search, $replace, $envContent);
        }

        return $envContent;
    }

    /**
     * Test database connection only (for connection test button)
     */
    private function testDatabaseConnectionOnly(Request $request)
    {
        try {
            // Basic validation
            $host = trim($request->input('db_host', ''));
            $port = (int) $request->input('db_port', 3306);
            $database = trim($request->input('db_database', ''));
            $username = trim($request->input('db_username', ''));
            $password = $request->input('db_password', '');
            
            // Check required fields
            if (empty($host)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database host is required.'
                ]);
            }
            
            if (empty($database)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database name is required.'
                ]);
            }
            
            if (empty($username)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database username is required.'
                ]);
            }
            
            if ($port < 1 || $port > 65535) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database port must be between 1 and 65535.'
                ]);
            }

            // Simple PDO connection test
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            
            try {
                $pdo = new \PDO($dsn, $username, $password, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_TIMEOUT => 10,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
                ]);
                
                // Test basic query
                $stmt = $pdo->query('SELECT 1 as test');
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if (!$result || (int)$result['test'] !== 1) {
                    throw new Exception('Query test failed');
                }
                
                // Test database access
                $stmt = $pdo->query('SELECT DATABASE() as db_name');
                $dbResult = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                $pdo = null; // Close connection
                
                return response()->json([
                    'success' => true,
                    'message' => 'Database connection successful! Connected to: ' . $dbResult['db_name']
                ]);
                
            } catch (\PDOException $e) {
                $errorMessage = $this->getFriendlyDatabaseError($e->getMessage());
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ]);
            }
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test database connection with improved error handling
     */
    private function testDatabaseConnection($data)
    {
        // Clear any existing connections to avoid conflicts
        DB::purge('install_test');
        
        $connection = [
            'driver' => 'mysql',
            'host' => trim($data['db_host']),
            'port' => (int) $data['db_port'],
            'database' => trim($data['db_database']),
            'username' => trim($data['db_username']),
            'password' => $data['db_password'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false, // Less strict for testing
            'engine' => null,
            'options' => [
                \PDO::ATTR_TIMEOUT => 10, // 10 second timeout
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ],
        ];

        try {
            // Set the connection configuration
            Config::set('database.connections.install_test', $connection);
            
            // Clear config cache to ensure new settings are used
            if (function_exists('config_clear')) {
                config_clear();
            }
            
            // Test 1: Basic PDO connection
            $pdo = DB::connection('install_test')->getPdo();
            if (!$pdo) {
                throw new Exception('Failed to establish PDO connection');
            }
            
            // Test 2: Simple query execution
            $result = DB::connection('install_test')->select('SELECT 1 as test');
            if (empty($result) || (int)$result[0]->test !== 1) {
                throw new Exception('Failed to execute test query');
            }
            
            // Test 3: Check database exists and is accessible
            try {
                $databases = DB::connection('install_test')->select(
                    'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?',
                    [$data['db_database']]
                );
                if (empty($databases)) {
                    throw new Exception('Database "' . $data['db_database'] . '" does not exist or is not accessible');
                }
            } catch (Exception $e) {
                // Fallback: try to select from the database directly
                DB::connection('install_test')->select('SELECT DATABASE() as current_db');
            }
            
            // Test 4: Check basic privileges (optional)
            try {
                DB::connection('install_test')->select('SHOW TABLES');
            } catch (Exception $e) {
                // If SHOW TABLES fails, we might still be able to create tables
                // This is not critical for the connection test
            }
            
            return true;
            
        } catch (\PDOException $e) {
            throw new Exception($this->getFriendlyDatabaseError($e->getMessage()));
        } catch (\Illuminate\Database\QueryException $e) {
            throw new Exception($this->getFriendlyDatabaseError($e->getMessage()));
        } catch (Exception $e) {
            throw $e;
        } finally {
            // Clean up the test connection
            try {
                DB::purge('install_test');
            } catch (Exception $e) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Update .env file
     */
    private function updateEnvFile($data)
    {
        $envFile = base_path('.env');
        $envContent = File::get($envFile);

        foreach ($data as $key => $value) {
            // Handle values that need quotes
            if (is_string($value) && (Str::contains($value, ' ') || empty($value))) {
                $value = '"' . str_replace('"', '\"', $value) . '"';
            }
            
            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}={$value}";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        File::put($envFile, $envContent);
    }

    /**
     * Import database from SQL file (MySQL only - no SQLite fallback)
     */
    private function importDatabaseFromSql($data)
    {
        // Force MySQL connection - no SQLite fallback during installation
        
        // For MySQL, use the traditional SQL import approach
        $sqlFile = base_path('install-files/database.mysql.sql');
        
        if (!File::exists($sqlFile)) {
            throw new Exception('Database SQL file not found at: ' . $sqlFile);
        }
        
        $sqlContent = File::get($sqlFile);
        
        if (empty($sqlContent)) {
            throw new Exception('Database SQL file is empty');
        }
        
        // Set up database connection
        $connection = [
            'driver' => 'mysql',
            'host' => $data['db_host'],
            'port' => $data['db_port'],
            'database' => $data['db_database'],
            'username' => $data['db_username'],
            'password' => $data['db_password'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false, // Allow more flexible SQL import
            'engine' => null,
            'options' => [
                \PDO::ATTR_EMULATE_PREPARES => true,
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            ],
        ];
        
        Config::set('database.connections.install_import', $connection);
        
        try {
            // Clear any existing config cache
            Artisan::call('config:clear');
            
            // Test connection first
            $pdo = DB::connection('install_import')->getPdo();
            
            // Use a simpler approach - execute the SQL file directly using MySQL command line if available
            $mysqlPath = trim(shell_exec('which mysql'));
            
            if (!empty($mysqlPath) && file_exists($mysqlPath)) {
                // Use mysql command line tool for reliable import
                $command = sprintf(
                    '%s -h%s -P%s -u%s %s %s < %s 2>&1',
                    escapeshellarg($mysqlPath),
                    escapeshellarg($data['db_host']),
                    escapeshellarg($data['db_port']),
                    escapeshellarg($data['db_username']),
                    !empty($data['db_password']) ? '-p' . escapeshellarg($data['db_password']) : '',
                    escapeshellarg($data['db_database']),
                    escapeshellarg($sqlFile)
                );
                
                $output = [];
                $returnCode = 0;
                exec($command, $output, $returnCode);
                
                if ($returnCode !== 0) {
                    throw new Exception('MySQL import failed: ' . implode("\n", $output));
                }
            } else {
                // Fallback to Laravel DB operations with improved parsing
                $this->importSqlWithLaravel($sqlFile, 'install_import');
            }
            
            // Update the default database connection to use the new database
            $this->updateEnvFile([
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $data['db_host'],
                'DB_PORT' => $data['db_port'],
                'DB_DATABASE' => $data['db_database'],
                'DB_USERNAME' => $data['db_username'],
                'DB_PASSWORD' => $data['db_password'] ?? '',
            ]);
            
            // Clear config cache to use new database settings
            Artisan::call('config:clear');
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to import database: ' . $e->getMessage());
        }
    }
    
    /**
     * Import SQL file using Laravel DB operations (fallback method)
     */
    private function importSqlWithLaravel($sqlFile, $connectionName)
    {
        $sqlContent = File::get($sqlFile);
        
        // Remove MySQL-specific SET commands that might cause issues
        $sqlContent = preg_replace('/\/\*.*?\*\//s', '', $sqlContent); // Remove /* */ comments
        $sqlContent = preg_replace('/^\s*SET\s+.*?;\s*$/m', '', $sqlContent); // Remove SET commands
        $sqlContent = preg_replace('/^\s*START\s+TRANSACTION\s*;\s*$/m', '', $sqlContent); // Remove START TRANSACTION
        $sqlContent = preg_replace('/^\s*COMMIT\s*;\s*$/m', '', $sqlContent); // Remove COMMIT
        $sqlContent = preg_replace('/^\s*--.*$/m', '', $sqlContent); // Remove -- comments
        $sqlContent = preg_replace('/^\s*\/\*!.*?\*\/\s*;\s*$/m', '', $sqlContent); // Remove MySQL version comments
        
        // Split into statements
        $statements = preg_split('/;\s*$/m', $sqlContent);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            
            // Skip empty statements
            if (empty($statement)) {
                continue;
            }
            
            try {
                DB::connection($connectionName)->unprepared($statement);
            } catch (Exception $e) {
                // Log but continue with other statements
                \Log::warning('SQL statement failed during import: ' . $e->getMessage());
                \Log::warning('Failed statement: ' . substr($statement, 0, 200) . '...');
            }
        }
    }
    
    /**
     * Update system settings
     */
    private function updateSystemSettings($data)
    {
        try {
            // Update settings in database if settings table exists
            if (DB::connection()->getSchemaBuilder()->hasTable('settings')) {
                foreach ($data as $key => $value) {
                    DB::table('settings')->updateOrInsert(
                        ['key' => $key],
                        ['value' => $value, 'updated_at' => now()]
                    );
                }
            }
            
            // Also store in cache for quick access
            foreach ($data as $key => $value) {
                Cache::forever("setting.{$key}", $value);
            }
        } catch (Exception $e) {
            // Fallback to cache only if database is not ready
            foreach ($data as $key => $value) {
                Cache::forever("setting.{$key}", $value);
            }
        }
    }

    /**
     * Check if system is already installed
     */
    private function isInstalled()
    {
        return File::exists(storage_path('installed'));
    }

    /**
     * Auto-fix file permissions (CodeCanyon + Shared Hosting Compatible)
     */
    private function autoFixPermissions()
    {
        $directories = [
            'storage' => 0755,
            'storage/app' => 0755,
            'storage/framework' => 0755,
            'storage/framework/cache' => 0755,
            'storage/framework/cache/data' => 0755,
            'storage/framework/sessions' => 0755,
            'storage/framework/views' => 0755,
            'storage/logs' => 0755,
            'bootstrap/cache' => 0755,
        ];

        foreach ($directories as $dir => $permission) {
            $path = base_path($dir);
            
            if (!File::exists($path)) {
                try {
                    File::makeDirectory($path, $permission, true);
                } catch (Exception $e) {
                    // Shared hosting might not allow directory creation
                    // Log the issue but continue
                    error_log('Could not create directory: ' . $path . ' - ' . $e->getMessage());
                }
            } else {
                try {
                    chmod($path, $permission);
                } catch (Exception $e) {
                    // Shared hosting might not allow chmod
                    // This is often not critical for shared hosting
                    error_log('Could not change permissions for: ' . $path . ' - ' . $e->getMessage());
                }
            }
        }

        // Create public storage symlink if it doesn't exist (shared hosting compatible)
        $this->createStorageLink();
    }
    
    /**
     * Create storage symlink (shared hosting compatible)
     */
    private function createStorageLink()
    {
        $publicStoragePath = public_path('storage');
        $storagePublicPath = storage_path('app/public');
        
        if (!File::exists($publicStoragePath)) {
            try {
                // Try symlink first
                if (function_exists('symlink')) {
                    symlink($storagePublicPath, $publicStoragePath);
                } else {
                    // Fallback: create directory and copy structure
                    File::makeDirectory($publicStoragePath, 0755, true);
                    // Note: Files will need to be copied manually or via upload
                }
            } catch (Exception $e) {
                // Shared hosting fallback - create regular directory
                try {
                    File::makeDirectory($publicStoragePath, 0755, true);
                } catch (Exception $e2) {
                    error_log('Could not create storage link: ' . $e2->getMessage());
                }
            }
        }
    }

    /**
     * Check and fix permissions with detailed status
     */
    private function checkAndFixPermissions()
    {
        $directories = [
            'storage/app' => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $permissions = [];
        foreach ($directories as $name => $path) {
            $wasFixed = false;
            
            if (!File::exists($path)) {
                File::makeDirectory($path, 0775, true);
                $wasFixed = true;
            }
            
            if (!is_writable($path)) {
                chmod($path, 0775);
                $wasFixed = true;
            }
            
            $permissions[] = [
                'name' => $name,
                'path' => $path,
                'status' => is_writable($path),
                'permission' => substr(sprintf('%o', fileperms($path)), -4),
                'auto_fixed' => $wasFixed
            ];
        }

        return $permissions;
    }

    /**
     * Enhanced database import with progress tracking
     */
    private function importDatabaseWithProgress()
    {
        $sqlFile = base_path('install-files/database.mysql.sql');
        
        if (!File::exists($sqlFile)) {
            throw new Exception('Database SQL file not found at: ' . $sqlFile);
        }
        
        $sqlContent = File::get($sqlFile);
        if (empty($sqlContent)) {
            throw new Exception('Database SQL file is empty');
        }
        
        try {
            // Use the default connection (which should already be configured)
            // Clear any cached connections first
            DB::purge('mysql');
            
            // Test connection using default connection
            $pdo = DB::connection()->getPdo();
            
            // Check if tables already exist and drop them if needed
            $this->dropExistingTables();
            
            // Improved SQL file parsing and execution
            $lines = explode("\n", $sqlContent);
            $statements = [];
            $currentStatement = '';
            $inMultiLineComment = false;

            foreach ($lines as $line) {
                $line = trim($line);
                
                // Handle multi-line comments
                if (strpos($line, '/*') !== false) {
                    $inMultiLineComment = true;
                }
                if (strpos($line, '*/') !== false) {
                    $inMultiLineComment = false;
                    continue;
                }
                if ($inMultiLineComment) {
                    continue;
                }
                
                // Skip empty lines and single-line comments
                if (empty($line) || strpos($line, '--') === 0 || strpos($line, '#') === 0) {
                    continue;
                }
                
                // Add line to current statement
                $currentStatement .= $line . " ";
                
                // Check if statement ends with semicolon
                if (substr(rtrim($line), -1) === ';') {
                    $statements[] = trim($currentStatement);
                    $currentStatement = '';
                }
            }

            // Add any remaining statement
            if (!empty(trim($currentStatement))) {
                $statements[] = trim($currentStatement);
            }

            $statements = array_filter($statements);
            $total = count($statements);
            $completed = 0;

            // Execute statements without transaction for DDL statements
            foreach ($statements as $statement) {
                $statement = trim($statement);
                
                // Skip empty statements and comments
                if (empty($statement) || 
                    preg_match('/^--/', $statement) || 
                    preg_match('/^\/\*/', $statement) ||
                    preg_match('/^#/', $statement) ||
                    preg_match('/^\s*$/', $statement)) {
                    continue;
                }
                
                try {
                    DB::unprepared($statement);
                    $completed++;
                    \Log::info('SQL statement executed successfully', ['statement_preview' => substr($statement, 0, 100)]);
                } catch (Exception $e) {
                    \Log::error('SQL statement failed', [
                        'error' => $e->getMessage(),
                        'statement_preview' => substr($statement, 0, 200)
                    ]);
                    throw $e;
                }
            }
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to import database: ' . $e->getMessage());
        }
    }
    
    /**
     * Drop existing tables before import
     */
    private function dropExistingTables()
    {
        try {
            \Log::info('Starting to drop existing tables');
            
            // Get list of all tables in the database
            $tables = DB::select('SHOW TABLES');
            \Log::info('Found tables in database', ['count' => count($tables)]);
            
            if (count($tables) > 0) {
                // Disable foreign key checks
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');
                \Log::info('Disabled foreign key checks');
                
                foreach ($tables as $table) {
                    $tableName = array_values((array) $table)[0];
                    try {
                        DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
                        \Log::info('Dropped table', ['table' => $tableName]);
                    } catch (Exception $dropError) {
                        \Log::warning('Failed to drop table', ['table' => $tableName, 'error' => $dropError->getMessage()]);
                    }
                }
                
                // Re-enable foreign key checks
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
                \Log::info('Re-enabled foreign key checks');
                
                // Verify tables are dropped
                $remainingTables = DB::select('SHOW TABLES');
                \Log::info('Tables remaining after drop', ['count' => count($remainingTables)]);
            }
        } catch (Exception $e) {
            \Log::error('Could not drop existing tables', ['error' => $e->getMessage()]);
            // Don't throw exception - let import proceed and handle errors there
        }
    }
    
    /**
     * Simple database import fallback method
     */
    private function simpleDatabaseImport()
    {
        $sqlFile = base_path('install-files/database.mysql.sql');
        
        if (!File::exists($sqlFile)) {
            throw new Exception('Database SQL file not found at: ' . $sqlFile);
        }
        
        $sqlContent = File::get($sqlFile);
        if (empty($sqlContent)) {
            throw new Exception('Database SQL file is empty');
        }
        
        // Check if tables already exist and drop them if needed
        $this->dropExistingTables();
        
        // Parse SQL manually line by line for better accuracy
        $lines = explode("\n", $sqlContent);
        $statements = [];
        $currentStatement = '';
        $inMultiLineComment = false;

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Handle multi-line comments
            if (strpos($line, '/*') !== false) {
                $inMultiLineComment = true;
            }
            if (strpos($line, '*/') !== false) {
                $inMultiLineComment = false;
                continue;
            }
            if ($inMultiLineComment) {
                continue;
            }
            
            // Skip empty lines and single-line comments
            if (empty($line) || strpos($line, '--') === 0 || strpos($line, '#') === 0) {
                continue;
            }
            
            // Add line to current statement
            $currentStatement .= $line . " ";
            
            // Check if statement ends with semicolon
            if (substr(rtrim($line), -1) === ';') {
                $statements[] = trim($currentStatement);
                $currentStatement = '';
            }
        }

        // Add any remaining statement
        if (!empty(trim($currentStatement))) {
            $statements[] = trim($currentStatement);
        }

        $statements = array_filter($statements);
        
        // Don't use transactions for DDL statements (CREATE TABLE, etc.)
        // as they auto-commit in MySQL and can cause transaction conflicts
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            
            // Skip empty statements and comments
            if (empty($statement) || 
                preg_match('/^--/', $statement) || 
                preg_match('/^\/*/', $statement) ||
                preg_match('/^#/', $statement) ||
                preg_match('/^\s*$/', $statement)) {
                continue;
            }
            
            try {
                // Use unprepared for DDL statements to avoid transaction issues
                DB::unprepared($statement);
                \Log::info('Simple SQL import - statement executed', ['preview' => substr($statement, 0, 100)]);
            } catch (Exception $e) {
                \Log::error('Simple SQL import - statement failed', [
                    'error' => $e->getMessage(),
                    'statement_preview' => substr($statement, 0, 200)
                ]);
                throw $e;
            }
        }
        
        return true;
    }
    
    /**
     * Verify database import was successful by checking for key tables
     */
    private function verifyDatabaseImport()
    {
        try {
            // Check for key tables that should exist after import
            $requiredTables = ['users', 'departments', 'patients', 'appointments', 'settings'];
            
            foreach ($requiredTables as $table) {
                if (!DB::connection()->getSchemaBuilder()->hasTable($table)) {
                    \Log::error('Required table missing after import', ['table' => $table]);
                    return false;
                }
            }
            
            // Check if users table has expected columns
            $userColumns = DB::connection()->getSchemaBuilder()->getColumnListing('users');
            $expectedColumns = ['id', 'name', 'email', 'password', 'role', 'is_admin', 'is_active'];
            
            foreach ($expectedColumns as $column) {
                if (!in_array($column, $userColumns)) {
                    \Log::error('Required column missing in users table', ['column' => $column]);
                    return false;
                }
            }
            
            \Log::info('Database import verification successful');
            return true;
        } catch (Exception $e) {
            \Log::error('Database import verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Get installation status and details
     */
    public function status()
    {
        $isInstalled = $this->isInstalled();
        $installationData = null;
        
        if ($isInstalled) {
            $installFile = storage_path('installed');
            $content = File::get($installFile);
            $installationData = json_decode($content, true) ?: ['installed_at' => $content];
        }
        
        return response()->json([
            'installed' => $isInstalled,
            'data' => $installationData,
            'product_info' => $this->getProductInfo()
        ]);
    }

    /**
     * Check installation progress (AJAX endpoint)
     */
    public function checkProgress(Request $request)
    {
        $step = $request->get('step', 'unknown');
        
        // Return progress based on current step
        $progress = [
            'welcome' => 0,
            'requirements' => 25,
            'database' => 50,
            'admin' => 75,
            'final' => 100
        ];
        
        return response()->json([
            'step' => $step,
            'progress' => $progress[$step] ?? 0,
            'message' => 'Installation in progress...'
        ]);
    }
    
    /**
     * Detect if running on shared hosting environment
     */
    private function detectSharedHosting()
    {
        // Check for common shared hosting indicators
        $indicators = [
            // Check if we can't execute system commands
            !function_exists('exec') || !function_exists('shell_exec'),
            
            // Check if symlink function is disabled
            !function_exists('symlink'),
            
            // Check for common shared hosting paths
            strpos(__DIR__, '/public_html/') !== false,
            strpos(__DIR__, '/www/') !== false,
            strpos(__DIR__, '/htdocs/') !== false,
            
            // Check for cPanel indicators
            isset($_SERVER['cPanel']) || isset($_SERVER['HTTP_X_FORWARDED_HOST']),
            
            // Check if running as apache/www-data but can't write to system dirs
            function_exists('posix_getuid') && posix_getuid() > 1000,
            
            // Check if we're in a subdirectory structure typical of shared hosting
            substr_count(base_path(), '/') > 5,
        ];
        
        // If more than 2 indicators are true, likely shared hosting
        return count(array_filter($indicators)) >= 2;
    }
    
    /**
     * Get shared hosting specific recommendations
     */
    private function getSharedHostingRecommendations()
    {
        return [
            'File Permissions' => '755 for folders, 644 for files (via cPanel File Manager)',
            'Database Setup' => 'Create database via hosting control panel first',
            'PHP Configuration' => 'Contact hosting support for PHP extension installation',
            'Storage Links' => 'May require manual file copying instead of symlinks',
            'Debugging' => 'Check hosting error logs for detailed error information'
        ];
    }

    /**
     * Update APP_URL in .env file based on the current domain
     */
    private function autoFixAppUrl()
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return;
        }
        
        $envContent = file_get_contents($envPath);
        $detectedUrl = $this->getAppUrl();

        // If APP_URL is empty or set to default placeholder, automatically update it to the detected domain
        if (strpos($envContent, "APP_URL=https://yourdomain.com") !== false || 
            strpos($envContent, "APP_URL=") !== false && preg_match('/^APP_URL=\s*$/m', $envContent)) {
            $newEnvContent = preg_replace('/^APP_URL=.*$/m', 'APP_URL=' . $detectedUrl, $envContent);
            if ($newEnvContent !== null) {
                file_put_contents($envPath, $newEnvContent);
                // Update config cache for immediate effect
                config(['app.url' => $detectedUrl]);
            }
        }
    }

    /**
     * Detect the application's URL dynamically
     * 
     * @return string
     */
    private function getAppUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'];
    }
    
    /**
     * Convert technical database errors to user-friendly messages
     */
    private function getFriendlyDatabaseError($message)
    {
        $message = strtolower($message);
        
        // Connection refused
        if (str_contains($message, 'connection refused') || str_contains($message, 'failed to connect')) {
            return 'Cannot connect to database server. Please check the host and port settings.';
        }
        
        // Access denied
        if (str_contains($message, 'access denied') || str_contains($message, 'authentication failed')) {
            return 'Database access denied. Please check your username and password.';
        }
        
        // Unknown database
        if (str_contains($message, 'unknown database') || str_contains($message, 'database does not exist')) {
            return 'Database does not exist. Please create the database first or check the database name.';
        }
        
        // Host not found
        if (str_contains($message, 'host') && (str_contains($message, 'not found') || str_contains($message, 'unknown host'))) {
            return 'Database host not found. Please check the host address.';
        }
        
        // Timeout
        if (str_contains($message, 'timeout') || str_contains($message, 'timed out')) {
            return 'Database connection timed out. The server may be overloaded or unreachable.';
        }
        
        // Too many connections
        if (str_contains($message, 'too many connections')) {
            return 'Too many database connections. Please try again in a few moments.';
        }
        
        // Server has gone away
        if (str_contains($message, 'server has gone away')) {
            return 'Database server disconnected. Please check your connection settings.';
        }
        
        // Permission issues
        if (str_contains($message, 'permission denied') || str_contains($message, 'access denied')) {
            return 'Permission denied. Please check database user privileges.';
        }
        
        // Default fallback for other errors
        return 'Database connection failed. Please verify your database settings and try again.';
    }
}