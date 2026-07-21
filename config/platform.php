<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform Applications
    |--------------------------------------------------------------------------
    |
    | Maps application slugs to their runtime configuration.
    | The database (applications table) identifies WHAT each app is.
    | This config file determines HOW each app runs.
    |
    | Adding a new application requires:
    |   1. A record in the applications table
    |   2. An entry here
    |   3. A Blade view and entry point (if Inertia-based)
    |
    | No change to DetectSubdomain middleware is needed.
    |
    */

    'applications' => [
        'stockflow' => [
            'name' => 'StockFlow',
            'domain_slug' => 'stockflow',
            'service_provider' => App\Providers\StockAuditServiceProvider::class,
            'middleware' => ['web', 'auth'],
            'uses_inertia' => true,
            'blade_layout' => 'stockflow',
            'session_prefix' => 'stockflow',
            'build_dir' => 'stockflow',
        ],

        'bms' => [
            'name' => 'BMS',
            'domain_slug' => 'bms',
            'service_provider' => null,
            'middleware' => ['web', 'auth'],
            'uses_inertia' => true,
            'blade_layout' => 'bms',
            'session_prefix' => null,
            'build_dir' => 'bms',
        ],

        'growmart' => [
            'name' => 'GrowMart',
            'domain_slug' => 'growmart',
            'service_provider' => null,
            'middleware' => ['web', 'auth'],
            'uses_inertia' => true,
            'blade_layout' => 'growmart',
            'session_prefix' => null,
            'build_dir' => 'growmart',
        ],

        'bizboost' => [
            'name' => 'BizBoost',
            'domain_slug' => 'bizboost',
            'service_provider' => null,
            'middleware' => ['web', 'auth'],
            'uses_inertia' => true,
            'blade_layout' => 'bizboost',
            'session_prefix' => null,
            'build_dir' => 'bizboost',
        ],

        'bizdocs' => [
            'name' => 'BizDocs',
            'domain_slug' => 'bizdocs',
            'service_provider' => null,
            'middleware' => ['web', 'auth'],
            'uses_inertia' => true,
            'blade_layout' => 'bizdocs',
            'session_prefix' => null,
            'build_dir' => 'bizdocs',
        ],

        'growbuilder' => [
            'name' => 'GrowBuilder',
            'domain_slug' => 'growbuilder',
            'service_provider' => null,
            'middleware' => ['web', 'auth'],
            'uses_inertia' => true,
            'blade_layout' => 'growbuilder',
            'session_prefix' => null,
            'build_dir' => 'growbuilder',
        ],

        'venture' => [
            'name' => 'Venture',
            'domain_slug' => 'venture',
            'service_provider' => null,
            'middleware' => ['web', 'auth'],
            'uses_inertia' => true,
            'blade_layout' => 'venture',
            'session_prefix' => null,
            'build_dir' => 'venture',
        ],

        'grownet' => [
            'name' => 'GrowNet',
            'domain_slug' => 'grownet',
            'service_provider' => null,
            'middleware' => ['web', 'auth'],
            'uses_inertia' => true,
            'blade_layout' => 'grownet',
            'session_prefix' => null,
            'build_dir' => 'grownet',
        ],

        'growstorage' => [
            'name' => 'GrowStorage',
            'domain_slug' => 'growstorage',
            'service_provider' => null,
            'middleware' => ['web', 'auth'],
            'uses_inertia' => true,
            'blade_layout' => 'growstorage',
            'session_prefix' => null,
            'build_dir' => null,
        ],

        'zamstay' => [
            'name' => 'ZamStay',
            'domain_slug' => 'zamstay',
            'service_provider' => null,
            'middleware' => ['web', 'auth'],
            'uses_inertia' => true,
            'blade_layout' => 'zamstay',
            'session_prefix' => null,
            'build_dir' => 'zamstay',
        ],

        'primeedge' => [
            'name' => 'PrimeEdge Advisory',
            'domain_slug' => 'primeedge',
            'service_provider' => null,
            'middleware' => ['web', 'auth', 'primeedge'],
            'uses_inertia' => true,
            'blade_layout' => 'primeedge',
            'session_prefix' => null,
            'build_dir' => 'primeedge',
        ],

        'geopamu' => [
            'name' => 'Geopamu',
            'domain_slug' => 'geopamu',
            'service_provider' => null,
            'middleware' => ['web'],
            'uses_inertia' => false,
            'blade_layout' => null,
            'session_prefix' => null,
            'build_dir' => null,
        ],

        'wedding' => [
            'name' => 'Wedding (WowThem)',
            'domain_slug' => 'wowthem',
            'service_provider' => null,
            'middleware' => ['web'],
            'uses_inertia' => false,
            'blade_layout' => 'wedding',
            'session_prefix' => null,
            'build_dir' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization Workspaces
    |--------------------------------------------------------------------------
    |
    | When organization-based subdomains are enabled (Phase 7),
    | this prefix is used to distinguish org subdomains from application subdomains.
    |
    */
    'organization_prefix' => null, // e.g., 'org' would make org.mygrownet.com patterns

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */
    'features' => [
        'unified_auth' => env('PLATFORM_UNIFIED_AUTH', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | MyGrow Identity (Phase 8)
    |--------------------------------------------------------------------------
    |
    | Configuration for the centralized authentication gateway at auth.mygrownet.com.
    | All platform authentication flows through this gateway.
    |
    */
    'identity' => [
        'login_url'            => env('IDENTITY_LOGIN_URL', 'https://auth.mygrownet.com/login'),
        'register_url'         => env('IDENTITY_REGISTER_URL', 'https://auth.mygrownet.com/register'),
        'logout_url'           => env('IDENTITY_LOGOUT_URL', 'https://auth.mygrownet.com/logout'),
        'password_reset_url'   => env('IDENTITY_PASSWORD_RESET_URL', 'https://auth.mygrownet.com/password/reset'),
        'email_verify_url'     => env('IDENTITY_EMAIL_VERIFY_URL', 'https://auth.mygrownet.com/email/verify'),
        'signing_key'          => env('IDENTITY_SIGNING_KEY'),
        'return_url_ttl'       => (int) env('IDENTITY_RETURN_URL_TTL', 300),
        'allowed_return_hosts' => ['*.mygrownet.com', '127.0.0.1', 'localhost'],
        'app_redirect_enabled' => [
            'stockflow'   => env('IDENTITY_REDIRECT_STOCKFLOW', false),
            'primeedge'   => env('IDENTITY_REDIRECT_PRIMEEDGE', false),
            'growbuilder' => env('IDENTITY_REDIRECT_GROWBUILDER', false),
        ],
        'rate_limiting' => [
            'per_ip'       => (int) env('IDENTITY_RATE_LIMIT_PER_IP', 20),
            'per_user'     => (int) env('IDENTITY_RATE_LIMIT_PER_USER', 5),
            'lockout_after' => (int) env('IDENTITY_LOCKOUT_AFTER', 5),
            'lockout_duration' => (int) env('IDENTITY_LOCKOUT_DURATION', 60),
        ],

        /*
        |--------------------------------------------------------------------------
        | Session Configuration
        |--------------------------------------------------------------------------
        |
        | The SESSION_DOMAIN must include a leading dot for cross-subdomain cookies.
        | In production: .mygrownet.com
        | In development: .mygrow.test
        |
        */
        'session_domain' => env('SESSION_DOMAIN', '.mygrow.test'),

        /*
        |--------------------------------------------------------------------------
        | Custom Domain SSO (Phase 8f — deferred)
        |--------------------------------------------------------------------------
        |
        | JWT configuration for organizations using custom domains that cannot
        | share the platform session cookie.
        |
        */
        'jwt' => [
            'ttl'           => (int) env('IDENTITY_JWT_TTL', 300),
            'signing_key'   => env('IDENTITY_JWT_SIGNING_KEY'),
            'use_jti'       => env('IDENTITY_JWT_USE_JTI', true),
        ],
    ],
];
