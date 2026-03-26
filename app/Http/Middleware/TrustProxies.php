<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Behind nginx, Cloudflare, AWS ELB, etc., this must be set so Laravel sees
     * HTTPS via X-Forwarded-Proto. Otherwise $request->secure() is false, session
     * cookies stay SameSite=Lax, and embedded booking iframes lose the session
     * on POST → 419 Page Expired.
     *
     * Override with TRUSTED_PROXIES in .env (comma-separated IPs), or * for all.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
