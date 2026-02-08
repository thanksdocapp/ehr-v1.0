<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows the public booking flow to be embedded in iframes (e.g. WordPress).
 * Sets headers so /book/* and /pay/* (post-confirm payment) can be framed by any site; other routes keep default strict headers.
 */
class AllowBookingEmbed
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only allow framing for booking paths (and homepage when it is the booking page)
        if (!$this->isEmbeddablePath($request)) {
            return $response;
        }

        // Allow this response to be embedded in iframes (WordPress, other sites).
        // Remove all framing restrictions (CSP can still add frame-ancestors *).
        $response->headers->remove('X-Frame-Options');
        $response->headers->remove('Content-Security-Policy');
        $response->headers->set('Content-Security-Policy', $this->cspWithFrameAncestors(), true);

        return $response;
    }

    /**
     * Whether the request is for a path that may be embedded (booking or homepage).
     * Uses path() and raw request URI so we catch booking even behind proxies/subdir.
     */
    private function isEmbeddablePath(Request $request): bool
    {
        $path = trim($request->path(), '/');
        if ($path === '' || $path === 'book' || str_starts_with($path, 'book/')) {
            return true;
        }
        // Post-confirm payment flow (redirect from booking confirm) must stay in iframe
        if ($path === 'pay' || str_starts_with($path, 'pay/')) {
            return true;
        }
        $uri = $request->getRequestUri();
        $uriPath = trim((string) parse_url($uri, PHP_URL_PATH), '/');
        return $uriPath === '' || $uriPath === 'book' || str_starts_with($uriPath, 'book/')
            || $uriPath === 'pay' || str_starts_with($uriPath, 'pay/');
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
