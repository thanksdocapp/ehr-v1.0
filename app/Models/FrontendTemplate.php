<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrontendTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'author',
        'template_path',
        'preview_image',
        'screenshots',
        'layouts',
        'components',
        'features',
        'color_schemes',
        'type',
        'style',
        'min_php_version',
        'laravel_version',
        'required_plugins',
        'is_default',
        'is_premium',
        'is_active',
        'price',
        'downloads',
        'rating',
        'sort_order'
    ];

    protected $casts = [
        'screenshots' => 'array',
        'layouts' => 'array',
        'components' => 'array',
        'features' => 'array',
        'color_schemes' => 'array',
        'required_plugins' => 'array',
        'is_default' => 'boolean',
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'downloads' => 'integer',
        'sort_order' => 'integer'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStyle($query, $style)
    {
        return $query->where('style', $style);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Accessors
    public function getPreviewImageUrlAttribute()
    {
        return $this->preview_image ? asset('assets/images/templates/' . $this->preview_image) : null;
    }

    public function getScreenshotUrlsAttribute()
    {
        if (!$this->screenshots) {
            return [];
        }

        return collect($this->screenshots)->map(function ($screenshot) {
            return asset('assets/images/templates/screenshots/' . $screenshot);
        })->toArray();
    }

    public function getTemplateDirectoryAttribute()
    {
        return resource_path('views/' . $this->template_path);
    }

    public function getIsFreeAttribute()
    {
        return !$this->is_premium;
    }

    // Helper methods
    public static function getDefault()
    {
        return static::where('is_default', true)->where('is_active', true)->first();
    }

    public static function getCurrent()
    {
        $currentTemplateSlug = SiteSetting::get('current_frontend_template');
        
        if ($currentTemplateSlug) {
            $template = static::where('slug', $currentTemplateSlug)->where('is_active', true)->first();
            if ($template) {
                return $template;
            }
        }

        return static::getDefault();
    }

    public static function getActive()
    {
        return static::getCurrent();
    }

    public function setAsDefault()
    {
        // Remove default from all other templates
        static::where('is_default', true)->update(['is_default' => false]);
        
        // Set this template as default
        $this->update(['is_default' => true]);
        
        // Update site setting
        SiteSetting::set('current_frontend_template', $this->slug);
    }

    public function incrementDownloads()
    {
        $this->increment('downloads');
    }

    public function updateRating($newRating)
    {
        // Simple rating update - in real app, you'd calculate average from reviews
        $this->update(['rating' => $newRating]);
    }

    public function isCompatibleWith($phpVersion, $laravelVersion)
    {
        return version_compare($phpVersion, $this->min_php_version, '>=') &&
               version_compare($laravelVersion, $this->laravel_version, '>=');
    }

    public function hasLayout($layoutName)
    {
        return in_array($layoutName, $this->layouts ?? []);
    }

    public function hasComponent($componentName)
    {
        return in_array($componentName, $this->components ?? []);
    }

    public function hasFeature($featureName)
    {
        return in_array($featureName, $this->features ?? []);
    }

    public function supportsColorScheme($colorScheme)
    {
        return in_array($colorScheme, $this->color_schemes ?? []);
    }
}
