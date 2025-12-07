<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class DocumentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Cache key prefix.
     */
    protected const CACHE_PREFIX = 'document_setting_';

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, mixed $value, ?string $type = null): void
    {
        $setting = static::where('key', $key)->first();

        if ($setting) {
            $setting->update([
                'value' => self::serializeValue($value, $type ?? $setting->type),
            ]);
        } else {
            static::create([
                'key' => $key,
                'value' => self::serializeValue($value, $type ?? 'string'),
                'type' => $type ?? 'string',
            ]);
        }

        // Clear cache
        Cache::forget(self::CACHE_PREFIX . $key);
    }

    /**
     * Get all settings in a group.
     */
    public static function getGroup(string $group): array
    {
        $settings = static::where('group', $group)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = self::castValue($setting->value, $setting->type);
        }

        return $result;
    }

    /**
     * Get all settings grouped by their group.
     */
    public static function getAllGrouped(): array
    {
        $settings = static::all();
        $result = [];

        foreach ($settings as $setting) {
            if (!isset($result[$setting->group])) {
                $result[$setting->group] = [];
            }
            $result[$setting->group][$setting->key] = [
                'value' => self::castValue($setting->value, $setting->type),
                'type' => $setting->type,
                'description' => $setting->description,
            ];
        }

        return $result;
    }

    /**
     * Cast value to proper type.
     */
    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            'array' => is_array($value) ? $value : json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Serialize value for storage.
     */
    protected static function serializeValue(mixed $value, string $type): string
    {
        if ($type === 'boolean') {
            return $value ? 'true' : 'false';
        }

        if ($type === 'json' || $type === 'array') {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * Clear all settings cache.
     */
    public static function clearCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget(self::CACHE_PREFIX . $setting->key);
        }
    }

    /**
     * Get setting groups.
     */
    public static function getGroups(): array
    {
        return [
            'general' => [
                'label' => 'General Settings',
                'icon' => 'fa-cog',
                'description' => 'General document management settings',
            ],
            'templates' => [
                'label' => 'Template Settings',
                'icon' => 'fa-file-alt',
                'description' => 'Settings for document templates',
            ],
            'pdf' => [
                'label' => 'PDF Generation',
                'icon' => 'fa-file-pdf',
                'description' => 'PDF generation and formatting options',
            ],
            'email' => [
                'label' => 'Email Settings',
                'icon' => 'fa-envelope',
                'description' => 'Email delivery and tracking settings',
            ],
        ];
    }
}
