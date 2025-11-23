<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',          // For compatibility
        'slug',
        'description',
        'short_description',
        'icon',
        'image',
        'image_url',      // For compatibility
        'department_id',
        'features',
        'duration',
        'price',
        'requirements',
        'procedure',
        'recovery_time',
        'is_emergency',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'features' => 'array',
        'requirements' => 'array',
        'procedure' => 'array',
        'is_emergency' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'sort_order' => 'integer'
    ];

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }

    public function scopeEmergency($query)
    {
        return $query->where('is_emergency', true);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Accessors for frontend compatibility
    public function getTitleAttribute()
    {
        return $this->attributes['title'] ?? $this->attributes['name'];
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getFormattedPriceAttribute()
    {
        return $this->price ? '$' . number_format($this->price, 2) : 'Contact for pricing';
    }

    public function getFeaturesListAttribute()
    {
        return $this->features ? implode(', ', $this->features) : '';
    }
}
