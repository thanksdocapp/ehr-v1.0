<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalled
{
    /**
     * Handle an incoming request - Only allow access if system is installed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $installedFile = storage_path('installed');
        
        // If system is not installed, redirect to installation
        if (!File::exists($installedFile)) {
            return redirect()->route('install.index');
        }
        
        // Check if .env file exists and has required configuration
        if (!file_exists(base_path('.env'))) {
            // Missing .env file, redirect to installation
            File::delete($installedFile);
            return redirect()->route('install.index');
        }
        
        // Check if APP_KEY is set
        try {
            $appKey = config('app.key');
            if (empty($appKey)) {
                // Missing APP_KEY, redirect to installation
                File::delete($installedFile);
                return redirect()->route('install.index');
            }
        } catch (\Exception $e) {
            // Error reading config, redirect to installation
            File::delete($installedFile);
            return redirect()->route('install.index');
        }
        
        return $next($request);
    }
}
