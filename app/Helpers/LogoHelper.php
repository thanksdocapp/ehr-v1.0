<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('getLogo')) {
    /**
     * Get the appropriate logo based on mode
     * 
     * @param string $mode 'light' or 'dark'
     * @param string $fallback Default logo path if none is set
     * @return string
     */
    function getLogo($mode = 'light', $fallback = null)
    {
        $settingKey = $mode === 'dark' ? 'logo_dark' : 'logo_light';
        
        // Try to get logo from settings
        $logo = \App\Models\Setting::get($settingKey);
        
        if ($logo) {
            // Check if logo is stored in storage (uploaded logo)
            if (str_starts_with($logo, 'storage/') || str_starts_with($logo, 'uploads/')) {
                // Clean the path and use Storage facade
                $cleanPath = str_replace(['storage/', 'storage\\'], '', $logo);
                return Storage::disk('public')->url($cleanPath);
            } elseif (file_exists(public_path($logo))) {
                // Static asset file
                return asset($logo);
            }
        }
        
        // Fall back to default logos
        $defaultLogos = [
            'light' => 'assets/images/logos/logo-light.svg',
            'dark' => 'assets/images/logos/logo-dark.jpg'
        ];
        
        if (isset($defaultLogos[$mode]) && file_exists(public_path($defaultLogos[$mode]))) {
            return asset($defaultLogos[$mode]);
        }
        
        // Use provided fallback or default
        return $fallback ?: asset('assets/images/logo.png');
    }
}

if (!function_exists('getFavicon')) {
    /**
     * Get the favicon URL
     * 
     * @param string $fallback Default favicon path if none is set
     * @return string
     */
    function getFavicon($fallback = null)
    {
        $favicon = \App\Models\Setting::get('favicon');
        
        if ($favicon && file_exists(public_path($favicon))) {
            return asset($favicon);
        }
        
        // Fall back to default favicon
        if (file_exists(public_path('favicon.ico'))) {
            return asset('favicon.ico');
        }
        
        return $fallback ?: asset('assets/images/favicon.ico');
    }
}

if (!function_exists('getAppName')) {
    /**
     * Get the application name from settings
     * 
     * @param string $fallback Default app name if none is set
     * @return string
     */
    function getAppName($fallback = null)
    {
        $appName = \App\Models\Setting::get('app_name');
        
        return $appName ?: ($fallback ?: config('app.name', 'Global Trust Finance'));
    }
}

if (!function_exists('getPrimaryColor')) {
    /**
     * Get the primary color from settings
     * 
     * @param string $fallback Default primary color if none is set
     * @return string
     */
    function getPrimaryColor($fallback = null)
    {
        $primaryColor = \App\Models\Setting::get('primary_color');
        
        return $primaryColor ?: ($fallback ?: '#007bff');
    }
}

if (!function_exists('getSecondaryColor')) {
    /**
     * Get the secondary color from settings
     * 
     * @param string $fallback Default secondary color if none is set
     * @return string
     */
    function getSecondaryColor($fallback = null)
    {
        $secondaryColor = \App\Models\Setting::get('secondary_color');
        
        return $secondaryColor ?: ($fallback ?: '#6c757d');
    }
}
