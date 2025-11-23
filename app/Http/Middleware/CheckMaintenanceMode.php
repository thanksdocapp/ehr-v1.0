<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip maintenance check for admin routes
        if ($request->is('admin') || $request->is('admin/*')) {
            return $next($request);
        }

        // Skip maintenance check for installation routes
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        // Skip maintenance check for API routes that need to work during maintenance
        if ($request->is('api/health') || $request->is('api/status')) {
            return $next($request);
        }

        // Skip maintenance check if system is not installed yet
        if (!file_exists(storage_path('installed'))) {
            return $next($request);
        }

        try {
            // Check if database is available before trying to access settings
            \DB::connection()->getPdo();
            
            // Check if maintenance mode is enabled
            $maintenanceMode = Setting::get('maintenance_mode', false, 'boolean', 'maintenance');
            
            if ($maintenanceMode) {
                // Get allowed IPs
                $allowedIps = Setting::get('allowed_ips', '', 'string', 'maintenance');
                $allowedIpArray = array_filter(array_map('trim', explode("\n", $allowedIps)));
                
                // Get client IP
                $clientIp = $request->ip();
                
                // Check if user is admin
                if (Auth::check() && Auth::user()->hasRole('admin')) {
                    return $next($request);
                }
                
                // Check if IP is in allowed list
                if (!empty($allowedIpArray) && $this->isIpAllowed($clientIp, $allowedIpArray)) {
                    return $next($request);
                }
                
                // Show maintenance page
                return $this->renderMaintenancePage($request);
            }
        } catch (\Exception $e) {
            // If there's an error checking maintenance mode (including DB connection), continue normally
            // This allows installation to proceed even if database isn't configured yet
        }

        return $next($request);
    }

    /**
     * Check if IP is in the allowed list
     */
    private function isIpAllowed($clientIp, $allowedIps)
    {
        foreach ($allowedIps as $allowedIp) {
            // Handle CIDR notation
            if (strpos($allowedIp, '/') !== false) {
                if ($this->ipInRange($clientIp, $allowedIp)) {
                    return true;
                }
            } else {
                // Handle exact IP match
                if ($clientIp === $allowedIp) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    private function ipInRange($ip, $range)
    {
        if (strpos($range, '/') == false) {
            $range .= '/32';
        }

        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal = ~ $wildcard_decimal;

        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    }

    /**
     * Render the maintenance page
     */
    private function renderMaintenancePage(Request $request)
    {
        // Get maintenance settings
        $maintenanceTitle = Setting::get('maintenance_title', 'Site Under Maintenance', 'string', 'maintenance');
        $maintenanceMessage = Setting::get('maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon.', 'string', 'maintenance');
        $retryAfter = Setting::get('maintenance_retry_after', 60, 'integer', 'maintenance');

        // Set HTTP status and headers
        $response = response()->view('maintenance', [
            'title' => $maintenanceTitle,
            'message' => $maintenanceMessage,
            'retryAfter' => $retryAfter
        ], 503);

        $response->header('Retry-After', $retryAfter * 60); // Convert minutes to seconds

        return $response;
    }
}
