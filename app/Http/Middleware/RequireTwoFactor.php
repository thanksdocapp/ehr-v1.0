<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use App\Services\TwoFactorAuthService;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    protected $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated (supports both default and admin guard)
        $user = Auth::user() ?? Auth::guard('admin')->user();
        
        // Skip if user is not authenticated
        if (!$user) {
            return $next($request);
        }

        // Skip 2FA check for certain routes (profile, settings, logout, etc.) to avoid redirect loops
        if ($this->shouldSkip2FACheck($request)) {
            return $next($request);
        }

        // Check if 2FA is required for this user
        if ($this->is2FARequired($user)) {
            // Check if user has 2FA enabled and confirmed using the service
            if (!$this->twoFactorService->requiresTwoFactor($user)) {
                // Get the setup route for this user type
                $setupRoute = $this->getRedirectRoute($user);
                $setupRouteName = $this->isAdmin($user) ? 'admin.two-factor.setup' : 'staff.two-factor.setup';
                
                // Get current route info
                $currentPath = $request->path();
                $currentRoute = $request->route()?->getName() ?? '';
                $isOnSetupPage = str_contains($currentPath, 'two-factor/setup') || 
                                str_contains($currentRoute, 'two-factor.setup');
                
                // Redirect to 2FA setup page
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'error' => 'Two-factor authentication is required. Please enable 2FA to continue.',
                        'require_2fa_setup' => true,
                        'redirect_url' => $setupRoute,
                        'is_forced' => true,
                    ], 403);
                }

                // If NOT on the setup page, redirect to it (enforce persistence)
                if (!$isOnSetupPage) {
                    // Set session flag to indicate forced setup
                    $request->session()->put('2fa_setup_required', true);
                    $request->session()->put('2fa_user_id', $user->id);
                    
                    return redirect($setupRoute)
                        ->with('warning', 'Two-factor authentication is required. Please complete 2FA setup to access the system.')
                        ->with('is_forced_setup', true);
                }
                
                // If on setup page, allow access but mark it as forced in session
                $request->session()->put('2fa_setup_required', true);
                $request->session()->put('2fa_user_id', $user->id);
            }
        }

        return $next($request);
    }

    /**
     * Check if 2FA check should be skipped for this route
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function shouldSkip2FACheck(Request $request): bool
    {
        // Allow access to profile pages, settings pages, and 2FA routes
        $allowedPaths = [
            'profile',
            'settings',
            'logout',
            'change-password',
            'two-factor',
            '2fa',
            'dashboard', // Allow dashboard access to prevent redirect loops
        ];

        $path = $request->path();
        $routeName = $request->route()?->getName() ?? '';

        // Check if the route or path contains allowed keywords
        foreach ($allowedPaths as $allowed) {
            if (str_contains($path, $allowed) || str_contains($routeName, $allowed)) {
                return true;
            }
        }

        // Allow AJAX/API requests to pass through
        if ($request->expectsJson() || $request->ajax()) {
            return true;
        }

        return false;
    }

    /**
     * Check if 2FA is required for the given user
     *
     * @param \App\Models\User $user
     * @return bool
     */
    protected function is2FARequired($user): bool
    {
        return $this->twoFactorService->isRequired($user);
    }

    /**
     * Check if user is an admin
     *
     * @param \App\Models\User $user
     * @return bool
     */
    protected function isAdmin($user): bool
    {
        return ($user->is_admin ?? false) || ($user->role === 'admin');
    }

    /**
     * Get the appropriate redirect route based on user context
     *
     * @param \App\Models\User $user
     * @return string
     */
    protected function getRedirectRoute($user): string
    {
        if ($this->isAdmin($user)) {
            return route('admin.two-factor.setup');
        }

        // For staff users, redirect to staff 2FA setup
        return route('staff.two-factor.setup');
    }
}

