<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * For booking (and homepage) requests, set session cookie to SameSite=None; Secure
 * so the cookie is sent when the page is embedded in an iframe on another site.
 * Without this, the browser does not send the session cookie on form submit → 419 Page Expired.
 */
class SetBookingSessionCookieAttributes
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isEmbeddablePath($request)) {
            // SameSite=None + Secure ensures cookie is sent when returning from payment gateway (Stripe, etc.)
            // Fixes 403 on first load after payment; refresh worked because cookie was then present
            if ($request->secure()) {
                config([
                    'session.same_site' => 'none',
                    'session.secure' => true,
                ]);
            }
            // Persist embed flag for booking paths
            if ($path = trim($request->path(), '/')) {
                if ($path === '' || $path === 'book' || str_starts_with($path, 'book/')) {
                    session(['embed' => $request->boolean('embed')]);
                }
            }
        }

        return $next($request);
    }

    private function isEmbeddablePath(Request $request): bool
    {
        $path = trim($request->path(), '/');
        if ($path === '' || $path === 'book' || str_starts_with($path, 'book/')) {
            return true;
        }
        // Post-confirm payment flow (redirect from booking) - must send cookie when returning from gateway
        if ($path === 'pay' || str_starts_with($path, 'pay/')) {
            return true;
        }
        // Patient billing - ensure session cookie sent when returning from Stripe/Paystack/etc.
        // Without SameSite=None, redirect from external gateway can drop the cookie → 403 on first load
        if (str_starts_with($path, 'patient/billing')) {
            return true;
        }
        // Generic payment success/callback routes
        if ($path === 'payment' || str_starts_with($path, 'payment/')) {
            return true;
        }
        return false;
    }
}
