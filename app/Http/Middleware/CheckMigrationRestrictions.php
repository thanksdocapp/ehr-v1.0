<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMigrationRestrictions
{
    /**
     * Routes allowed in migration mode (non-admin staff).
     * Patterns use dot-notation route names.
     * Exact matches and prefix matches (trailing dot) are supported.
     */
    private const ALLOWED_ROUTES = [
        // Dashboard
        'staff.dashboard',
        'staff.api.stats',
        'staff.toggle-dark-mode',

        // Doctor Services — view and copy links (full index page)
        'staff.doctor-services.index',
        'staff.doctor-services.generate-booking-link',

        // Patients — view only
        'staff.patients.index',
        'staff.patients.search',
        'staff.patients.show',
        'staff.patients.download-document',
        'staff.api.patients.search',

        // Appointments — view only
        'staff.appointments.index',
        'staff.appointments.show',
        'staff.appointments.calendar',
        'staff.api.appointments.calendar-data',
        'staff.api.doctors.available-slots',

        // Doctor Settlements — full access
        'staff.doctor-settlements.',

        // Booking Payments — view
        'staff.booking-payments.index',

        // Notifications — full access for usability
        'staff.notifications.',

        // Two-factor auth management
        'staff.two-factor.',

        // Service Orders — view only
        'staff.service-orders.index',
        'staff.service-orders.show',

        // Clinic booking requests — view only
        'staff.clinic-booking-requests.index',

        // Billing — view only
        'staff.billing.index',
        'staff.billing.show',

        // Custom menu reorder (Quick Links)
        'staff.custom-menu-items.reorder',

        // Feedback — view only
        'staff.feedback.index',
        'staff.feedback.show',

        // API suggestions (needed for various search fields)
        'staff.api.suggestions.',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (Setting::get('migration_mode', '0') != '1') {
            return $next($request);
        }

        $user = Auth::user();
        if (! $user) {
            return $next($request);
        }

        if ($user->is_admin ?? false) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (! $routeName) {
            return $next($request);
        }

        if ($this->isRouteAllowed($routeName)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'This feature is disabled during the migration to the new EHR.',
                'migration_mode' => true,
            ], 403);
        }

        return redirect()
            ->route('staff.dashboard')
            ->with('migration_blocked', true);
    }

    private function isRouteAllowed(string $routeName): bool
    {
        foreach (self::ALLOWED_ROUTES as $pattern) {
            if (str_ends_with($pattern, '.')) {
                if (str_starts_with($routeName, $pattern)) {
                    return true;
                }
            } elseif ($routeName === $pattern) {
                return true;
            }
        }

        return false;
    }
}
