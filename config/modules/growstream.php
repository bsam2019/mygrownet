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
        'free_catalog' => 'Free Content Catalogue',
        'hd_streaming' => 'HD Streaming',
        '4k_streaming' => '4K Streaming',
        'ad_free' => 'Ad-Free',
        'offline_downloads' => 'Offline Downloads',
        'multi_device' => 'Watch on Multiple Devices',
        'priority_support' => 'Priority Support',
        'creator_tools' => 'Free Creator Tools',
    ],

    'limit_labels' => [
        'watch_minutes_per_month' => 'Watch Minutes / Month',
    ],

    'tiers' => [
        'free' => [
            'name' => 'Free',
            'description' => 'Watch free content. No subscription needed.',
            'price_monthly' => 0,
            'price_annual' => 0,
            'popular' => false,
            'sort_order' => 0,
            'limits' => [
                'watch_minutes_per_month' => 0,
            ],
            'features' => [
                'free_catalog',
                'creator_tools',
            ],
        ],

        'starter' => [
            'name' => 'Starter',
            'description' => '~8 hours of premium content',
            'price_monthly' => 35,
            'price_annual' => 350,
            'popular' => true,
            'sort_order' => 1,
            'limits' => [
                'watch_minutes_per_month' => 500,
            ],
            'features' => [
                'hd_streaming',
                'ad_free',
                'multi_device',
            ],
        ],

        'premium' => [
            'name' => 'Premium',
            'description' => '~18 hours of premium content',
            'price_monthly' => 75,
            'price_annual' => 750,
            'popular' => false,
            'sort_order' => 2,
            'limits' => [
                'watch_minutes_per_month' => 1100,
            ],
            'features' => [
                'hd_streaming',
                'ad_free',
                'offline_downloads',
                'multi_device',
            ],
        ],

        'business' => [
            'name' => 'Unlimited',
            'description' => 'Unlimited premium streaming',
            'price_monthly' => 145,
            'price_annual' => 1450,
            'popular' => false,
            'sort_order' => 3,
            'limits' => [
                'watch_minutes_per_month' => -1,
            ],
            'features' => [
                '4k_streaming',
                'ad_free',
                'offline_downloads',
                'multi_device',
                'priority_support',
            ],
        ],
    ],

    'usage_metrics' => [
        'watch_minutes_per_month' => [
            'label' => 'Watch Minutes',
            'period' => 'monthly',
        ],
    ],
];
