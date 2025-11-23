<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyFeaturesSection extends Model
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
            'section_title' => 'Key Features',
            'section_subtitle' => 'Advanced features that set us apart',
            'section_description' => null,
            'main_image' => null,
            'background_color' => '#f8f9fa',
        ];
    }
}
