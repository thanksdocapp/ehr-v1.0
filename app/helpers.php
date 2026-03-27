<?php

if (!function_exists('formatDateUk')) {
    /**
     * Format a date in UK style (day first): 31 Dec 2025
     *
     * @param \Carbon\Carbon|string|null $date
     * @return string
     */
    function formatDateUk($date)
    {
        if (!$date) {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($date)->format('j M Y');
        } catch (\Exception $e) {
            return (string) $date;
        }
    }
}

if (!function_exists('formatDateUkLong')) {
    /**
     * Format a date in UK style long: 31 December 2025
     *
     * @param \Carbon\Carbon|string|null $date
     * @return string
     */
    function formatDateUkLong($date)
    {
        if (!$date) {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($date)->format('j F Y');
        } catch (\Exception $e) {
            return (string) $date;
        }
    }
}

if (!function_exists('formatDateUkLongWeekday')) {
    /**
     * UK: weekday + full date (e.g. Monday, 31 December 2025). Uses en_GB for week start context.
     *
     * @param \Carbon\Carbon|string|null $date
     * @return string
     */
    function formatDateUkLongWeekday($date)
    {
        if (! $date) {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($date)->locale('en_GB')->isoFormat('dddd, D MMMM YYYY');
        } catch (\Exception $e) {
            return (string) $date;
        }
    }
}

if (!function_exists('formatDateUkSlash')) {
    /**
     * UK numeric date: dd/mm/yyyy
     *
     * @param \Carbon\Carbon|string|null $date
     * @return string
     */
    function formatDateUkSlash($date)
    {
        if (! $date) {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Exception $e) {
            return (string) $date;
        }
    }
}

if (!function_exists('formatDateTimeUk')) {
    /**
     * Format date and time in UK style: 31 Dec 2025, 14:30
     *
     * @param \Carbon\Carbon|string|null $date
     * @return string
     */
    function formatDateTimeUk($date)
    {
        if (!$date) {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($date)->format('j M Y, H:i');
        } catch (\Exception $e) {
            return (string) $date;
        }
    }
}

if (!function_exists('formatDateTimeUkAmPm')) {
    /**
     * Format date and time in UK style with AM/PM: 31 Dec 2025, 2:30 pm
     *
     * @param \Carbon\Carbon|string|null $date
     * @return string
     */
    function formatDateTimeUkAmPm($date)
    {
        if (!$date) {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($date)->format('j M Y g:i A');
        } catch (\Exception $e) {
            return (string) $date;
        }
    }
}

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
            // Try parsing dd/mm/yyyy format (UK format)
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
                return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
            }
            
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

if (!function_exists('parseDateTimeInput')) {
    /**
     * Parse datetime from dd/mm/yyyy HH:MM format to Y-m-d H:i:s for database
     *
     * @param string|null $datetime
     * @return string|null
     */
    function parseDateTimeInput($datetime)
    {
        if (!$datetime) {
            return null;
        }

        try {
            // Try parsing dd/mm/yyyy HH:MM format (UK format)
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2})$/', $datetime, $matches)) {
                return $matches[3] . '-' . $matches[2] . '-' . $matches[1] . ' ' . $matches[4] . ':' . $matches[5] . ':00';
            }
            
            // If already in Y-m-d H:i format, return as is (add seconds if needed)
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2})$/', $datetime)) {
                return $datetime . ':00';
            }
            
            // If already in Y-m-d H:i:s format, return as is
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})$/', $datetime)) {
                return $datetime;
            }
            
            // Try Carbon parsing
            return \Carbon\Carbon::parse($datetime)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return $datetime;
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

if (!function_exists('getClinicName')) {
    /**
     * Get the clinic/practice name from settings (not the EHR/app name).
     * Use for printed documents, letterheads, and patient-facing materials.
     *
     * @param string|null $default Default value if not set
     * @return string
     */
    function getClinicName($default = null)
    {
        try {
            $clinicName = \App\Models\Setting::get('clinic_name');
            if ($clinicName) {
                return $clinicName;
            }

            $clinicName = \App\Models\SiteSetting::get('clinic_name');
            if ($clinicName) {
                return $clinicName;
            }

            $hospitalName = \App\Models\SiteSetting::get('hospital_name');
            if ($hospitalName) {
                return $hospitalName;
            }

            return $default ?? getAppName();
        } catch (\Exception $e) {
            return $default ?? getAppName();
        }
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
            
            $userRole = strtolower(trim($user->role ?? 'admin'));
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

if (!function_exists('normalize_public_booking_address_fields')) {
    /**
     * Merge address line 1 + line 2 into a single patients.address value and normalise
     * town, county, postcode, and country for public / clinic booking flows.
     *
     * @param  array<string, mixed>  $input
     * @return array{address: string|null, city: string|null, state: string|null, postal_code: string|null, country: string|null}
     */
    function normalize_public_booking_address_fields(array $input): array
    {
        $line1 = trim((string) ($input['address'] ?? ''));
        $line2 = trim((string) ($input['address_line_2'] ?? ''));
        $merged = $line1;
        if ($line2 !== '') {
            $merged = $merged === '' ? $line2 : $merged."\n".$line2;
        }

        $city = trim((string) ($input['city'] ?? ''));
        $state = trim((string) ($input['state'] ?? ''));
        $postal = trim((string) ($input['postal_code'] ?? ''));
        if ($postal !== '') {
            $postal = strtoupper($postal);
        }
        $country = trim((string) ($input['country'] ?? ''));
        if ($country === '') {
            $country = 'United Kingdom';
        }

        return [
            'address' => $merged !== '' ? $merged : null,
            'city' => $city !== '' ? $city : null,
            'state' => $state !== '' ? $state : null,
            'postal_code' => $postal !== '' ? $postal : null,
            'country' => $country !== '' ? $country : null,
        ];
    }
}
