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
            'middleware' => ['web', 'auth', 'stockflow'],
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
];
