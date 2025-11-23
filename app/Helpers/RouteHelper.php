<?php

if (!function_exists('contextRoute')) {
    /**
     * Generate a context-aware route based on current request prefix
     *
     * @param string $routeName
     * @param array $parameters
     * @return string
     */
    function contextRoute($routeName, $parameters = [])
    {
        $request = app('request');
        $route = $request->route();
        
        // Determine route prefix based on current route - PRIORITIZE ROUTE OVER USER ROLE
        $routePrefix = null; // Don't set default yet
        
        if ($route) {
            $prefix = $route->getPrefix();
            $currentRouteName = $route->getName();
            
            // Check route name pattern FIRST (most reliable)
            if ($currentRouteName) {
                if (str_starts_with($currentRouteName, 'staff.')) {
                    $routePrefix = 'staff';
                } elseif (str_starts_with($currentRouteName, 'admin.')) {
                    $routePrefix = 'admin';
                }
            }
            
            // If route name didn't match, check route prefix
            if (!$routePrefix && $prefix) {
                if (str_starts_with($prefix, 'staff')) {
                    $routePrefix = 'staff';
                } elseif (str_starts_with($prefix, 'admin')) {
                    $routePrefix = 'admin';
                }
            }
        }
        
        // Only use user role as fallback if we couldn't determine from route
        // This ensures admin routes always use admin context, even if accessed by staff users
        if (!$routePrefix && auth()->check()) {
            $user = auth()->user();
            // If user is staff/doctor/nurse/etc (not admin), use staff routes
            if (isset($user->is_admin) && !$user->is_admin) {
                $routePrefix = 'staff';
            } elseif (isset($user->role) && in_array($user->role, ['staff', 'doctor', 'nurse', 'receptionist', 'pharmacist', 'technician', 'medical_assistant'])) {
                $routePrefix = 'staff';
            } else {
                $routePrefix = 'admin'; // Default to admin if user is admin or role not determined
            }
        }
        
        // Final fallback to admin
        if (!$routePrefix) {
            $routePrefix = 'admin';
        }
        
        // Build full route name
        $routeName = ltrim($routeName, '.');
        
        // Check if route name already starts with the prefix
        if (str_starts_with($routeName, $routePrefix . '.')) {
            // Route already has the prefix, use it as-is
            $fullRouteName = $routeName;
        } else {
            // Prepend the prefix
            $fullRouteName = $routePrefix . '.' . $routeName;
        }
        
        // Fix parameter names for route model binding
        // Convert patient_id to patient, medical_record_id to medical_record, etc.
        if (is_array($parameters)) {
            $fixedParameters = [];
            foreach ($parameters as $key => $value) {
                // Convert common parameter name patterns
                if ($key === 'patient_id' && (str_contains($fullRouteName, 'patients'))) {
                    $fixedParameters['patient'] = $value;
                } elseif ($key === 'medical_record_id' && (str_contains($fullRouteName, 'medical-records'))) {
                    $fixedParameters['medical_record'] = $value;
                } elseif ($key === 'appointment_id' && (str_contains($fullRouteName, 'appointments'))) {
                    $fixedParameters['appointment'] = $value;
                } else {
                    $fixedParameters[$key] = $value;
                }
            }
            $parameters = $fixedParameters;
        }
        
        return route($fullRouteName, $parameters);
    }
}

if (!function_exists('isContextRoute')) {
    /**
     * Check if current route matches a pattern with context-aware prefix
     *
     * @param string $pattern
     * @return bool
     */
    function isContextRoute($pattern)
    {
        $request = app('request');
        $route = $request->route();
        
        // Determine route prefix based on current route
        $routePrefix = 'admin'; // default
        if ($route) {
            $prefix = $route->getPrefix();
            if ($prefix && str_starts_with($prefix, 'staff')) {
                $routePrefix = 'staff';
            } else {
                // Also check route name pattern
                $routeName = $route->getName();
                if ($routeName && str_starts_with($routeName, 'staff.')) {
                    $routePrefix = 'staff';
                }
            }
        }
        
        // Fallback: Check authenticated user's role if route detection fails
        if ($routePrefix === 'admin' && auth()->check()) {
            $user = auth()->user();
            // If user is staff/doctor/nurse/etc (not admin), use staff routes
            if (isset($user->is_admin) && !$user->is_admin) {
                $routePrefix = 'staff';
            } elseif (isset($user->role) && in_array($user->role, ['staff', 'doctor', 'nurse', 'receptionist', 'pharmacist', 'technician', 'medical_assistant'])) {
                $routePrefix = 'staff';
            }
        }
        
        // Build full pattern
        $pattern = ltrim($pattern, '.');
        
        // Check if pattern already starts with the prefix
        if (str_starts_with($pattern, $routePrefix . '.')) {
            // Pattern already has the prefix, use it as-is
            $fullPattern = $pattern;
        } else {
            // Prepend the prefix
            $fullPattern = $routePrefix . '.' . $pattern;
        }
        
        return $request->routeIs($fullPattern);
    }
}

if (!function_exists('getContextPrefix')) {
    /**
     * Get the current context prefix (admin or staff)
     *
     * @return string
     */
    function getContextPrefix()
    {
        $request = app('request');
        $route = $request->route();
        
        if ($route) {
            $prefix = $route->getPrefix();
            if ($prefix && str_starts_with($prefix, 'staff')) {
                return 'staff';
            }
            // Also check route name pattern
            $routeName = $route->getName();
            if ($routeName && str_starts_with($routeName, 'staff.')) {
                return 'staff';
            }
        }
        
        // Fallback: Check authenticated user's role
        if (auth()->check()) {
            $user = auth()->user();
            // If user is staff/doctor/nurse/etc (not admin), use staff routes
            if (isset($user->is_admin) && !$user->is_admin) {
                return 'staff';
            } elseif (isset($user->role) && in_array($user->role, ['staff', 'doctor', 'nurse', 'receptionist', 'pharmacist', 'technician', 'medical_assistant'])) {
                return 'staff';
            }
        }
        
        return 'admin';
    }
}
