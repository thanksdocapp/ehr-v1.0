<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_position',
        'customer_company',
        'customer_photo',
        'review_text',
        'rating',
        'sort_order',
        'is_featured',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'rating' => 'integer',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }


    // Accessors
    public function getPhotoUrlAttribute()
    {
        if ($this->customer_photo) {
            return asset('storage/' . $this->customer_photo);
        }
        return null;
    }
}
