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
            config([
                'session.same_site' => 'none',
                'session.secure' => true,
            ]);
            // Persist embed flag so layout keeps desktop viewport after redirects (forms don't pass ?embed=1)
            session(['embed' => $request->boolean('embed')]);
        }

        return $next($request);
    }

    private function isEmbeddablePath(Request $request): bool
    {
        $path = trim($request->path(), '/');
        return $path === '' || $path === 'book' || str_starts_with($path, 'book/');
    }
}
