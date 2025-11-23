<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated with admin guard
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access the admin panel.');
        }

        $user = Auth::guard('admin')->user();
        
        // Check if user has admin access (multiple ways to determine admin status)
        // Primary check: is_admin flag or role field - this is the most reliable check
        $hasAdminAccess = ($user->is_admin === true) || ($user->role === 'admin');
        
        // Secondary check: permission system (if exists and primary check failed)
        if (!$hasAdminAccess) {
            try {
                if (method_exists($user, 'hasPermission')) {
                    /** @phpstan-ignore-next-line */
                    $hasAdminAccess = $user->hasPermission('admin.access');
                }
            } catch (\Exception $e) {
                // Permission system not available, ignore
            }
        }
        
        // Tertiary check: roles relationship (if exists and previous checks failed)
        if (!$hasAdminAccess) {
            try {
                if (method_exists($user, 'roles')) {
                    $roles = $user->roles ?? null;
                    if ($roles && method_exists($roles, 'whereIn')) {
                        /** @phpstan-ignore-next-line */
                        $hasAdminAccess = $roles->whereIn('name', ['super_admin', 'admin'])->count() > 0;
                    }
                }
            } catch (\Exception $e) {
                // Roles relationship not available, ignore
            }
        }
        
        if (!$hasAdminAccess) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->with('error', 'Access denied. Admin privileges required. Please ensure your account has admin access.');
        }

        return $next($request);
    }
}
