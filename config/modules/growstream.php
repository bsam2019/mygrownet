<?php

return [
    'id' => 'growstream',
    'name' => 'GrowStream',
    'slug' => 'growstream',
    'description' => 'Video streaming & learning platform',
    'category' => 'media',
    'status' => 'active',
    'version' => '1.0.0',
    'account_types' => ['member', 'business'],
    'requires_subscription' => true,
    'icon' => 'PlayCircleIcon',
    'color' => 'fuchsia',

    'feature_labels' => [
        'videos' => 'Video Hosting',
        'livestream' => 'Live Streaming',
        'courses' => 'Courses',
        'analytics' => 'Viewer Analytics',
        'branding' => 'Custom Branding',
        'priority_support' => 'Priority Support',
    ],

    'limit_labels' => [
        'videos' => 'Videos',
        'storage_mb' => 'Storage (MB)',
        'viewers' => 'Monthly Viewers',
    ],

    'tiers' => [
        'free' => [
            'name' => 'Free',
            'description' => 'Upload and share a few videos',
            'price_monthly' => 0,
            'price_annual' => 0,
            'popular' => false,
            'sort_order' => 0,
            'limits' => [
                'videos' => 5,
                'storage_mb' => 500,
                'viewers' => 100,
            ],
            'features' => [
                'videos',
            ],
        ],

        'starter' => [
            'name' => 'Starter',
            'description' => 'For content creators getting started',
            'price_monthly' => 129,
            'price_annual' => 1238,
            'popular' => true,
            'sort_order' => 1,
            'limits' => [
                'videos' => 50,
                'storage_mb' => 5120,
                'viewers' => 1000,
            ],
            'features' => [
                'videos',
                'courses',
                'analytics',
            ],
        ],

        'business' => [
            'name' => 'Business',
            'description' => 'Full streaming platform for organisations',
            'price_monthly' => 549,
            'price_annual' => 5270,
            'popular' => false,
            'sort_order' => 2,
            'limits' => [
                'videos' => -1,
                'storage_mb' => 51200,
                'viewers' => -1,
            ],
            'features' => [
                'videos',
                'livestream',
                'courses',
                'analytics',
                'branding',
                'priority_support',
            ],
        ],
    ],

    'usage_metrics' => [
        'videos' => [
            'label' => 'Videos',
            'period' => 'lifetime',
        ],
        'storage_mb' => [
            'label' => 'Storage',
            'period' => 'lifetime',
            'unit' => 'MB',
        ],
    ],
];
