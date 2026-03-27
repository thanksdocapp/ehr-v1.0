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
        'action_back' => 'Go back',
        'action_refresh' => 'Refresh',

        'forbidden_title' => 'You need different access',
        'forbidden' => 'Your account does not have permission to open this page. If you expected to see this content, ask your practice administrator to update your role.',
        'not_found_title' => 'We could not find that page',
        'not_found' => 'The link may be outdated, or the page may have been moved. Try going back or use the home button to continue.',
        'gone_title' => 'This link is no longer active',
        'gone' => 'The information here has been removed or archived and is not available anymore.',
        'page_expired_title' => 'Please refresh this page',
        'page_expired' => 'For your security, this form session has timed out. If your browser asks whether to resubmit data you entered earlier, choose Cancel (or close the dialog), then refresh this page and try again. You can also use the Refresh button below.',
        'too_many_requests_title' => 'Take a short pause',
        'too_many_requests' => 'Too many actions happened in a short time. Please wait a moment, then try again.',
        'server_error_title' => 'We hit a snag',
        'server_error' => 'Something did not work on our side. Please try again in a moment. If it keeps happening, contact your support team.',
        'service_unavailable_title' => 'We will be right back',
        'service_unavailable' => 'The system is temporarily busy or undergoing maintenance. Please try again shortly.',
    ],
];
