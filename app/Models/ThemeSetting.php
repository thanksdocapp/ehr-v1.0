<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'preview_image',
        'primary_color',
        'secondary_color',
        'success_color',
        'danger_color',
        'warning_color',
        'info_color',
        'light_color',
        'dark_color',
        'accent_color',
        'text_color',
        'background_color',
        'card_background',
        'sidebar_color',
        'header_color',
        'footer_color',
        'primary_font',
        'secondary_font',
        'font_size_base',
        'border_radius',
        'box_shadow',
        'container_width',
        'is_default',
        'is_active'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean'
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

    // Helper methods
    public static function getDefault()
    {
        return static::where('is_default', true)->where('is_active', true)->first();
    }

    public static function getCurrent()
    {
        // Get current theme from site settings or return default
        $currentThemeSlug = SiteSetting::get('current_theme');
        
        if ($currentThemeSlug) {
            $theme = static::where('slug', $currentThemeSlug)->where('is_active', true)->first();
            if ($theme) {
                return $theme;
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
        // Remove default from all other themes
        static::where('is_default', true)->update(['is_default' => false]);
        
        // Set this theme as default
        $this->update(['is_default' => true]);
        
        // Update site setting
        SiteSetting::set('current_theme', $this->slug);
    }

    public function generateCssVariables()
    {
        return [
            '--primary-color' => $this->primary_color,
            '--secondary-color' => $this->secondary_color,
            '--success-color' => $this->success_color,
            '--danger-color' => $this->danger_color,
            '--warning-color' => $this->warning_color,
            '--info-color' => $this->info_color,
            '--light-color' => $this->light_color,
            '--dark-color' => $this->dark_color,
            '--accent-color' => $this->accent_color,
            '--text-color' => $this->text_color,
            '--background-color' => $this->background_color,
            '--card-background' => $this->card_background,
            '--sidebar-color' => $this->sidebar_color,
            '--header-color' => $this->header_color,
            '--footer-color' => $this->footer_color,
            '--primary-font' => $this->primary_font,
            '--secondary-font' => $this->secondary_font,
            '--font-size-base' => $this->font_size_base,
            '--border-radius' => $this->border_radius,
            '--box-shadow' => $this->box_shadow,
            '--container-width' => $this->container_width,
        ];
    }

    public function toCssString()
    {
        $variables = $this->generateCssVariables();
        $css = ":root {\n";
        
        foreach ($variables as $property => $value) {
            $css .= "    {$property}: {$value};\n";
        }
        
        $css .= "}\n";
        
        return $css;
    }
}
