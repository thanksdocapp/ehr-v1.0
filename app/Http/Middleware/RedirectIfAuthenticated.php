<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            // Check if request is for admin login routes
            if ($request->is('admin/login*') || $request->is('admin/register*')) {
                // Only redirect if user is authenticated with admin guard
                if ($guard === 'admin' && Auth::guard('admin')->check()) {
                    return redirect()->route('admin.dashboard');
                }
                // If user is authenticated with a different guard, allow access to login
                continue;
            }
            
            // Check if request is for patient login routes
            if ($request->is('patient/login*') || $request->is('patient/register*')) {
                // Only redirect if user is authenticated with patient guard
                if ($guard === 'patient' && Auth::guard('patient')->check()) {
                    return redirect()->route('patient.dashboard');
                }
                // If user is authenticated with a different guard, allow access to login
                continue;
            }
            
            // For other routes, check if user is authenticated with the specified guard
            if (Auth::guard($guard)->check()) {
                // For admin routes, redirect to admin dashboard
                if ($request->is('admin/*')) {
                    return redirect()->route('admin.dashboard');
                }
                
                // For patient routes, redirect to patient dashboard
                if ($request->is('patient/*')) {
                    return redirect()->route('patient.dashboard');
                }
                
                // For other routes, redirect to home page
                return redirect()->route('home');
            }
        }

        return $next($request);
    }
}
