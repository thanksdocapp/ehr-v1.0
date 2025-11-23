<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_title',
        'section_subtitle', 
        'section_description',
        'main_image',
        'background_color',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the active section (there should only be one)
     */
    public static function getActive()
    {
        return static::active()->first() ?: static::getDefault();
    }

    /**
     * Get default section data
     */
    public static function getDefault()
    {
        return (object) [
            'section_title' => 'Our Mission',
            'section_subtitle' => null,
            'section_description' => 'To provide a secure financial environment where individuals, families, and institutions can preserve and grow their assets with confidence.',
            'main_image' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80',
            'background_color' => '#ffffff',
        ];
    }
}
