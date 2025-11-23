<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the users that have this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * Get the permissions for this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Check if role has permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists() ||
               (is_array($this->permissions) && in_array($permission, $this->permissions));
    }

    /**
     * Grant permission to role.
     */
    public function grantPermission(string $permission)
    {
        $permissionModel = Permission::where('name', $permission)->first();
        if ($permissionModel && !$this->permissions()->where('name', $permission)->exists()) {
            $this->permissions()->attach($permissionModel->id);
        }
    }

    /**
     * Revoke permission from role.
     */
    public function revokePermission(string $permission)
    {
        $permissionModel = Permission::where('name', $permission)->first();
        if ($permissionModel) {
            $this->permissions()->detach($permissionModel->id);
        }
    }

    /**
     * Sync permissions for role.
     */
    public function syncPermissions(array $permissions)
    {
        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
        $this->permissions()->sync($permissionIds);
    }

    /**
     * Scope to get active roles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get default hospital roles.
     */
    public static function getHospitalRoles(): array
    {
        return [
            'super_admin' => [
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'permissions' => ['*'], // All permissions
            ],
            'admin' => [
                'display_name' => 'Administrator',
                'description' => 'System administrator with most permissions',
                'permissions' => [
                    'admin.access', 'admin.dashboard',
                    'users.view', 'users.create', 'users.update', 'users.delete',
                    'patients.view', 'patients.create', 'patients.update', 'patients.delete',
                    'appointments.view', 'appointments.create', 'appointments.update', 'appointments.delete',
                    'medical_records.view', 'medical_records.create', 'medical_records.update',
                    'departments.view', 'departments.create', 'departments.update', 'departments.delete',
                    'settings.view', 'settings.update',
                    'reports.view', 'reports.create',
                ],
            ],
            'doctor' => [
                'display_name' => 'Doctor',
                'description' => 'Medical doctor with patient care permissions',
                'permissions' => [
                    'patients.view', 'patients.update',
                    'appointments.view', 'appointments.update',
                    'medical_records.view', 'medical_records.create', 'medical_records.update',
                    'prescriptions.view', 'prescriptions.create', 'prescriptions.update',
                    'lab_reports.view', 'lab_reports.create', 'lab_reports.update',
                ],
            ],
            'nurse' => [
                'display_name' => 'Nurse',
                'description' => 'Nursing staff with patient care permissions',
                'permissions' => [
                    'patients.view', 'patients.update',
                    'appointments.view', 'appointments.update',
                    'medical_records.view', 'medical_records.update',
                    'prescriptions.view',
                    'lab_reports.view',
                ],
            ],
            'receptionist' => [
                'display_name' => 'Receptionist',
                'description' => 'Front desk staff with appointment and patient management',
                'permissions' => [
                    'patients.view', 'patients.create', 'patients.update',
                    'appointments.view', 'appointments.create', 'appointments.update', 'appointments.delete',
                ],
            ],
            'pharmacist' => [
                'display_name' => 'Pharmacist',
                'description' => 'Pharmacy staff with prescription management',
                'permissions' => [
                    'patients.view',
                    'prescriptions.view', 'prescriptions.update',
                    'medical_records.view',
                ],
            ],
            'technician' => [
                'display_name' => 'Laboratory Technician',
                'description' => 'Lab staff with lab report management',
                'permissions' => [
                    'patients.view',
                    'lab_reports.view', 'lab_reports.create', 'lab_reports.update',
                    'medical_records.view',
                ],
            ],
        ];
    }
}
