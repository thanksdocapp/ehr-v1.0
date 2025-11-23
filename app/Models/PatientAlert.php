<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PatientAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'type',
        'code',
        'severity',
        'title',
        'description',
        'restricted',
        'active',
        'expires_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'restricted' => 'boolean',
        'active' => 'boolean',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the patient that owns the alert.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who created the alert.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the alert.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to filter active alerts.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope to filter expired alerts.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope to filter by severity.
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by code.
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope to filter restricted alerts.
     */
    public function scopeRestricted($query)
    {
        return $query->where('restricted', true);
    }

    /**
     * Scope to filter non-restricted alerts.
     */
    public function scopeNonRestricted($query)
    {
        return $query->where('restricted', false);
    }

    /**
     * Check if alert is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if alert is active (not expired and active flag is true).
     */
    public function isActive(): bool
    {
        return $this->active && !$this->isExpired();
    }

    /**
     * Get severity badge color.
     */
    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'secondary',
            'info' => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Get severity icon.
     */
    public function getSeverityIconAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'exclamation-triangle',
            'high' => 'exclamation-circle',
            'medium' => 'info-circle',
            'low' => 'info',
            'info' => 'info',
            default => 'info',
        };
    }

    /**
     * Get type icon.
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'clinical' => 'cross',
            'safeguarding' => 'shield-alt',
            'behaviour' => 'exclamation-triangle',
            'communication' => 'comments',
            'admin' => 'info-circle',
            'medication' => 'pills',
            default => 'info-circle',
        };
    }

    /**
     * Get category config from alert categories config.
     */
    public function getCategoryConfigAttribute(): ?array
    {
        $categories = config('alerts.categories', []);
        return $categories[$this->type][$this->code] ?? null;
    }
}
