<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class SavedReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'report_type',
        'query_config',
        'filters',
        'created_by',
        'is_public',
        'view_count',
        'last_viewed_at'
    ];

    protected $casts = [
        'query_config' => 'array',
        'filters' => 'array',
        'is_public' => 'boolean',
        'view_count' => 'integer',
        'last_viewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user who created this report
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get exports for this report
     */
    public function exports(): HasMany
    {
        return $this->hasMany(ReportExport::class, 'report_id');
    }

    /**
     * Get schedules for this report
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(ReportSchedule::class, 'report_id');
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
        $this->update(['last_viewed_at' => now()]);
    }

    /**
     * Get formatted created at date
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('M d, Y');
    }

    /**
     * Get human readable last viewed time
     */
    public function getLastViewedHumanAttribute(): string
    {
        return $this->last_viewed_at ? $this->last_viewed_at->diffForHumans() : 'Never';
    }

    /**
     * Scope for public reports
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for reports by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Scope for recent reports
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for most viewed reports
     */
    public function scopeMostViewed($query, $limit = 10)
    {
        return $query->orderBy('view_count', 'desc')->limit($limit);
    }
}
