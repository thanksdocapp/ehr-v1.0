<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows the public booking flow to be embedded in iframes (e.g. WordPress).
 * Sets headers so /book/* pages can be framed by any site; other routes keep default strict headers.
 */
class AllowBookingEmbed
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Allow this response to be embedded in iframes (WordPress, other sites)
        $response->headers->remove('X-Frame-Options');
        $response->headers->set('Content-Security-Policy', $this->cspWithFrameAncestors(), true);

        return $response;
    }

    /**
     * CSP that allows framing by any origin (for embed). Keeps other directives strict.
     */
    private function cspWithFrameAncestors(): string
    {
        return "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.quilljs.com; " .
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.quilljs.com; " .
            "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com data:; " .
            "img-src 'self' data: https:; connect-src 'self' https:; " .
            "frame-ancestors *; " .
            "base-uri 'self'; form-action 'self';";
    }
}
