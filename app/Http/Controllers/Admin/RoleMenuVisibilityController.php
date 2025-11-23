<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoleMenuVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleMenuVisibilityController extends Controller
{
    /**
     * Display the role-based menu visibility management page
     */
    public function index(Request $request)
    {
        $menuType = $request->get('type', 'admin'); // 'admin' or 'staff'
        $role = $request->get('role', 'admin'); // Selected role
        
        // Available roles
        $availableRoles = ['admin', 'doctor', 'nurse', 'receptionist', 'pharmacist', 'technician', 'staff'];
        
        // Get all available menu items (including custom items)
        $allMenuItems = RoleMenuVisibility::getAllMenuItems($menuType);
        
        // Get default visibility for the selected role
        $defaultVisibility = RoleMenuVisibility::getDefaultVisibilityByRole($role, $menuType);
        
        // Get saved visibility settings for the selected role
        $savedItems = RoleMenuVisibility::where('role', $role)
            ->where('menu_type', $menuType)
            ->get()
            ->keyBy('menu_key')
            ->toArray();
        
        // Build menu items array (including custom items)
        $menuItems = [];
        foreach ($allMenuItems as $menuKey => $item) {
            // Determine visibility
            $isVisible = true;
            if (isset($savedItems[$menuKey])) {
                // Use saved visibility
                $isVisible = $savedItems[$menuKey]['is_visible'];
            } elseif (isset($defaultVisibility[$menuKey])) {
                // Use default visibility for role
                $isVisible = $defaultVisibility[$menuKey];
            } elseif (isset($item['is_custom'])) {
                // Custom items default to visible (unless explicitly hidden)
                $isVisible = true;
            }
            
            // Determine order
            $order = $item['order'] ?? 999;
            if (isset($savedItems[$menuKey])) {
                $order = $savedItems[$menuKey]['order'];
            }
            
            $menuItems[] = [
                'menu_key' => $menuKey,
                'label' => $item['label'],
                'icon' => $item['icon'] ?? null,
                'is_visible' => $isVisible,
                'order' => $order,
                'is_custom' => $item['is_custom'] ?? false,
            ];
        }
        
        // Sort by order
        usort($menuItems, function($a, $b) {
            return ($a['order'] ?? 999) <=> ($b['order'] ?? 999);
        });
        
        return view('admin.role-menu-visibility.index', compact('menuItems', 'menuType', 'role', 'availableRoles'));
    }

    /**
     * Save the role-based menu visibility settings
     */
    public function store(Request $request)
    {
        $request->validate([
            'role' => 'required|in:admin,doctor,nurse,receptionist,pharmacist,technician,staff',
            'menu_type' => 'required|in:admin,staff',
            'menu_items' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            RoleMenuVisibility::saveVisibility(
                $request->menu_items,
                $request->role,
                $request->menu_type
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Menu visibility settings saved successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save menu visibility: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset visibility settings to default for a role
     */
    public function reset(Request $request)
    {
        $request->validate([
            'role' => 'required|in:admin,doctor,nurse,receptionist,pharmacist,technician,staff',
            'menu_type' => 'required|in:admin,staff',
        ]);
        
        DB::beginTransaction();
        try {
            RoleMenuVisibility::where('role', $request->role)
                ->where('menu_type', $request->menu_type)
                ->delete();
            
            DB::commit();
            
            return redirect()->route('admin.role-menu-visibility.index', [
                'role' => $request->role,
                'type' => $request->menu_type
            ])->with('success', 'Menu visibility reset to default successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('admin.role-menu-visibility.index', [
                'role' => $request->role,
                'type' => $request->menu_type
            ])->with('error', 'Failed to reset menu visibility: ' . $e->getMessage());
        }
    }
}
