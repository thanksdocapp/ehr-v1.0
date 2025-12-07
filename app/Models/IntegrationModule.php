<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class IntegrationModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'provider',
        'type',
        'description',
        'logo',
        'website',
        'is_active',
        'is_configured',
        'config',
        'settings',
        'capabilities',
        'api_version',
        'environment',
        'last_sync_at',
        'last_error_at',
        'last_error_message',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_configured' => 'boolean',
        'config' => 'array',
        'settings' => 'array',
        'capabilities' => 'array',
        'last_sync_at' => 'datetime',
        'last_error_at' => 'datetime',
    ];

    /**
     * Integration types
     */
    const TYPE_LAB_TESTS = 'lab_tests';
    const TYPE_PRESCRIPTIONS = 'prescriptions';
    const TYPE_IMAGING = 'imaging';
    const TYPE_PHARMACY = 'pharmacy';
    const TYPE_OTHER = 'other';

    /**
     * Environments
     */
    const ENV_SANDBOX = 'sandbox';
    const ENV_PRODUCTION = 'production';

    /**
     * Get integration requests
     */
    public function requests()
    {
        return $this->hasMany(IntegrationRequest::class);
    }

    /**
     * Get webhooks
     */
    public function webhooks()
    {
        return $this->hasMany(IntegrationWebhook::class);
    }

    /**
     * Get config value (decrypted if sensitive)
     */
    public function getConfigValue(string $key, $default = null)
    {
        $config = $this->config ?? [];
        return $config[$key] ?? $default;
    }

    /**
     * Set config value
     */
    public function setConfigValue(string $key, $value): void
    {
        $config = $this->config ?? [];
        $config[$key] = $value;
        $this->config = $config;
    }

    /**
     * Get setting value
     */
    public function getSettingValue(string $key, $default = null)
    {
        $settings = $this->settings ?? [];
        return $settings[$key] ?? $default;
    }

    /**
     * Set setting value
     */
    public function setSettingValue(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
    }

    /**
     * Check if module has capability
     */
    public function hasCapability(string $capability): bool
    {
        $capabilities = $this->capabilities ?? [];
        return in_array($capability, $capabilities);
    }

    /**
     * Get the service instance for this module
     */
    public function getService()
    {
        $serviceClass = "App\\Services\\Integrations\\{$this->provider}Service";

        if (class_exists($serviceClass)) {
            return new $serviceClass($this);
        }

        return null;
    }

    /**
     * Scope: Active modules only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Configured modules only
     */
    public function scopeConfigured($query)
    {
        return $query->where('is_configured', true);
    }

    /**
     * Scope: By type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Check if module is ready (active and configured)
     */
    public function isReady(): bool
    {
        return $this->is_active && $this->is_configured;
    }

    /**
     * Log an error
     */
    public function logError(string $message): void
    {
        $this->update([
            'last_error_at' => now(),
            'last_error_message' => $message,
        ]);
    }

    /**
     * Clear error
     */
    public function clearError(): void
    {
        $this->update([
            'last_error_at' => null,
            'last_error_message' => null,
        ]);
    }

    /**
     * Update last sync time
     */
    public function updateLastSync(): void
    {
        $this->update(['last_sync_at' => now()]);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        if (!$this->is_configured) {
            return 'bg-warning';
        }

        if (!$this->is_active) {
            return 'bg-secondary';
        }

        if ($this->last_error_at && $this->last_error_at->isAfter(now()->subHours(24))) {
            return 'bg-danger';
        }

        return 'bg-success';
    }

    /**
     * Get status text
     */
    public function getStatusText(): string
    {
        if (!$this->is_configured) {
            return 'Not Configured';
        }

        if (!$this->is_active) {
            return 'Disabled';
        }

        if ($this->last_error_at && $this->last_error_at->isAfter(now()->subHours(24))) {
            return 'Error';
        }

        return 'Active';
    }

    /**
     * Get type label
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_LAB_TESTS => 'Lab Tests',
            self::TYPE_PRESCRIPTIONS => 'Prescriptions',
            self::TYPE_IMAGING => 'Imaging/Scans',
            self::TYPE_PHARMACY => 'Pharmacy',
            self::TYPE_OTHER => 'Other',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get type icon
     */
    public function getTypeIcon(): string
    {
        return match($this->type) {
            self::TYPE_LAB_TESTS => 'fa-vial',
            self::TYPE_PRESCRIPTIONS => 'fa-prescription',
            self::TYPE_IMAGING => 'fa-x-ray',
            self::TYPE_PHARMACY => 'fa-pills',
            self::TYPE_OTHER => 'fa-plug',
            default => 'fa-plug',
        };
    }
}
