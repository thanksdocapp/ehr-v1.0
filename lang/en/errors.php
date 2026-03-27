<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API / JSON messages (no technical or status-code wording for end users)
    |--------------------------------------------------------------------------
    */
    'unauthenticated' => 'Please sign in to continue.',
    'forbidden' => 'You do not have permission to access this resource.',
    'not_found' => 'We could not find what you were looking for.',
    'gone' => 'This content is no longer available.',
    'server_error' => 'Something went wrong. Please try again later.',
    'generic_client_error' => 'We could not complete this request.',
    'page_expired' => 'This page has expired. Please refresh and try again.',
    'too_many_requests' => 'Too many attempts. Please wait a moment and try again.',
    'service_unavailable' => 'The service is temporarily unavailable. Please try again later.',
    'method_not_allowed' => 'This action is not allowed for this resource.',

    /*
    |--------------------------------------------------------------------------
    | Web error pages (plain language; do not reference exception details)
    |--------------------------------------------------------------------------
    */
    'web' => [
        'forbidden_title' => 'Access denied',
        'forbidden' => 'You do not have permission to view this page. If you believe this is a mistake, contact your administrator.',
        'not_found_title' => 'Page not found',
        'not_found' => 'The page you are looking for does not exist or may have been moved.',
        'gone_title' => 'No longer available',
        'gone' => 'This page or resource is no longer available.',
        'page_expired_title' => 'Session expired',
        'page_expired' => 'Your session has expired for security. Please refresh the page and try again.',
        'too_many_requests_title' => 'Please slow down',
        'too_many_requests' => 'You have made too many requests in a short time. Please wait a moment and try again.',
        'server_error_title' => 'Something went wrong',
        'server_error' => 'We could not complete your request. Please try again later. If the problem continues, contact support.',
        'service_unavailable_title' => 'Temporarily unavailable',
        'service_unavailable' => 'We are performing maintenance or experiencing high load. Please try again shortly.',
    ],
];
