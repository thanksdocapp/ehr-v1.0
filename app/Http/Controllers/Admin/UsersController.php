<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\PasswordResetToken;
use App\Models\UserActivity;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with(['departments', 'department', 'doctor.departments', 'doctor.department']); // Load both relationships for compatibility, including doctor's departments
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }
        
        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        // Department filter (support both old and new relationships)
        if ($request->filled('department')) {
            $query->byDepartment($request->department);
        }
        
        // Status filter
        if ($request->filled('status')) {
            $active = $request->status === 'active';
            $query->where('is_active', $active);
        }
        
        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);
        
        $users = $query->paginate(15)->withQueryString();
        $departments = Department::all();
        $roles = User::getRoles();
        
        return view('admin.users.index', compact('users', 'departments', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $departments = Department::all();
        $roles = User::getRoles();
        
        return view('admin.users.create', compact('departments', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,doctor,nurse,receptionist,pharmacist,technician,staff',
            'department_id' => 'nullable|exists:departments,id', // Primary department for backward compatibility
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
            'bio' => 'nullable|string|max:1000',
            'specialization' => 'nullable|string|max:255',
            'employee_id' => 'nullable|string|max:50|unique:users',
            'hire_date' => 'nullable|date',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
        ]);
        
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '_' . Str::random(10) . '.' . $avatar->getClientOriginalExtension();
            $avatar->move(public_path('assets/images/avatars'), $avatarName);
            $validated['avatar'] = $avatarName;
        }
        
        // Generate employee ID if not provided
        if (empty($validated['employee_id'])) {
            $maxAttempts = 100;
            $attempt = 0;
            
            do {
                // Find the next available employee ID
                $lastEmployeeId = User::whereNotNull('employee_id')
                    ->where('employee_id', 'LIKE', 'EMP%')
                    ->orderBy('employee_id', 'desc')
                    ->first()?->employee_id;
                    
                if ($lastEmployeeId) {
                    // Extract number from last employee ID (e.g., EMP0003 -> 3)
                    $lastNumber = intval(substr($lastEmployeeId, 3));
                    $nextNumber = $lastNumber + 1 + $attempt; // Add attempt to avoid conflicts
                } else {
                    $nextNumber = 1 + $attempt;
                }
                
                $proposedEmployeeId = 'EMP' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                
                // Check if this ID already exists
                $exists = User::where('employee_id', $proposedEmployeeId)->exists();
                
                if (!$exists) {
                    $validated['employee_id'] = $proposedEmployeeId;
                    break;
                }
                
                $attempt++;
            } while ($attempt < $maxAttempts);
            
            if ($attempt >= $maxAttempts) {
                throw new \Exception('Unable to generate unique employee ID after ' . $maxAttempts . ' attempts');
            }
        }
        
        // Handle multiple departments
        $departmentIds = $request->input('department_ids', []);
        $primaryDepartmentId = $request->input('department_id');
        
        // Ensure primary department is in the list if provided
        if ($primaryDepartmentId && !in_array($primaryDepartmentId, $departmentIds)) {
            $departmentIds[] = $primaryDepartmentId;
        }
        
        $user = User::create($validated);
        
        // Sync departments to pivot table
        if (!empty($departmentIds)) {
            $syncData = [];
            foreach ($departmentIds as $deptId) {
                $syncData[$deptId] = [
                    'is_primary' => ($deptId == $primaryDepartmentId),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $user->departments()->sync($syncData);
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['departments', 'department', 'patient', 'doctor']); // Load both for compatibility
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user->load(['departments', 'department']); // Load both for compatibility
        $departments = Department::all();
        $roles = User::getRoles();
        
        return view('admin.users.edit', compact('user', 'departments', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,doctor,nurse,receptionist,pharmacist,technician,staff',
            'department_id' => 'nullable|exists:departments,id', // Primary department for backward compatibility
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
            'bio' => 'nullable|string|max:1000',
            'specialization' => 'nullable|string|max:255',
            'employee_id' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'hire_date' => 'nullable|date',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
        ]);
        
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && file_exists(public_path('assets/images/avatars/' . $user->avatar))) {
                unlink(public_path('assets/images/avatars/' . $user->avatar));
            }
            
            $avatar = $request->file('avatar');
            $avatarName = time() . '_' . Str::random(10) . '.' . $avatar->getClientOriginalExtension();
            $avatar->move(public_path('assets/images/avatars'), $avatarName);
            $validated['avatar'] = $avatarName;
        }
        
        // Only update password if provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        
        // Handle multiple departments
        $departmentIds = $request->input('department_ids', []);
        $primaryDepartmentId = $request->input('department_id');
        
        // Ensure primary department is in the list if provided
        if ($primaryDepartmentId && !in_array($primaryDepartmentId, $departmentIds)) {
            $departmentIds[] = $primaryDepartmentId;
        }
        
        $user->update($validated);
        
        // Sync departments to pivot table
        if (!empty($departmentIds)) {
            $syncData = [];
            foreach ($departmentIds as $deptId) {
                $syncData[$deptId] = [
                    'is_primary' => ($deptId == $primaryDepartmentId),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $user->departments()->sync($syncData);
        } else {
            // If no departments selected, clear the pivot table
            $user->departments()->sync([]);
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deletion of current admin user
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account!');
        }
        
        // Delete avatar if exists
        if ($user->avatar && file_exists(public_path('assets/images/avatars/' . $user->avatar))) {
            unlink(public_path('assets/images/avatars/' . $user->avatar));
        }
        
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }
    
    /**
     * Toggle user status (active/inactive).
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        
        // If user is a doctor, also sync status with Doctor model
        if ($user->role === 'doctor' && $user->doctor) {
            $user->doctor->update(['is_active' => $user->is_active]);
        }
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        
        return response()->json([
            'success' => true,
            'message' => "User {$status} successfully!",
            'is_active' => $user->is_active
        ]);
    }
    
    /**
     * Reset user password with secure token and audit trail.
     */
    public function resetPassword(Request $request, User $user)
    {
        // Check permission
        if (!Auth::user()->hasPermission('users.reset_password') && !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to reset passwords.'
            ], 403);
        }
        
        // Validate request
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'notify_via' => 'required|in:email,sms,both',
            'force_change' => 'boolean',
        ]);
        
        // Check if user account is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reset password for inactive user account.'
            ], 400);
        }
        
        // Prevent self-reset through this admin function
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot reset your own password using this function.'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Create password reset token
            $resetToken = PasswordResetToken::createForUser(
                $user->id,
                Auth::id(),
                $validated['reason'],
                24 // Valid for 24 hours
            );
            
            // Mark user for password change on next login
            $user->update([
                'password_change_required' => $validated['force_change'] ?? true
            ]);
            
            // Invalidate all active sessions for this user if required
            if ($resetToken->invalidate_sessions) {
                try {
                    // Check if sessions table exists
                    if (Schema::hasTable('sessions')) {
                        DB::table('sessions')
                            ->where('user_id', $user->id)
                            ->delete();
                    }
                } catch (\Exception $e) {
                    // Log but don't fail if session invalidation fails
                    \Log::warning('Could not invalidate sessions: ' . $e->getMessage());
                }
            }
            
            // Log the action in audit trail
            UserActivity::create([
                'user_id' => Auth::id(),
                'action' => 'password_reset_admin',
                'model_type' => 'User',
                'model_id' => $user->id,
                'description' => "Admin reset password for user: {$user->name} (ID: {$user->id}). Reason: {$validated['reason']}",
                'old_values' => null,
                'new_values' => json_encode([
                    'force_password_change' => $validated['force_change'] ?? true,
                    'invalidate_sessions' => true,
                    'reason' => $validated['reason'],
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => Session::getId(),
                'severity' => 'high',
            ]);
            
            // Generate reset link
            $resetLink = url('/password/reset/' . $resetToken->token);
            
            // Send notification
            $this->sendPasswordResetNotification($user, $resetLink, $validated['notify_via']);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Password reset link has been sent to the user.',
                'reset_link' => config('app.debug') ? $resetLink : null, // Only show in debug mode
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Resend login credentials to user.
     */
    public function resendCredentials(Request $request, User $user)
    {
        // Check permission
        if (!Auth::user()->hasPermission('users.reset_password') && !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to resend credentials.'
            ], 403);
        }
        
        // Validate
        $validated = $request->validate([
            'notify_via' => 'required|in:email,sms,both',
        ]);
        
        // Check account status
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot resend credentials for inactive account.'
            ], 400);
        }
        
        try {
            // Create a password reset token (instead of sending plaintext password)
            $resetToken = PasswordResetToken::createForUser(
                $user->id,
                Auth::id(),
                'Credentials resend requested by admin',
                72 // Valid for 72 hours for initial setup
            );
            
            $resetLink = url('/password/reset/' . $resetToken->token);
            $portalLink = url('/login');
            
            // Log the action
            UserActivity::create([
                'user_id' => Auth::id(),
                'action' => 'resend_credentials',
                'model_type' => 'User',
                'model_id' => $user->id,
                'description' => "Admin resent credentials to user: {$user->name} (ID: {$user->id})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => Session::getId(),
                'severity' => 'medium',
            ]);
            
            // Send notification with username and reset link
            $this->sendCredentialsNotification($user, $portalLink, $resetLink, $validated['notify_via']);
            
            return response()->json([
                'success' => true,
                'message' => 'Login credentials have been sent to the user.',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend credentials: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Send password reset notification using email template service.
     */
    private function sendPasswordResetNotification($user, $resetLink, $notifyVia)
    {
        if (in_array($notifyVia, ['email', 'both']) && $user->email) {
            try {
                $emailService = app(EmailNotificationService::class);
                
                $emailLog = $emailService->sendTemplateEmail(
                    'admin_password_reset',
                    [$user->email => $user->name],
                    [
                        'user_name' => $user->name,
                        'reason' => 'Admin-initiated password reset',
                        'reset_link' => $resetLink,
                        'expiry_hours' => 24,
                        'hospital_name' => config('app.name'),
                    ],
                    [
                        'email_type' => 'password_reset',
                        'event' => 'password.reset.admin',
                        'metadata' => [
                            'user_id' => $user->id,
                            'initiated_by' => auth()->id(),
                        ]
                    ]
                );
                
                if ($emailLog) {
                    \Log::info('Password reset email logged', [
                        'email_log_id' => $emailLog->id,
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);
                } else {
                    \Log::warning('Password reset email sent but not logged', [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send password reset email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        if (in_array($notifyVia, ['sms', 'both']) && $user->phone) {
            // TODO: Send SMS - integrate with your SMS system if needed
            \Log::info('SMS notification skipped (not implemented)', ['user_id' => $user->id]);
        }
    }
    
    /**
     * Send credentials notification using email template service.
     */
    private function sendCredentialsNotification($user, $portalLink, $resetLink, $notifyVia)
    {
        if (in_array($notifyVia, ['email', 'both']) && $user->email) {
            try {
                $emailService = app(EmailNotificationService::class);
                
                $emailService->sendTemplateEmail(
                    'admin_welcome_credentials',
                    [$user->email => $user->name],
                    [
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'user_role' => ucfirst($user->role ?? 'Staff'),
                        'employee_id' => $user->employee_id ?? 'N/A',
                        'portal_link' => $portalLink,
                        'reset_link' => $resetLink,
                        'expiry_hours' => 72,
                        'hospital_name' => config('app.name'),
                    ]
                );
                
                \Log::info('Welcome credentials email sent', ['user_id' => $user->id, 'email' => $user->email]);
            } catch (\Exception $e) {
                \Log::error('Failed to send welcome credentials email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        if (in_array($notifyVia, ['sms', 'both']) && $user->phone) {
            // TODO: Send SMS - integrate with your SMS system if needed
            \Log::info('SMS notification skipped (not implemented)', ['user_id' => $user->id]);
        }
    }
    
    /**
     * Get user statistics for dashboard.
     */
    public function getStats()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'doctors' => User::where('role', 'doctor')->count(),
            'nurses' => User::where('role', 'nurse')->count(),
            'staff' => User::where('role', 'staff')->count(),
            'recent_users' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];
        
        return response()->json($stats);
    }
}
