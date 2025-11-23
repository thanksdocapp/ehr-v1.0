<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }
        
        // Check if the request is for admin routes
        if ($request->is('admin/*')) {
            return route('admin.login');
        }
        
        // Check if the request is for patient routes
        if ($request->is('patient/*')) {
            return route('patient.login');
        }
        
        // Default fallback - this should not be reached in normal cases
        return route('patient.login');
    }
}
