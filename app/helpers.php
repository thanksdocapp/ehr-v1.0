<?php

if (!function_exists('formatDate')) {
    /**
     * Format a date according to hospital settings
     *
     * @param string|null $date
     * @param string|null $format Override default format
     * @return string
     */
    function formatDate($date, $format = null)
    {
        if (!$date) {
            return '';
        }

        $format = $format ?? config('hospital.date_format', 'd-m-Y');
        
        try {
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Format a datetime according to hospital settings
     *
     * @param string|null $datetime
     * @param string|null $format Override default format
     * @return string
     */
    function formatDateTime($datetime, $format = null)
    {
        if (!$datetime) {
            return '';
        }

        $format = $format ?? config('hospital.datetime_format', 'd-m-Y H:i');
        
        try {
            return \Carbon\Carbon::parse($datetime)->format($format);
        } catch (\Exception $e) {
            return $datetime;
        }
    }
}

if (!function_exists('formatTime')) {
    /**
     * Format a time according to hospital settings
     *
     * @param string|null $time
     * @param string|null $format Override default format
     * @return string
     */
    function formatTime($time, $format = null)
    {
        if (!$time) {
            return '';
        }

        $format = $format ?? config('hospital.time_format', 'H:i');
        
        try {
            return \Carbon\Carbon::parse($time)->format($format);
        } catch (\Exception $e) {
            return $time;
        }
    }
}

if (!function_exists('parseDateInput')) {
    /**
     * Parse date from dd-mm-yyyy format to Y-m-d for database
     *
     * @param string|null $date
     * @return string|null
     */
    function parseDateInput($date)
    {
        if (!$date) {
            return null;
        }

        try {
            // Try parsing dd-mm-yyyy format
            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $date, $matches)) {
                return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
            }
            
            // If already in Y-m-d format, return as is
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date)) {
                return $date;
            }
            
            // Try Carbon parsing
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('parseImportDate')) {
    /**
     * Parse date from MM/DD/YYYY format (enforced for CSV imports) to Y-m-d for database
     *
     * @param string|null $date Date string in MM/DD/YYYY format
     * @return string|null Date in Y-m-d format
     * @throws \Exception If date format is invalid
     */
    function parseImportDate($date)
    {
        if (empty($date)) {
            return null;
        }

        // Remove any whitespace
        $date = trim($date);
        
        // Try parsing MM/DD/YYYY format first (required format for imports)
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $matches)) {
            $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $day = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            
            // Validate date components
            if ((int)$month < 1 || (int)$month > 12) {
                throw new \Exception("Invalid month: {$month}. Month must be between 01 and 12.");
            }
            if ((int)$day < 1 || (int)$day > 31) {
                throw new \Exception("Invalid day: {$day}. Day must be between 01 and 31.");
            }
            if ((int)$year < 1900 || (int)$year > 2100) {
                throw new \Exception("Invalid year: {$year}. Year must be between 1900 and 2100.");
            }
            
            // Convert to Y-m-d format
            $convertedDate = "{$year}-{$month}-{$day}";
            
            // Validate the actual date (catches invalid dates like 02/30/2024)
            try {
                $carbonDate = \Carbon\Carbon::createFromFormat('Y-m-d', $convertedDate);
                return $convertedDate;
            } catch (\Exception $e) {
                throw new \Exception("Invalid date: {$date}. Date does not exist (e.g., 02/30/2024).");
            }
        }
        
        // If already in Y-m-d format, return as is (for backward compatibility)
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date)) {
            try {
                \Carbon\Carbon::createFromFormat('Y-m-d', $date);
                return $date;
            } catch (\Exception $e) {
                throw new \Exception("Invalid date format: {$date}. Expected MM/DD/YYYY format.");
            }
        }
        
        // Strict format enforcement - reject any other format
        throw new \Exception("Invalid date format: {$date}. Date must be in MM/DD/YYYY format (e.g., 01/15/2024).");
    }
}

if (!function_exists('formatDateForInput')) {
    /**
     * Format date for HTML5 date input (always Y-m-d format)
     * This is required because HTML5 date inputs must use yyyy-mm-dd format
     *
     * @param string|null $date
     * @return string|null
     */
    function formatDateForInput($date)
    {
        if (!$date) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('convertDateFormat')) {
    /**
     * Convert date from one format to another
     *
     * @param string|null $date
     * @param string $fromFormat Source format (e.g., 'Y-m-d' or 'd-m-Y')
     * @param string $toFormat Target format (e.g., 'd-m-Y' or 'Y-m-d')
     * @return string|null
     */
    function convertDateFormat($date, $fromFormat, $toFormat)
    {
        if (!$date) {
            return null;
        }

        try {
            return \Carbon\Carbon::createFromFormat($fromFormat, $date)->format($toFormat);
        } catch (\Exception $e) {
            try {
                // Try parsing as any format
                return \Carbon\Carbon::parse($date)->format($toFormat);
            } catch (\Exception $e2) {
                return $date;
            }
        }
    }
}

if (!function_exists('formatDoctorName')) {
    /**
     * Format doctor name with Dr. prefix, avoiding duplicates
     *
     * @param string|null $name The doctor's name (may already contain Dr.)
     * @param bool $includePrefix Whether to include Dr. prefix (default: true)
     * @return string
     */
    function formatDoctorName($name, $includePrefix = true)
    {
        if (!$name) {
            return 'Unknown';
        }

        // Remove any existing Dr. prefixes (case insensitive)
        $cleanedName = preg_replace('/^Dr\.\s*/i', '', trim($name));
        $cleanedName = preg_replace('/^Dr\s+/i', '', $cleanedName);
        
        // Add Dr. prefix if requested and name doesn't already start with it
        if ($includePrefix && !empty($cleanedName)) {
            return 'Dr. ' . $cleanedName;
        }
        
        return $cleanedName ?: 'Unknown';
    }
}

if (!function_exists('getAppName')) {
    /**
     * Get the application/brand name from settings
     * This replaces hardcoded branding throughout the app
     *
     * @param string|null $default Default value if not set
     * @return string
     */
    function getAppName($default = null)
    {
        try {
            $appName = \App\Models\Setting::get('app_name');
            if ($appName) {
                return $appName;
            }
            
            // Try SiteSetting as fallback
            $appName = \App\Models\SiteSetting::get('app_name');
            if ($appName) {
                return $appName;
            }
            
            // Try hospital_name from SiteSetting
            $hospitalName = \App\Models\SiteSetting::get('hospital_name');
            if ($hospitalName) {
                return $hospitalName;
            }
            
            // Fallback to config
            return $default ?? config('app.name', config('hospital.name', 'Hospital System'));
        } catch (\Exception $e) {
            return $default ?? config('app.name', 'Hospital System');
        }
    }
}

if (!function_exists('getAppVersion')) {
    /**
     * Get the application version from settings
     *
     * @param string|null $default Default value if not set
     * @return string
     */
    function getAppVersion($default = '1.0')
    {
        try {
            $version = \App\Models\Setting::get('app_version');
            if ($version) {
                return $version;
            }
            
            // Try SiteSetting as fallback
            $version = \App\Models\SiteSetting::get('app_version');
            if ($version) {
                return $version;
            }
            
            return $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('getCompanyName')) {
    /**
     * Get the company/author name from settings
     *
     * @param string|null $default Default value if not set
     * @return string
     */
    function getCompanyName($default = null)
    {
        try {
            $companyName = \App\Models\Setting::get('company_name');
            if ($companyName) {
                return $companyName;
            }
            
            // Try SiteSetting as fallback
            $companyName = \App\Models\SiteSetting::get('company_name');
            if ($companyName) {
                return $companyName;
            }
            
            return $default ?? getAppName();
        } catch (\Exception $e) {
            return $default ?? getAppName();
        }
    }
}

if (!function_exists('shouldShowPoweredBy')) {
    /**
     * Check if "Powered by" footer should be displayed
     *
     * @return bool
     */
    function shouldShowPoweredBy()
    {
        try {
            $show = \App\Models\Setting::get('show_powered_by', '1');
            return $show === '1' || $show === true || $show === 1;
        } catch (\Exception $e) {
            return true; // Default to showing
        }
    }
}

if (!function_exists('getPoweredByText')) {
    /**
     * Get the "Powered by" footer text
     *
     * @return string
     */
    function getPoweredByText()
    {
        if (!shouldShowPoweredBy()) {
            return '';
        }
        
        $appName = getAppName();
        $version = getAppVersion();
        
        return "Powered by <strong>{$appName} v{$version}</strong> - Advanced Administration Dashboard";
    }
}

if (!function_exists('getCopyrightText')) {
    /**
     * Get the copyright footer text
     *
     * @return string
     */
    function getCopyrightText()
    {
        $appName = getAppName();
        $year = date('Y');
        
        return "© {$year} {$appName}. All rights reserved.";
    }
}

if (!function_exists('getSidebarMenuItems')) {
    /**
     * Get ordered and visible sidebar menu items for the current user
     * Uses RoleMenuVisibility for per-role ordering and visibility
     *
     * @param string $menuType 'admin' or 'staff'
     * @return array
     */
    function getSidebarMenuItems(string $menuType = 'admin'): array
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return [];
            }
            
            $userRole = $user->role ?? 'admin';
            $isAdmin = $user->is_admin ?? false;
            
            // Admin users always see all menu items
            if ($isAdmin && $menuType === 'admin') {
                $userRole = 'admin';
            }
            
            return \App\Models\RoleMenuVisibility::getOrderedMenuItemsForRole($userRole, $menuType);
        } catch (\Exception $e) {
            // Fallback to default if there's any error
            return \App\Models\RoleMenuVisibility::getAllMenuItems($menuType);
        }
    }
}
