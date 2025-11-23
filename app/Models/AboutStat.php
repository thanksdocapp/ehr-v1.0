<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle', 
        'description',
        'icon',
        'value',
        'prefix',
        'suffix',
        'color',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }

    // Accessors
    public function getFormattedValueAttribute()
    {
        return ($this->prefix ?? '') . $this->value . ($this->suffix ?? '');
    }

    public function getDisplayColorAttribute()
    {
        return $this->color ?? '#0d6efd';
    }
}
