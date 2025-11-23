<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class CheckFrontendEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip this middleware for admin, patient, and staff routes
        // These routes should be accessible regardless of frontend setting
        if ($request->is('admin/*') || 
            $request->is('patient/*') || 
            $request->is('staff/*') ||
            $request->is('login*') ||
            $request->is('logout*') ||
            $request->is('password/reset*') ||
            $request->is('password/email*') ||
            $request->is('install/*')) {
            return $next($request);
        }
        
        // Check if frontend/homepage is enabled
        $frontendEnabled = Setting::get('enable_frontend', '1');
        
        // If frontend is disabled, redirect to staff login page
        if ($frontendEnabled != '1') {
            return redirect()->route('login');
        }
        
        return $next($request);
    }
}
