<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomMenuItemController extends Controller
{
    /**
     * Display a listing of custom menu items
     */
    public function index(Request $request)
    {
        try {
            $menuType = $request->get('type', 'staff'); // 'staff' or 'admin'
            
            $menuItems = CustomMenuItem::where('menu_type', $menuType)
                ->orderBy('order')
                ->orderBy('label')
                ->get();

            return view('admin.custom-menu-items.index', compact('menuItems', 'menuType'));
        } catch (\Exception $e) {
            \Log::error('Custom Menu Items Index Error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            
            return redirect()
                ->route('admin.settings.index')
                ->with('error', 'Failed to load custom menu items: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new custom menu item
     */
    public function create(Request $request)
    {
        $menuType = $request->get('type', 'staff');
        return view('admin.custom-menu-items.create', compact('menuType'));
    }

    /**
     * Store a newly created custom menu item
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'label' => 'required|string|max:255',
                'icon' => 'nullable|string|max:255',
                'url' => 'required|url|max:500',
                'target' => 'required|in:_blank,_self',
                'order' => 'nullable|integer|min:0',
                'menu_type' => 'required|in:staff,admin',
                'description' => 'nullable|string|max:500',
            ]);

            // Handle checkbox: unchecked checkboxes don't send a value
            $validated['is_active'] = $request->has('is_active') && $request->input('is_active') !== '0';
            $validated['order'] = isset($validated['order']) ? (int)$validated['order'] : 0;
            
            // Generate unique menu_key
            $baseKey = 'custom-' . strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($validated['label'])));
            if (empty($baseKey) || $baseKey === 'custom-') {
                $baseKey = 'custom-link-' . time();
            }
            
            $counter = 1;
            $menuKey = $baseKey;
            while (CustomMenuItem::where('menu_key', $menuKey)->exists()) {
                $menuKey = $baseKey . '-' . $counter;
                $counter++;
            }
            $validated['menu_key'] = $menuKey;

            CustomMenuItem::create($validated);

            return redirect()
                ->route('admin.custom-menu-items.index', ['type' => $validated['menu_type']])
                ->with('success', 'Custom menu item created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Custom Menu Item Creation Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Failed to create custom menu item: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing a custom menu item
     */
    public function edit(CustomMenuItem $customMenuItem)
    {
        return view('admin.custom-menu-items.edit', compact('customMenuItem'));
    }

    /**
     * Update the specified custom menu item
     */
    public function update(Request $request, CustomMenuItem $customMenuItem)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'url' => 'required|url|max:500',
            'target' => 'required|in:_blank,_self',
            'order' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        // Handle checkbox: unchecked checkboxes don't send a value
        $validated['is_active'] = $request->has('is_active') && $request->input('is_active') !== '0';
        $validated['order'] = $validated['order'] ?? 0;

        $customMenuItem->update($validated);

        return redirect()
            ->route('admin.custom-menu-items.index', ['type' => $customMenuItem->menu_type])
            ->with('success', 'Custom menu item updated successfully!');
    }

    /**
     * Remove the specified custom menu item
     */
    public function destroy(CustomMenuItem $customMenuItem)
    {
        $menuType = $customMenuItem->menu_type;
        $customMenuItem->delete();

        return redirect()
            ->route('admin.custom-menu-items.index', ['type' => $menuType])
            ->with('success', 'Custom menu item deleted successfully!');
    }
}

