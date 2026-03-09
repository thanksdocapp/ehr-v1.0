<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Capture UTM and ad tracking parameters when users land on booking pages.
 * Stores them in session so they persist through the booking flow and can be
 * used for conversion tracking on the success page (multi-agency attribution).
 */
class CaptureBookingUtmParams
{
    protected array $utmKeys = [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
    ];

    protected array $adKeys = [
        'gclid',   // Google Ads click ID
        'fbclid',  // Facebook click ID
        'msclkid', // Microsoft Ads click ID
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isBookingEntryPath($request)) {
            $this->captureAndStore($request);
        }

        return $next($request);
    }

    protected function isBookingEntryPath(Request $request): bool
    {
        $path = trim($request->path(), '/');
        // Entry points: /book, /book/{slug}, /book/clinic/{slug}, /book/service/{id}/{id}
        if ($path === 'book') {
            return true;
        }
        if (!str_starts_with($path, 'book/')) {
            return false;
        }
        // Don't capture on success, review, confirm, or API paths
        $exclude = ['book/success', 'book/review', 'book/confirm', 'book/select-datetime', 'book/patient-details'];
        foreach ($exclude as $ex) {
            if (str_starts_with($path, $ex)) {
                return false;
            }
        }
        return true;
    }

    protected function captureAndStore(Request $request): void
    {
        $params = [];

        foreach ($this->utmKeys as $key) {
            if ($request->filled($key)) {
                $params[$key] = substr($request->input($key), 0, 255);
            }
        }

        foreach ($this->adKeys as $key) {
            if ($request->filled($key)) {
                $params[$key] = substr($request->input($key), 0, 255);
            }
        }

        if (!empty($params)) {
            session(['booking_utm_params' => $params]);
        }
    }
}
