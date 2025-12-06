<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public'
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $cacheKey = 'setting_' . $key;
        
        return Cache::remember($cacheKey, 60 * 60, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value, $type = 'string', $group = null, $description = null, $isPublic = false)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
                'is_public' => $isPublic
            ]
        );

        // Clear cache
        Cache::forget('setting_' . $key);
        Cache::forget('settings_group_' . $group);
        
        return $setting;
    }

    /**
     * Get all settings by group
     */
    public static function getGroup($group)
    {
        $cacheKey = 'settings_group_' . $group;
        
        return Cache::remember($cacheKey, 60 * 60, function () use ($group) {
            $settings = self::where('group', $group)->get();
            
            return $settings->mapWithKeys(function ($setting) {
                return [$setting->key => self::castValue($setting->value, $setting->type)];
            });
        });
    }

    /**
     * Cast value to appropriate type
     */
    protected static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                // Handle string boolean values correctly
                if (is_string($value)) {
                    return in_array(strtolower($value), ['1', 'true', 'yes', 'on']);
                }
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache()
    {
        $groups = ['general', 'email', 'sms', 'security', 'maintenance', 'backup', 'appearance', 'alerts'];
        
        foreach ($groups as $group) {
            Cache::forget('settings_group_' . $group);
        }
        
        // Clear individual setting caches
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget('setting_' . $setting->key);
        }
    }
}
