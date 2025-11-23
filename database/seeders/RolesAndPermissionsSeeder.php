<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create permissions first
        Permission::createDefaults();

        // Create roles
        $hospitalRoles = Role::getHospitalRoles();

        foreach ($hospitalRoles as $roleName => $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                    'is_active' => true,
                ]
            );

            // Assign permissions to roles
            if (!empty($roleData['permissions'])) {
                if (in_array('*', $roleData['permissions'])) {
                    // Super admin gets all permissions
                    $allPermissions = Permission::all();
                    $role->permissions()->sync($allPermissions->pluck('id'));
                } else {
                    // Assign specific permissions
                    $permissions = Permission::whereIn('name', $roleData['permissions'])->get();
                    $role->permissions()->sync($permissions->pluck('id'));
                }
            }
        }

        $this->command->info('Roles and permissions created successfully!');
    }
}
