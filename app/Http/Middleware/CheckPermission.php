<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserActivity;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = auth()->user();

        if (!$user) {
            // Log unauthorized access attempt
            UserActivity::log([
                'user_id' => null,
                'action' => 'unauthorized_access',
                'description' => 'Unauthorized access attempt to protected route: ' . $request->path(),
                'severity' => 'high',
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return redirect()->route('admin.login');
        }

        // Check if user has any of the required permissions
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            // Log access denied
            UserActivity::log([
                'user_id' => $user->id,
                'action' => 'access_denied',
                'description' => 'Access denied to route: ' . $request->path() . '. Required permissions: ' . implode(', ', $permissions),
                'severity' => 'medium',
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden. You do not have the required permissions.',
                    'required_permissions' => $permissions
                ], 403);
            }

            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
