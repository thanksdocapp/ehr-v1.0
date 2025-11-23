<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Specify which origins are allowed to make cross-origin requests.
    | Use environment variable CORS_ALLOWED_ORIGINS for production.
    | Format: comma-separated list of origins (e.g., "https://example.com,https://app.example.com")
    | 
    | For ThankDoc production, set in .env:
    | CORS_ALLOWED_ORIGINS=https://thanksdoc.co.uk,https://www.thanksdoc.co.uk,https://notes.thanksdoc.co.uk
    |
    | For development, you can use ['*'] but this should be restricted in production.
    |
    */
    'allowed_origins' => env('CORS_ALLOWED_ORIGINS') 
        ? explode(',', env('CORS_ALLOWED_ORIGINS'))
        : (env('APP_ENV') === 'local' ? ['*'] : []),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false,

];
