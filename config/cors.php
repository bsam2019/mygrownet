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

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        // MyGrow Identity Gateway — applications redirect to auth.mygrownet.com
        // via full-page navigations, but Inertia links on app subdomains issue
        // XHRs whose cross-origin redirects must pass the CORS preflight.
        'login',
        'register',
        'password/*',
        'logout',
        'email/*',
        '2fa/*',
        'session/validate',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => ['/^https?:\/\/(.*\.)?mygrownet\.com$/'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
