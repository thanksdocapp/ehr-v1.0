<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_name',
        'title',
        'subtitle',
        'description',
        'image',
        'data',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'data' => 'json',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }

    /**
     * Get section data by section name
     */
    public static function getSectionByName($sectionName)
    {
        return static::where('section_name', $sectionName)
                    ->where('is_active', true)
                    ->first();
    }

    /**
     * Get section title by section name with fallback
     */
    public static function getSectionTitle($sectionName, $fallback = '')
    {
        $section = static::getSectionByName($sectionName);
        return $section ? $section->title : $fallback;
    }

    /**
     * Get section subtitle by section name with fallback
     */
    public static function getSectionSubtitle($sectionName, $fallback = '')
    {
        $section = static::getSectionByName($sectionName);
        return $section ? $section->subtitle : $fallback;
    }
}
