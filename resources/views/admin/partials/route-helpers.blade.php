{{-- Global Route Helpers for Admin Pages --}}
@php
    // Define global route helper functions if they don't exist
    if (!function_exists('contextRoute')) {
        function contextRoute($routeName, $parameters = []) {
            $routePrefix = str_starts_with(request()->route()->getPrefix() ?? '', 'staff') ? 'staff' : 'admin';
            $fullRouteName = $routePrefix . '.' . ltrim($routeName, '.');
            return route($fullRouteName, $parameters);
        }
    }
    
    if (!function_exists('isContextRoute')) {
        function isContextRoute($pattern) {
            $routePrefix = str_starts_with(request()->route()->getPrefix() ?? '', 'staff') ? 'staff' : 'admin';
            $fullPattern = $routePrefix . '.' . ltrim($pattern, '.');
            return request()->routeIs($fullPattern);
        }
    }
    
    // Set route prefix variable for backward compatibility
    $routePrefix = str_starts_with(request()->route()->getPrefix() ?? '', 'staff') ? 'staff' : 'admin';
@endphp
