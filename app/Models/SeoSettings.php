<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'default_title',
        'default_description',
        'default_keywords',
        'meta_keywords',
        'meta_description',
        'social_title',
        'social_description',
        'social_image',
        'og_title',
        'og_description',
        'og_image',
        'twitter_card',
        'twitter_site',
        'google_analytics_id',
        'google_search_console_id',
        'facebook_pixel_id',
        'robots_content',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Get the singleton instance
    public static function getInstance()
    {
        return static::first() ?? new static();
    }
}
