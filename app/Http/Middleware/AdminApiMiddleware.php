<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class AdminApiMiddleware
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
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'timestamp' => now()->toISOString()
            ], 401);
        }

        // Check if user is admin/staff (User model, not Patient)
        if (!$user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin or staff account required.',
                'timestamp' => now()->toISOString()
            ], 403);
        }

        // Check if user account is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated. Please contact administrator.',
                'timestamp' => now()->toISOString()
            ], 403);
        }

        // Optional: Check if user has admin privileges for admin-only routes
        // if (!$user->is_admin) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Access denied. Administrator privileges required.',
        //         'timestamp' => now()->toISOString()
        //     ], 403);
        // }

        return $next($request);
    }
}
