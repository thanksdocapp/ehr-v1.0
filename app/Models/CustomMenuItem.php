<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CustomMenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_key',
        'label',
        'icon',
        'url',
        'target',
        'order',
        'is_active',
        'menu_type',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get active custom menu items for a menu type and role, ordered by order
     * Respects RoleMenuVisibility settings
     */
    public static function getActiveForMenuTypeAndRole(string $menuType = 'staff', string $role = 'staff'): \Illuminate\Database\Eloquent\Collection
    {
        try {
            $items = static::where('menu_type', $menuType)
                ->where('is_active', true)
                ->orderBy('order')
                ->orderBy('label')
                ->get();
            
            // Filter by role visibility if RoleMenuVisibility exists and menu_key is available
            return $items->filter(function ($item) use ($role, $menuType) {
                // If menu_key doesn't exist, generate it temporarily for checking
                if (!$item->menu_key) {
                    return false; // Skip items without menu_key
                }
                
                // Check if visible for this role
                return \App\Models\RoleMenuVisibility::isVisible($role, $menuType, $item->menu_key);
            });
        } catch (\Exception $e) {
            // If there's any error (e.g., table doesn't exist), return empty collection
            return collect([]);
        }
    }
    
    /**
     * Get active custom menu items for a menu type, ordered by order (legacy method)
     */
    public static function getActiveForMenuType(string $menuType = 'staff'): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('menu_type', $menuType)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('label')
            ->get();
    }
    
    /**
     * Get all custom menu items formatted for RoleMenuVisibility integration
     */
    public static function getAllForMenuType(string $menuType = 'staff'): array
    {
        try {
            if (!Schema::hasTable('custom_menu_items')) {
                return [];
            }
            
            $items = static::where('menu_type', $menuType)
                ->orderBy('order')
                ->orderBy('label')
                ->get();
            
            return $items->map(function ($item) {
                // Generate menu_key if it doesn't exist (for legacy items)
                if (!$item->menu_key) {
                    $baseKey = 'custom-' . strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($item->label)));
                    if (empty($baseKey) || $baseKey === 'custom-') {
                        $baseKey = 'custom-link-' . $item->id;
                    }
                    $item->menu_key = $baseKey;
                    // Try to save it (but don't fail if it errors)
                    try {
                        $item->save();
                    } catch (\Exception $e) {
                        // Ignore save errors for now
                    }
                }
                
                return [
                    'menu_key' => $item->menu_key,
                    'label' => $item->label,
                    'icon' => $item->icon ?? 'fa-external-link-alt',
                    'order' => $item->order,
                    'is_custom' => true,
                ];
            })
            ->toArray();
        } catch (\Exception $e) {
            // If there's any error, return empty array
            return [];
        }
    }

    /**
     * Scope: Active items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: For menu type
     */
    public function scopeForMenuType($query, string $menuType)
    {
        return $query->where('menu_type', $menuType);
    }

    /**
     * Scope: Ordered
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('label');
    }
}

