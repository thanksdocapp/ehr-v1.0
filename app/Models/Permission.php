<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'category',
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    /**
     * Get the users that have this permission directly.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions');
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get all hospital permissions organized by category.
     */
    public static function getHospitalPermissions(): array
    {
        return [
            'users' => [
                'users.view' => 'View Users',
                'users.create' => 'Create Users',
                'users.update' => 'Update Users',
                'users.delete' => 'Delete Users',
                'users.manage_roles' => 'Manage User Roles',
                'users.reset_password' => 'Reset User Passwords',
                'users.toggle_status' => 'Activate/Deactivate Users',
            ],
            'patients' => [
                'patients.view' => 'View Patients',
                'patients.create' => 'Create Patients',
                'patients.update' => 'Update Patients',
                'patients.delete' => 'Delete Patients',
                'patients.view_sensitive' => 'View Sensitive Patient Data',
            ],
            'appointments' => [
                'appointments.view' => 'View Appointments',
                'appointments.create' => 'Create Appointments',
                'appointments.update' => 'Update Appointments',
                'appointments.delete' => 'Delete Appointments',
                'appointments.cancel' => 'Cancel Appointments',
                'appointments.reschedule' => 'Reschedule Appointments',
            ],
            'medical_records' => [
                'medical_records.view' => 'View Medical Records',
                'medical_records.create' => 'Create Medical Records',
                'medical_records.update' => 'Update Medical Records',
                'medical_records.delete' => 'Delete Medical Records',
                'medical_records.view_all' => 'View All Patient Records',
            ],
            'prescriptions' => [
                'prescriptions.view' => 'View Prescriptions',
                'prescriptions.create' => 'Create Prescriptions',
                'prescriptions.update' => 'Update Prescriptions',
                'prescriptions.delete' => 'Delete Prescriptions',
                'prescriptions.dispense' => 'Dispense Prescriptions',
            ],
            'lab_reports' => [
                'lab_reports.view' => 'View Lab Reports',
                'lab_reports.create' => 'Create Lab Reports',
                'lab_reports.update' => 'Update Lab Reports',
                'lab_reports.delete' => 'Delete Lab Reports',
                'lab_reports.approve' => 'Approve Lab Reports',
            ],
            'departments' => [
                'departments.view' => 'View Departments',
                'departments.create' => 'Create Departments',
                'departments.update' => 'Update Departments',
                'departments.delete' => 'Delete Departments',
            ],
            'doctors' => [
                'doctors.view' => 'View Doctors',
                'doctors.create' => 'Create Doctors',
                'doctors.update' => 'Update Doctors',
                'doctors.delete' => 'Delete Doctors',
            ],
            'settings' => [
                'settings.view' => 'View Settings',
                'settings.update' => 'Update Settings',
                'settings.security' => 'Manage Security Settings',
                'settings.backup' => 'Manage Backups',
            ],
            'reports' => [
                'reports.view' => 'View Reports',
                'reports.create' => 'Create Reports',
                'reports.export' => 'Export Reports',
                'reports.analytics' => 'View Analytics',
            ],
            'system' => [
                'system.logs' => 'View System Logs',
                'system.maintenance' => 'System Maintenance',
                'system.users_activity' => 'View User Activity',
                'system.audit' => 'System Audit',
            ],
            'admin' => [
                'admin.access' => 'Access Admin Panel',
                'admin.dashboard' => 'View Admin Dashboard',
                'admin.manage_all' => 'Full Admin Management',
            ],
        ];
    }

    /**
     * Create default permissions.
     */
    public static function createDefaults()
    {
        $permissions = static::getHospitalPermissions();
        
        foreach ($permissions as $category => $categoryPermissions) {
            foreach ($categoryPermissions as $name => $displayName) {
                static::firstOrCreate(
                    ['name' => $name],
                    [
                        'display_name' => $displayName,
                        'category' => $category,
                        'description' => "Permission to {$displayName}",
                    ]
                );
            }
        }
    }
}
