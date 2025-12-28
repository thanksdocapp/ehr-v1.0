<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'priority',
        'is_active',
        'starts_at',
        'expires_at',
        'target_roles',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'target_roles' => 'array',
    ];

    /**
     * Get the user who created this notice
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get active notices
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Scope to get notices visible to a specific user role
     */
    public function scopeForRole($query, $role)
    {
        return $query->where(function($q) use ($role) {
            $q->whereNull('target_roles')
              ->orWhereJsonContains('target_roles', $role)
              ->orWhere('target_roles', '[]'); // Also match empty array
        });
    }

    /**
     * Check if notice is currently active
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if notice is visible to a specific role
     */
    public function isVisibleToRole($role): bool
    {
        if (!$this->target_roles || empty($this->target_roles)) {
            return true; // Visible to all if no target roles specified
        }

        return in_array($role, $this->target_roles);
    }

    /**
     * Get priority color for Bootstrap
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'secondary',
            default => 'info',
        };
    }

    /**
     * Get type icon
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'info' => 'fa-info-circle',
            'warning' => 'fa-exclamation-triangle',
            'success' => 'fa-check-circle',
            'danger' => 'fa-exclamation-circle',
            default => 'fa-info-circle',
        };
    }
}
