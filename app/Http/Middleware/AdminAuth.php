<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission = null): Response
    {
        if (!Auth::guard('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect()->route('admin.login');
        }

        $admin = Auth::guard('admin')->user();
        
        // Temporarily disabled is_active check
        // if (!$admin->is_active) {
        //     Auth::guard('admin')->logout();
        //     return redirect()->route('admin.login')
        //         ->with('error', 'Your account has been deactivated.');
        // }

        // Check specific permission if provided
        if ($permission && !$admin->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}