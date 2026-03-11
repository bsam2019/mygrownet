<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Video Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default video provider used for uploads.
    | Supported: "digitalocean", "cloudflare", "local"
    |
    */
    'default_provider' => env('GROWSTREAM_VIDEO_PROVIDER', 'digitalocean'),
    
    /*
    |--------------------------------------------------------------------------
    | Video Providers Configuration
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'digitalocean' => [
            'key' => env('DO_SPACES_KEY'),
            'secret' => env('DO_SPACES_SECRET'),
            'region' => env('DO_SPACES_REGION', 'nyc3'),
            'bucket' => env('DO_SPACES_BUCKET'),
            'endpoint' => env('DO_SPACES_ENDPOINT'),
            'cdn_endpoint' => env('DO_SPACES_CDN_ENDPOINT'),
        ],
        
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
    */
    'content_types' => [
        'movie' => 'Movie',
        'series' => 'Series',
        'episode' => 'Episode',
        'lesson' => 'Lesson',
        'short' => 'Short Video',
        'workshop' => 'Workshop',
        'webinar' => 'Webinar',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Access Levels
    |--------------------------------------------------------------------------
    */
    'access_levels' => [
        'free' => 'Free',
        'basic' => 'Basic Subscription',
        'premium' => 'Premium Subscription',
        'institutional' => 'Institutional',
    ],
];
