<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
class SiteSetting extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }

    // Helper methods
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->where('is_active', true)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value, $label = null)
    {
        $label = $label ?: ucwords(str_replace(['_', '-'], ' ', $key));
        
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'label' => $label,
                'type' => 'text',
                'group' => 'general',
                'is_active' => true,
                'sort_order' => 0
            ]
        );
    }

    public static function getByGroup($group)
    {
        return static::where('group', $group)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('value', 'key');
    }

    public static function getSettings()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('value', 'key');
    }
}
