<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'event_type',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the user that performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model (polymorphic relationship)
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for filtering by event type
     */
    public function scopeEventType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope for filtering by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope for filtering by model type
     */
    public function scopeModelType($query, $type)
    {
        return $query->where('auditable_type', $type);
    }

    /**
     * Get formatted event type
     */
    public function getFormattedEventTypeAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->event_type));
    }

    /**
     * Get short model name (e.g., Patient instead of App\Models\Patient)
     */
    public function getShortModelNameAttribute()
    {
        if (!$this->auditable_type) {
            return null;
        }
        
        $parts = explode('\\', $this->auditable_type);
        return end($parts);
    }

    /**
     * Get color badge for event type
     */
    public function getEventBadgeColorAttribute()
    {
        return match($this->event_type) {
            'login' => 'success',
            'logout' => 'secondary',
            'create' => 'primary',
            'update' => 'warning',
            'delete' => 'danger',
            'view' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get icon for event type
     */
    public function getEventIconAttribute()
    {
        return match($this->event_type) {
            'login' => 'sign-in-alt',
            'logout' => 'sign-out-alt',
            'create' => 'plus-circle',
            'update' => 'edit',
            'delete' => 'trash',
            'view' => 'eye',
            default => 'circle',
        };
    }

    /**
     * Static method to log an event
     */
    public static function logEvent($eventType, $description, $auditableType = null, $auditableId = null, $oldValues = null, $newValues = null, $user = null)
    {
        $logUser = $user ?? auth()->user();
        
        return self::create([
            'user_id' => $logUser?->id,
            'user_name' => $logUser?->name,
            'event_type' => $eventType,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ]);
    }

    /**
     * Log a login event
     */
    public static function logLogin($user)
    {
        return self::logEvent(
            'login',
            "User {$user->name} logged in",
            User::class,
            $user->id,
            null,
            null,
            $user
        );
    }

    /**
     * Log a logout event
     */
    public static function logLogout($user)
    {
        return self::logEvent(
            'logout',
            "User {$user->name} logged out",
            User::class,
            $user->id,
            null,
            null,
            $user
        );
    }
}
