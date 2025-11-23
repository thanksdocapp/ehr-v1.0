<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    /**
     * Handle an incoming request - Only allow installation routes if not installed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the app is already installed
        $installedFile = storage_path('installed');
        $isInstalled = File::exists($installedFile);
        
        // If requesting installation routes
        if ($request->is('install*')) {
            // If already installed, only allow access to final step and cleanup
            if ($isInstalled) {
                // Allow access to final step and cleanup even when installed
                if ($request->is('install/final') || $request->is('install/cleanup')) {
                    return $next($request);
                }
                // Block other installation routes
                return redirect('/');
            }
            // Not installed, allow access to installation
            return $next($request);
        }
        
        // If not requesting installation routes but app is not installed
        if (!$isInstalled) {
            return redirect('/install');
        }
        
        // App is installed and not requesting installation routes, proceed normally
        return $next($request);
    }
}