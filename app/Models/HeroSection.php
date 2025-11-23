<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle', 
        'description',
        'background_image',
        'button_text',
        'button_url',
        'secondary_button_text',
        'secondary_button_url',
        'overlay_opacity',
        'text_color',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'overlay_opacity' => 'float',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the active hero section (there should only be one)
     */
    public static function getActive()
    {
        return static::active()->first() ?: static::getDefault();
    }

    /**
     * Get default hero section data
     */
    public static function getDefault()
    {
        return (object) [
            'title' => 'Secure Your Financial Future with Global Trust',
            'subtitle' => 'Trusted by 50,000+ Clients Worldwide',
            'description' => 'Transform your wealth management experience with our world-class investment solutions, inheritance fund protection, and personalized financial advisory services.',
            'background_image' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-4.0.3&auto=format&fit=crop&w=2940&q=80',
            'button_text' => 'Start Investing Today',
            'button_url' => '/register',
            'secondary_button_text' => 'Schedule Consultation',
            'secondary_button_url' => '/contact',
            'overlay_opacity' => 0.7,
            'text_color' => '#ffffff',
        ];
    }
}
