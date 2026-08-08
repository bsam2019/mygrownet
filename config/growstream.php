<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Video Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default video provider used for uploads.
    | Supported: "cloudflare", "local"
    |
    */
    'default_provider' => env('GROWSTREAM_VIDEO_PROVIDER', 'cloudflare'),

    /*
    |--------------------------------------------------------------------------
    | Video Providers Configuration
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'cloudflare' => [
            'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
            'api_token' => env('CLOUDFLARE_API_TOKEN'),
            'customer_subdomain' => env('CLOUDFLARE_CUSTOMER_SUBDOMAIN'),
            'signing_key_id' => env('CLOUDFLARE_SIGNING_KEY_ID'),
            'signing_key' => env('CLOUDFLARE_SIGNING_KEY'),
        ],

        'local' => [
            'disk' => env('GROWSTREAM_LOCAL_DISK', 'local'),
            'path' => 'videos',
        ],
    ],

    // Shorthand for Cloudflare config (used by Video model)
    'cloudflare' => [
        'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
        'customer_subdomain' => env('CLOUDFLARE_CUSTOMER_SUBDOMAIN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */
    'upload' => [
        'max_file_size' => 5 * 1024 * 1024 * 1024, // 5GB
        'allowed_mimetypes' => [
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-matroska',
            'video/webm',
        ],
        'chunk_size' => 5 * 1024 * 1024, // 5MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Paths
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'videos' => 'growstream/videos',
        'thumbnails' => 'growstream/thumbnails',
        'posters' => 'growstream/posters',
        'banners' => 'growstream/banners',
        'subtitles' => 'growstream/subtitles',
        'resources' => 'growstream/resources',
    ],

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for custom thumbnail uploads and processing.
    |
    | Note: Wasabi has a 90-day minimum storage retention policy. Frequent
    | thumbnail replacements will incur minor retention fees for deleted files.
    |
    */
    'thumbnails' => [
        'disk' => env('GROWSTREAM_THUMBNAIL_DISK', 'wasabi'), // Storage disk for custom thumbnails
        'max_size' => 2048, // Maximum file size in KB (2MB)
        'quality' => 80, // JPEG/WebP quality (0-100)
        'generate_webp' => true, // Generate WebP versions alongside JPEG
        
        // Multiple sizes for responsive images
        // [width, height] in pixels - all maintain 16:9 aspect ratio
        'sizes' => [
            'thumb' => [320, 180],   // Grid thumbnails, mobile
            'medium' => [640, 360],  // Detail pages, tablets
            'large' => [1280, 720],  // Full-screen, OG tags
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Control
    |--------------------------------------------------------------------------
    */
    'access' => [
        'preview_duration' => 300, // 5 minutes for guests
        'concurrent_streams' => [
            'basic' => 1,
            'premium' => 2,
            'family' => 5,
        ],
        'signed_url_expiration' => 86400, // 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Creator Settings
    |--------------------------------------------------------------------------
    */
    'creator' => [
        'default_revenue_share' => 70, // 70% to creator
        'minimum_payout' => 100, // K100 minimum
        'payout_schedule' => 'monthly', // monthly, weekly
        'upload_limit_per_month' => 50,
        'agreement_version' => '1.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pay-Per-View (PPV) / Video Rentals
    |--------------------------------------------------------------------------
    */
    'ppv' => [
        'price' => (int) env('GROWSTREAM_PPV_PRICE', 15), // K default rental price
        'access_duration' => '48_hours', // how long the rental lasts
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    */
    'analytics' => [
        'retention_days' => 365,
        'aggregate_after_days' => 90,
        'update_interval' => 10, // Update progress every 10 seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Player Settings
    |--------------------------------------------------------------------------
    */
    'player' => [
        'autoplay_next' => true,
        'countdown_duration' => 10, // seconds
        'default_quality' => 'auto',
        'playback_speeds' => [0.5, 0.75, 1, 1.25, 1.5, 2],
        'completion_threshold' => 95, // Mark as completed at 95%
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Types
    |--------------------------------------------------------------------------
    |
    | Entertainment-first categories aligned with the creator platform
    | (per the market research: drama, movies, comedy, skits, documentaries,
    | music, etc.).
    */
    'content_types' => [
        'movie' => 'Movie',
        'series' => 'Series',
        'episode' => 'Episode',
        'short' => 'Short Video',
        'comedy' => 'Comedy',
        'skit' => 'Skits',
        'soap' => 'Soap Opera',
        'drama' => 'Drama',
        'documentary' => 'Documentary',
        'reality' => 'Reality & Talk Shows',
        'music' => 'Music & Performance',
        'kids' => 'Kids & Family',
        'lifestyle' => 'Lifestyle',
        'faith' => 'Faith-Based',
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Levels
    |--------------------------------------------------------------------------
    |
    | Only 'free' (everyone) and 'premium' (subscribers) are used by the
    | AccessControlService enforcement. 'basic'/'institutional' had no mapping.
    */
    'access_levels' => [
        'free' => 'Free (Everyone)',
        'premium' => 'Premium (Subscribers)',
    ],

    /*
    |--------------------------------------------------------------------------
    | PWA / Offline
    |--------------------------------------------------------------------------
    |
    | Service worker registration + offline support for the GrowStream app.
    | Disable only if Cloudflare Stream playback regressions reappear.
    */
    'pwa' => [
        'enabled' => env('GROWSTREAM_PWA_ENABLED', true),
        'service_worker' => '/growstream-sw.js',
        'offline_page' => '/growstream-offline.html',
    ],

    /*
    |--------------------------------------------------------------------------
    | Operations Monitoring
    |--------------------------------------------------------------------------
    |
    | Thresholds checked by `php artisan growstream:check-ops` (scheduled hourly).
    |
    | - cloudflare_stored_minutes_limit: stored video minutes on Cloudflare Stream
    |   before a warning/critical alert fires. Set to null to disable.
    | - pawapay_webhook_stale_minutes: alert if no successful PawaPay webhook was
    |   received within this window (only meaningful once payments are live).
    | - failed_jobs_warning / failed_jobs_critical: failed_jobs table counts.
    */
    'ops_monitoring' => [
        'cloudflare_stored_minutes_limit' => env('GROWSTREAM_CF_STORED_MINUTES_LIMIT'),
        'pawapay_webhook_stale_minutes' => env('GROWSTREAM_PAWAPAY_STALE_MINUTES', 120),
        'failed_jobs_warning' => env('GROWSTREAM_FAILED_JOBS_WARNING', 5),
        'failed_jobs_critical' => env('GROWSTREAM_FAILED_JOBS_CRITICAL', 20),
    ],
];
