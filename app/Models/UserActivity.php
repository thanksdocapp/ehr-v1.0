<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'session_id',
        'severity',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the model that the activity is related to.
     */
    public function model()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }
        return null;
    }

    /**
     * Scope to filter by action.
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by severity.
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get recent activities.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get severity badge class.
     */
    public function getSeverityBadgeAttribute()
    {
        return match($this->severity) {
            'low' => 'bg-success',
            'medium' => 'bg-warning',
            'high' => 'bg-danger',
            'critical' => 'bg-dark',
            default => 'bg-secondary'
        };
    }

    /**
     * Get severity icon.
     */
    public function getSeverityIconAttribute()
    {
        return match($this->severity) {
            'low' => 'fas fa-info-circle',
            'medium' => 'fas fa-exclamation-triangle',
            'high' => 'fas fa-exclamation-circle',
            'critical' => 'fas fa-skull-crossbones',
            default => 'fas fa-circle'
        };
    }

    /**
     * Get action icon.
     */
    public function getActionIconAttribute()
    {
        return match($this->action) {
            'login' => 'fas fa-sign-in-alt text-success',
            'logout' => 'fas fa-sign-out-alt text-muted',
            'create' => 'fas fa-plus text-success',
            'update' => 'fas fa-edit text-warning',
            'delete' => 'fas fa-trash text-danger',
            'view' => 'fas fa-eye text-info',
            'download' => 'fas fa-download text-primary',
            'export' => 'fas fa-file-export text-info',
            'import' => 'fas fa-file-import text-info',
            'password_change' => 'fas fa-key text-warning',
            'failed_login' => 'fas fa-ban text-danger',
            'pre_consultation_verified' => 'fas fa-clipboard-check text-success',
            default => 'fas fa-circle text-secondary'
        };
    }

    /**
     * Static method to log activity.
     */
    public static function log(array $data)
    {
        return static::create([
            'user_id' => $data['user_id'] ?? auth()->id(),
            'action' => $data['action'],
            'model_type' => $data['model_type'] ?? null,
            'model_id' => $data['model_id'] ?? null,
            'description' => $data['description'],
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'severity' => $data['severity'] ?? 'low',
        ]);
    }
}
