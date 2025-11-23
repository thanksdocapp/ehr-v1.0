<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'url',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'seo_score',
        'status',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'seo_score' => 'integer'
    ];

    // Scope for active pages
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for optimized pages (score >= 80)
    public function scopeOptimized($query)
    {
        return $query->where('seo_score', '>=', 80);
    }

    // Scope for pages needing work (score < 80)
    public function scopeNeedsWork($query)
    {
        return $query->where('seo_score', '<', 80);
    }

    // Get SEO score badge class
    public function getScoreBadgeClassAttribute()
    {
        if ($this->seo_score >= 80) {
            return 'good';
        } elseif ($this->seo_score >= 60) {
            return 'ok';
        } else {
            return 'poor';
        }
    }

    // Get status badge class
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'optimized' => 'status-optimized',
            'needs_work' => 'status-needs-work',
            'poor' => 'status-poor',
            default => 'status-needs-work'
        };
    }
}
