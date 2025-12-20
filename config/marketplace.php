<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Configure image processing behavior for marketplace products
    |
    */

    'images' => [
        // Current phase of image processing
        // Options: 'mvp', 'phase2', 'phase3', 'phase4'
        'processing_phase' => env('MARKETPLACE_IMAGE_PHASE', 'phase2'),

        // Maximum number of images per product
        'max_images' => 8,

        // Maximum file size in KB
        'max_file_size' => 5120, // 5MB

        // Allowed image formats
        'allowed_formats' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],

        // Image sizes to generate
        'sizes' => [
            'original' => ['width' => null, 'height' => null], // Keep original
            'large' => ['width' => 1200, 'height' => null],
            'medium' => ['width' => 800, 'height' => null],
            'thumbnail' => ['width' => 300, 'height' => null],
        ],

        // JPEG quality (1-100)
        'jpeg_quality' => 85,

        // Phase 3: Background removal
        'background_removal' => [
            'enabled' => env('MARKETPLACE_BG_REMOVAL', false),
            'featured_only' => true, // Only process featured products
            'use_api' => env('MARKETPLACE_BG_REMOVAL_API', false), // Use external API
        ],

        // Phase 4: Advanced features
        'watermark' => [
            'enabled' => env('MARKETPLACE_WATERMARK', false),
            'text' => 'MyGrowNet',
            'premium_only' => true, // Only for premium sellers
        ],

        'enhancement' => [
            'enabled' => env('MARKETPLACE_IMAGE_ENHANCE', false),
            'auto_enhance' => false, // Automatically enhance all images
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Seller Trust Levels
    |--------------------------------------------------------------------------
    */

    'trust_levels' => [
        'new' => [
            'name' => 'New Seller',
            'badge' => '🆕',
            'min_orders' => 0,
            'min_rating' => 0,
        ],
        'verified' => [
            'name' => 'Verified Seller',
            'badge' => '✓',
            'min_orders' => 0,
            'min_rating' => 0,
            'requires_kyc' => true,
        ],
        'trusted' => [
            'name' => 'Trusted Seller',
            'badge' => '⭐',
            'min_orders' => 50,
            'min_rating' => 4.5,
        ],
        'top' => [
            'name' => 'Top Seller',
            'badge' => '👑',
            'min_orders' => 200,
            'min_rating' => 4.8,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Escrow Configuration
    |--------------------------------------------------------------------------
    */

    'escrow' => [
        'auto_release_days' => 7, // Auto-release funds after X days
        'dispute_window_days' => 14, // Buyer can dispute within X days
    ],

    /*
    |--------------------------------------------------------------------------
    | External Services
    |--------------------------------------------------------------------------
    */

    'services' => [
        'removebg' => [
            'api_key' => env('REMOVEBG_API_KEY'),
            'enabled' => env('REMOVEBG_ENABLED', false),
        ],
        'cloudinary' => [
            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
            'api_key' => env('CLOUDINARY_API_KEY'),
            'api_secret' => env('CLOUDINARY_API_SECRET'),
            'enabled' => env('CLOUDINARY_ENABLED', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Delivery Configuration
    |--------------------------------------------------------------------------
    |
    | Configure delivery methods and courier integrations
    |
    */

    'delivery' => [
        // Manual courier integration (MVP - realistic for Zambia)
        'manual_couriers' => [
            'enabled' => true,
            // Couriers are defined in ManualCourierService
        ],

        // API-based courier integrations (future)
        'api_couriers' => [
            'dhl' => [
                'enabled' => env('DHL_API_ENABLED', false),
                'api_key' => env('DHL_API_KEY'),
                'api_secret' => env('DHL_API_SECRET'),
                'api_url' => env('DHL_API_URL', 'https://api.dhl.com/v1'),
            ],
            'fedex' => [
                'enabled' => env('FEDEX_API_ENABLED', false),
                'api_key' => env('FEDEX_API_KEY'),
                'api_secret' => env('FEDEX_API_SECRET'),
                'api_url' => env('FEDEX_API_URL'),
            ],
        ],

        // Pickup stations
        'pickup_stations' => [
            'enabled' => true,
            'fee' => 1000, // K10.00 in ngwee
        ],

        // Self-delivery by seller
        'self_delivery' => [
            'enabled' => true,
            'auto_release_days' => 7, // Auto-release funds after 7 days
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | BizBoost Integration
    |--------------------------------------------------------------------------
    |
    | Configure integration with BizBoost mini-websites
    |
    */

    'bizboost_integration' => [
        'enabled' => env('MARKETPLACE_BIZBOOST_SYNC', true),
        
        // Auto-sync when mini-website is published
        'auto_sync_on_publish' => true,
        
        // Auto-approve BizBoost sellers (they're already vetted)
        'auto_approve_sellers' => true,
        
        // Auto-approve BizBoost products
        'auto_approve_products' => true,
        
        // Sync interval for batch updates (seconds)
        'sync_interval' => 300, // 5 minutes
        
        // Category mapping from BizBoost to Marketplace
        'category_mapping' => [
            'Food & Beverage' => 'Food & Drinks',
            'Fashion & Apparel' => 'Fashion',
            'Electronics' => 'Electronics',
            'Health & Beauty' => 'Health & Beauty',
            'Home & Garden' => 'Home & Living',
            'Sports & Fitness' => 'Sports & Outdoors',
            'Books & Media' => 'Books & Media',
            'Toys & Games' => 'Toys & Kids',
            'Automotive' => 'Automotive',
            'Services' => 'Services',
        ],
    ],
];
