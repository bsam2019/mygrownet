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
        'views_per_month' => 'Premium Views / Month',
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
                'views_per_month' => 0,
            ],
            'features' => [
                'free_catalog',
                'creator_tools',
            ],
        ],

        'starter' => [
            'name' => 'Starter',
            'description' => 'For viewers who want more premium content',
            'price_monthly' => 129,
            'price_annual' => 1238,
            'popular' => true,
            'sort_order' => 1,
            'limits' => [
                'views_per_month' => 300,
            ],
            'features' => [
                'hd_streaming',
                'ad_free',
                'multi_device',
            ],
        ],

        'business' => [
            'name' => 'Business',
            'description' => 'Unlimited premium streaming for families & organisations',
            'price_monthly' => 549,
            'price_annual' => 5270,
            'popular' => false,
            'sort_order' => 2,
            'limits' => [
                'views_per_month' => -1,
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
        'views_per_month' => [
            'label' => 'Premium Views',
            'period' => 'monthly',
        ],
    ],
];
