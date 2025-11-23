<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_admin',
        'is_active',
        'department_id',
        'avatar',
        'bio',
        'specialization',
        'employee_id',
        'hire_date',
        'last_login_at',
        'email_verified_at',
        'password_change_required',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'dark_mode',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'hire_date' => 'date',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
        'password_change_required' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'two_factor_recovery_codes' => 'array',
        'two_factor_confirmed_at' => 'datetime',
        'dark_mode' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships for Hospital Management System
     */

    /**
     * Get the patient record if this user is a patient.
     */
    public function patient()
    {
        return $this->hasOne(Patient::class, 'email', 'email');
    }

    /**
     * Get the doctor record if this user is a doctor.
     */
    public function doctor()
    {
        return $this->hasOne(Doctor::class, 'user_id', 'id');
    }

    /**
     * Many-to-many relationship with departments
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'user_department')
            ->withPivot('is_primary')
            ->withTimestamps()
            ->orderByPivot('is_primary', 'desc');
    }

    /**
     * Primary department relationship (for backward compatibility)
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the two-factor authentication record for this user.
     */
    public function twoFactorAuth()
    {
        return $this->hasOne(TwoFactorAuth::class);
    }

    /**
     * Get primary department from pivot table
     */
    public function primaryDepartment()
    {
        return $this->departments()->wherePivot('is_primary', true)->first() 
            ?? $this->departments()->first() 
            ?? $this->department; // Fallback to old department_id
    }

    /**
     * Helper Methods
     */

    /**
     * Check if user is an admin.
     */
    public function isAdmin()
    {
        return $this->is_admin;
    }

    /**
     * Check if user is active.
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Get user's role display name.
     */
    public function getRoleDisplayAttribute()
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'doctor' => 'Doctor',
            'nurse' => 'Nurse',
            'receptionist' => 'Receptionist',
            'pharmacist' => 'Pharmacist',
            'technician' => 'Technician',
            'staff' => 'Staff',
            default => 'User'
        };
    }

    /**
     * Get user's status badge color.
     */
    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'success' : 'danger';
    }

    /**
     * Get user's avatar URL.
     */
    public function getAvatarUrlAttribute()
    {
        // If user is a doctor, try to use the doctor's photo first
        if ($this->role === 'doctor' && $this->doctor && $this->doctor->photo) {
            // Check if the doctor's photo file exists
            $doctorPhotoPath = storage_path('app/public/uploads/doctors/' . $this->doctor->photo);
            if (file_exists($doctorPhotoPath)) {
                return asset('storage/uploads/doctors/' . $this->doctor->photo);
            }
        }
        
        // Use the user's avatar if available
        if ($this->avatar) {
            // For shared hosting - check if symlink exists, fallback to direct storage access
            if (file_exists(public_path('storage/avatars/' . $this->avatar))) {
                return asset('storage/avatars/' . $this->avatar);
            }
            // Fallback for shared hosting without symlinks
            return url('storage-access/avatars/' . $this->avatar);
        }
        
        // Generate a simple SVG avatar as fallback
        $colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1'];
        $color = $colors[array_sum(str_split(ord($this->name[0]))) % count($colors)];
        
        return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="150" height="150"><rect width="150" height="150" fill="' . $color . '"/><text x="75" y="80" font-family="Arial" font-size="24" fill="white" text-anchor="middle">' . strtoupper(substr($this->name, 0, 1)) . '</text></svg>');
    }

    /**
     * Scope to get only admin users.
     */
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    /**
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get users by role.
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to get users by department (support both old and new relationships).
     */
    public function scopeByDepartment($query, $departmentId)
    {
        // Support both old department_id and new pivot table
        return $query->where(function($q) use ($departmentId) {
            $q->where('department_id', $departmentId)
              ->orWhereHas('departments', function($deptQuery) use ($departmentId) {
                  $deptQuery->where('departments.id', $departmentId);
              });
        });
    }

    /**
     * Scope to get users with any of the given departments
     */
    public function scopeByDepartments($query, array $departmentIds)
    {
        return $query->where(function($q) use ($departmentIds) {
            $q->whereIn('department_id', $departmentIds)
              ->orWhereHas('departments', function($deptQuery) use ($departmentIds) {
                  $deptQuery->whereIn('departments.id', $departmentIds);
              });
        });
    }

    /**
     * ROLE-BASED ACCESS CONTROL RELATIONSHIPS
     */

    /**
     * Get the roles assigned to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Get the permissions assigned directly to the user.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
                   ->withPivot('type');
    }

    /**
     * Get user activities.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }

    /**
     * Get all notifications for the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * Get unread notifications for the user.
     */
    public function unreadNotifications()
    {
        return $this->hasMany(UserNotification::class)->where('is_read', false);
    }

    /**
     * Get recent notifications (last 30 days).
     */
    public function recentNotifications()
    {
        return $this->hasMany(UserNotification::class)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->orderBy('created_at', 'desc');
    }

    /**
     * PERMISSION METHODS
     */

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->is_admin && $this->role === 'admin') {
            return true;
        }

        // Check direct permissions first
        $directPermission = $this->permissions()->where('name', $permission)->first();
        if ($directPermission) {
            return $directPermission->pivot->type === 'grant';
        }

        // Check role-based permissions
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Assign role to user.
     */
    public function assignRole(string $roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if ($role && !$this->roles()->where('name', $roleName)->exists()) {
            $this->roles()->attach($role->id);
            
            // Log activity
            UserActivity::log([
                'user_id' => $this->id, // Use the user being modified
                'action' => 'role_assigned',
                'description' => "Role '{$role->display_name}' assigned to user",
                'model_type' => static::class,
                'model_id' => $this->id,
                'severity' => 'medium',
            ]);
        }
    }

    /**
     * Remove role from user.
     */
    public function removeRole(string $roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role->id);
            
            // Log activity
            UserActivity::log([
                'action' => 'role_removed',
                'description' => "Role '{$role->display_name}' removed from user",
                'model_type' => static::class,
                'model_id' => $this->id,
                'severity' => 'medium',
            ]);
        }
    }

    /**
     * Sync roles for user.
     */
    public function syncRoles(array $roleNames)
    {
        $roles = Role::whereIn('name', $roleNames)->get();
        $this->roles()->sync($roles->pluck('id'));
        
        // Log activity
        UserActivity::log([
            'action' => 'roles_synced',
            'description' => 'User roles synchronized: ' . implode(', ', $roleNames),
            'model_type' => static::class,
            'model_id' => $this->id,
            'severity' => 'medium',
        ]);
    }

    /**
     * Grant permission directly to user.
     */
    public function grantPermission(string $permission)
    {
        $permissionModel = Permission::where('name', $permission)->first();
        if ($permissionModel) {
            $this->permissions()->syncWithoutDetaching([
                $permissionModel->id => ['type' => 'grant']
            ]);
        }
    }

    /**
     * Deny permission directly to user.
     */
    public function denyPermission(string $permission)
    {
        $permissionModel = Permission::where('name', $permission)->first();
        if ($permissionModel) {
            $this->permissions()->syncWithoutDetaching([
                $permissionModel->id => ['type' => 'deny']
            ]);
        }
    }

    /**
     * Get all user permissions (from roles and direct assignments).
     */
    public function getAllPermissions(): array
    {
        $permissions = [];
        
        // Get permissions from roles
        foreach ($this->roles as $role) {
            $rolePermissions = $role->permissions()->pluck('name')->toArray();
            $permissions = array_merge($permissions, $rolePermissions);
        }
        
        // Get direct permissions
        $directPermissions = $this->permissions()->where('type', 'grant')->pluck('name')->toArray();
        $permissions = array_merge($permissions, $directPermissions);
        
        // Remove denied permissions
        $deniedPermissions = $this->permissions()->where('type', 'deny')->pluck('name')->toArray();
        $permissions = array_diff($permissions, $deniedPermissions);
        
        return array_unique($permissions);
    }

    /**
     * Get available user roles.
     */
    public static function getRoles()
    {
        return [
            'admin' => 'Administrator',
            'doctor' => 'Doctor',
            'nurse' => 'Nurse',
            'receptionist' => 'Receptionist',
            'pharmacist' => 'Pharmacist',
            'technician' => 'Technician',
            'staff' => 'Staff',
        ];
    }
}
