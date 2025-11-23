<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

class PaymentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByKey($query, $key)
    {
        return $query->where('key', $key);
    }

    // Accessors
    public function getValueAttribute($value)
    {
        return $this->castValue($value, $this->type);
    }

    // Mutators
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = $this->prepareValue($value, $this->type);
    }

    // Helper Methods
    private function castValue($value, $type)
    {
        if (is_null($value)) {
            return null;
        }

        return match($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            'encrypted' => Crypt::decryptString($value),
            default => $value
        };
    }

    private function prepareValue($value, $type)
    {
        return match($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) (int) $value,
            'float' => (string) (float) $value,
            'json' => json_encode($value),
            'encrypted' => Crypt::encryptString($value),
            default => (string) $value
        };
    }

    // Static Methods
    public static function get($key, $default = null)
    {
        return Cache::remember("payment_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::byKey($key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set($key, $value, $type = 'string', $category = 'general', $description = null, $isPublic = false)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'category' => $category,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        );

        Cache::forget("payment_setting_{$key}");
        Cache::forget('public_payment_settings');
        
        return $setting;
    }

    public static function getPublicSettings()
    {
        return Cache::remember('public_payment_settings', 3600, function () {
            return static::public()->get()->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            });
        });
    }

    public static function getByCategory($category)
    {
        return static::byCategory($category)->get()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value];
        });
    }

    public static function getDefaultSettings()
    {
        return [
            // General Settings
            'payment_currency' => ['value' => 'USD', 'type' => 'string', 'category' => 'currency', 'description' => 'Default payment currency', 'is_public' => true],
            'currency_symbol' => ['value' => '$', 'type' => 'string', 'category' => 'currency', 'description' => 'Currency symbol', 'is_public' => true],
            'multi_currency_support' => ['value' => true, 'type' => 'boolean', 'category' => 'features', 'description' => 'Enable multi-currency support', 'is_public' => true],
            
            // Payment Features
            'enable_apple_pay' => ['value' => true, 'type' => 'boolean', 'category' => 'features', 'description' => 'Enable Apple Pay', 'is_public' => true],
            'enable_google_pay' => ['value' => true, 'type' => 'boolean', 'category' => 'features', 'description' => 'Enable Google Pay', 'is_public' => true],
            'enable_payment_plans' => ['value' => true, 'type' => 'boolean', 'category' => 'features', 'description' => 'Enable payment plans', 'is_public' => true],
            'enable_auto_pay' => ['value' => true, 'type' => 'boolean', 'category' => 'features', 'description' => 'Enable automatic payments', 'is_public' => false],
            'enable_payment_retry' => ['value' => true, 'type' => 'boolean', 'category' => 'features', 'description' => 'Enable payment retry on failure', 'is_public' => false],
            
            // Payment Limits
            'min_payment_amount' => ['value' => 1.00, 'type' => 'float', 'category' => 'limits', 'description' => 'Minimum payment amount', 'is_public' => true],
            'max_payment_amount' => ['value' => 50000.00, 'type' => 'float', 'category' => 'limits', 'description' => 'Maximum payment amount', 'is_public' => true],
            'payment_timeout' => ['value' => 300, 'type' => 'integer', 'category' => 'limits', 'description' => 'Payment timeout in seconds', 'is_public' => false],
            
            // Security Settings
            'require_cvv' => ['value' => true, 'type' => 'boolean', 'category' => 'security', 'description' => 'Require CVV for card payments', 'is_public' => false],
            'enable_3d_secure' => ['value' => true, 'type' => 'boolean', 'category' => 'security', 'description' => 'Enable 3D Secure authentication', 'is_public' => false],
            'store_payment_methods' => ['value' => true, 'type' => 'boolean', 'category' => 'security', 'description' => 'Allow storing payment methods', 'is_public' => false],
            
            // Notifications
            'send_payment_confirmations' => ['value' => true, 'type' => 'boolean', 'category' => 'notifications', 'description' => 'Send payment confirmation emails', 'is_public' => false],
            'send_failure_notifications' => ['value' => true, 'type' => 'boolean', 'category' => 'notifications', 'description' => 'Send payment failure notifications', 'is_public' => false],
        ];
    }

    public static function initializeDefaults()
    {
        $defaults = static::getDefaultSettings();
        
        foreach ($defaults as $key => $config) {
            if (!static::where('key', $key)->exists()) {
                static::create([
                    'key' => $key,
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'category' => $config['category'],
                    'description' => $config['description'],
                    'is_public' => $config['is_public'],
                ]);
            }
        }
        
        Cache::flush();
    }
}
