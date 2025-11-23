<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Patient;

class PatientApiMiddleware
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

        // Check if user is a patient
        if (!$user instanceof Patient) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Patient account required.',
                'timestamp' => now()->toISOString()
            ], 403);
        }

        // Check if patient account is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated. Please contact support.',
                'timestamp' => now()->toISOString()
            ], 403);
        }

        return $next($request);
    }
}
