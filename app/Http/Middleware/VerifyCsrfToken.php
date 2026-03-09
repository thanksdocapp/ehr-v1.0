<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'install/*',
        'pay/*',           // Public payment success - gateways may POST on return
        'payment/*',      // Payment callbacks/success
        'patient/billing/*', // Patient payment success - gateways may POST on return
    ];
}