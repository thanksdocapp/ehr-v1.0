<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleMenuVisibility extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'role_menu_visibility';

    protected $fillable = [
        'role',
        'menu_type',
        'menu_key',
        'is_visible',
        'order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Check if a menu item is visible for a specific role
     */
    public static function isVisible(string $role, string $menuType, string $menuKey): bool
    {
        $visibility = static::where('role', $role)
            ->where('menu_type', $menuType)
            ->where('menu_key', $menuKey)
            ->first();
        
        // If no specific visibility setting exists, default to visible
        return $visibility ? $visibility->is_visible : true;
    }

    /**
     * Get all menu items with visibility for a specific role
     */
    public static function getMenuItemsForRole(string $role, string $menuType): array
    {
        return static::where('role', $role)
            ->where('menu_type', $menuType)
            ->where('is_visible', true)
            ->orderBy('order')
            ->get()
            ->toArray();
    }

    /**
     * Get ordered and visible menu items for a role
     * Priority: Role-specific order > Default order
     */
    public static function getOrderedMenuItemsForRole(string $role, string $menuType): array
    {
        // Get all available menu items (default structure)
        $allMenuItems = self::getAllMenuItems($menuType);
        
        // Get role-specific visibility and order settings
        $roleSettings = static::where('role', $role)
            ->where('menu_type', $menuType)
            ->get()
            ->keyBy('menu_key')
            ->toArray();
        
        // Get default visibility for role
        $defaultVisibility = self::getDefaultVisibilityByRole($role, $menuType);
        
        // Build menu items array with metadata
        $menuItems = [];
        
        foreach ($allMenuItems as $menuKey => $menuItem) {
            // Determine visibility
            $isVisible = true;
            if (isset($roleSettings[$menuKey])) {
                // Use role-specific visibility
                $isVisible = (bool)$roleSettings[$menuKey]['is_visible'];
            } elseif (isset($defaultVisibility[$menuKey])) {
                // Use default visibility for role
                $isVisible = (bool)$defaultVisibility[$menuKey];
            } elseif (isset($defaultVisibility['*'])) {
                // Use wildcard default (for admin role)
                $isVisible = (bool)$defaultVisibility['*'];
            }
            
            // Skip if not visible
            if (!$isVisible) {
                continue;
            }
            
            // Determine order (priority: role > default)
            $order = $menuItem['order'] ?? 999;
            if (isset($roleSettings[$menuKey])) {
                // Use role-specific order
                $order = (int)$roleSettings[$menuKey]['order'];
            }
            
            $menuItems[] = [
                'menu_key' => $menuKey,
                'label' => $menuItem['label'],
                'icon' => $menuItem['icon'] ?? null,
                'is_visible' => true, // Already filtered above
                'order' => $order,
                'is_custom' => $menuItem['is_custom'] ?? false, // Preserve custom flag
            ];
        }
        
        // Sort by order
        usort($menuItems, function($a, $b) {
            return $a['order'] <=> $b['order'];
        });
        
        return $menuItems;
    }

    /**
     * Save visibility settings for a role
     */
    public static function saveVisibility(array $visibilityData, string $role, string $menuType): void
    {
        // Delete existing visibility settings for this role and menu type
        static::where('role', $role)
            ->where('menu_type', $menuType)
            ->delete();
        
        // Insert new visibility settings
        foreach ($visibilityData as $item) {
            static::create([
                'role' => $role,
                'menu_type' => $menuType,
                'menu_key' => $item['menu_key'],
                'is_visible' => $item['is_visible'] ?? true,
                'order' => $item['order'] ?? 0,
            ]);
        }
    }

    /**
     * Get default menu items for a role
     */
    public static function getDefaultMenuItemsForRole(string $role, string $menuType): array
    {
        // Define default menu items based on role and menu type
        $allMenuItems = self::getAllMenuItems($menuType);
        
        // Return all items with default visibility based on role
        $defaultVisibility = self::getDefaultVisibilityByRole($role, $menuType);
        
        $items = [];
        foreach ($allMenuItems as $key => $item) {
            $items[] = [
                'menu_key' => $key,
                'label' => $item['label'],
                'icon' => $item['icon'] ?? null,
                'is_visible' => $defaultVisibility[$key] ?? true,
                'order' => $item['order'] ?? 0,
            ];
        }
        
        return $items;
    }

    /**
     * Get all available menu items for a menu type (including custom menu items)
     */
    public static function getAllMenuItems(string $menuType): array
    {
        // Base menu items
        if ($menuType === 'staff') {
            $items = [
                'dashboard' => ['label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'order' => 0],
                'patients' => ['label' => 'Patients', 'icon' => 'fa-users', 'order' => 1],
                'appointments' => ['label' => 'Appointments', 'icon' => 'fa-calendar-alt', 'order' => 2],
                'medical-records' => ['label' => 'Medical Records', 'icon' => 'fa-file-medical', 'order' => 3],
                'prescriptions' => ['label' => 'Prescriptions', 'icon' => 'fa-prescription-bottle-alt', 'order' => 4],
                'lab-reports' => ['label' => 'Lab Reports', 'icon' => 'fa-vial', 'order' => 5],
                'document-templates' => ['label' => 'Letters & Forms', 'icon' => 'fa-file-alt', 'order' => 6],
                'alerts' => ['label' => 'Patient Alerts', 'icon' => 'fa-exclamation-triangle', 'order' => 7],
                'billing' => ['label' => 'Billing', 'icon' => 'fa-file-invoice-dollar', 'order' => 8],
            ];
        } else {
            // Admin menu items
            $items = [
                'dashboard' => ['label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'order' => 0],
                'patient-management' => ['label' => 'Patient Management', 'icon' => 'fa-users', 'order' => 1],
                'doctors' => ['label' => 'Doctors', 'icon' => 'fa-user-md', 'order' => 2],
                'appointments' => ['label' => 'Appointments', 'icon' => 'fa-calendar-check', 'order' => 3],
                'medical-records' => ['label' => 'Medical Records', 'icon' => 'fa-file-medical-alt', 'order' => 4],
                'document-templates' => ['label' => 'Letters & Forms', 'icon' => 'fa-file-alt', 'order' => 5],
                'alerts' => ['label' => 'Patient Alerts', 'icon' => 'fa-exclamation-triangle', 'order' => 6],
                'billing-management' => ['label' => 'Billing Management', 'icon' => 'fa-file-invoice-dollar', 'order' => 7],
                'departments' => ['label' => 'Clinics', 'icon' => 'fa-building', 'order' => 8],
                'staff-management' => ['label' => 'Staffs Management', 'icon' => 'fa-users-cog', 'order' => 9],
                'communication' => ['label' => 'Communication', 'icon' => 'fa-paper-plane', 'order' => 10],
                'advanced-reports' => ['label' => 'Advanced Reports', 'icon' => 'fa-chart-line', 'order' => 11],
                'system-settings' => ['label' => 'System Settings', 'icon' => 'fa-cog', 'order' => 12],
            ];
        }
        
        // Add custom menu items
        try {
            $customItems = \App\Models\CustomMenuItem::getAllForMenuType($menuType);
            foreach ($customItems as $customItem) {
                $items[$customItem['menu_key']] = [
                    'label' => $customItem['label'],
                    'icon' => $customItem['icon'],
                    'order' => $customItem['order'] + 100, // Custom items appear after standard items
                    'is_custom' => true,
                ];
            }
        } catch (\Exception $e) {
            // If CustomMenuItem table doesn't exist yet, just continue
        }
        
        return $items;
    }

    /**
     * Get default visibility by role
     */
    public static function getDefaultVisibilityByRole(string $role, string $menuType): array
    {
        // Define default visibility rules per role
        $defaults = [
            'admin' => [
                // Admin sees everything by default
                'dashboard' => true,
                'patient-management' => true,
                'doctors' => true,
                'appointments' => true,
                'medical-records' => true,
                'document-templates' => true,
                'alerts' => true,
                'billing-management' => true,
                'departments' => true,
                'staff-management' => true,
                'communication' => true,
                'advanced-reports' => true,
                'system-settings' => true,
            ],
            'doctor' => [
                'dashboard' => true,
                'patients' => true,
                'appointments' => true,
                'medical-records' => true,
                'prescriptions' => true,
                'lab-reports' => true,
                'document-templates' => true,
                'alerts' => true,
                'billing' => false, // Doctors typically don't manage billing
            ],
            'nurse' => [
                'dashboard' => true,
                'patients' => true,
                'appointments' => true,
                'medical-records' => true,
                'prescriptions' => false,
                'lab-reports' => true,
                'document-templates' => true,
                'alerts' => true,
                'billing' => false,
            ],
            'receptionist' => [
                'dashboard' => true,
                'patients' => true,
                'appointments' => true,
                'medical-records' => false,
                'prescriptions' => false,
                'lab-reports' => false,
                'document-templates' => true,
                'alerts' => true,
                'billing' => true,
            ],
            'pharmacist' => [
                'dashboard' => true,
                'patients' => true,
                'appointments' => false,
                'medical-records' => true,
                'prescriptions' => true,
                'lab-reports' => false,
                'document-templates' => false,
                'alerts' => true,
                'billing' => false,
            ],
            'technician' => [
                'dashboard' => true,
                'patients' => true,
                'appointments' => false,
                'medical-records' => true,
                'prescriptions' => false,
                'lab-reports' => true,
                'document-templates' => false,
                'alerts' => true,
                'billing' => false,
            ],
            'staff' => [
                // Generic staff sees limited menu
                'dashboard' => true,
                'patients' => true,
                'appointments' => true,
                'medical-records' => false,
                'prescriptions' => false,
                'lab-reports' => false,
                'document-templates' => false,
                'alerts' => true,
                'billing' => false,
            ],
        ];

        $defaultVisibility = $defaults[$role] ?? $defaults['staff'];
        
        // Add custom menu items to default visibility (all visible by default)
        try {
            $customItems = \App\Models\CustomMenuItem::getAllForMenuType($menuType);
            foreach ($customItems as $customItem) {
                // Custom items default to visible for all roles (unless explicitly hidden)
                $defaultVisibility[$customItem['menu_key']] = true;
            }
        } catch (\Exception $e) {
            // If CustomMenuItem table doesn't exist yet, just continue
        }
        
        return $defaultVisibility;
    }
}
