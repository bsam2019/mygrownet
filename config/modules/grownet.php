<?php

return [
    'id' => 'grownet',
    'name' => 'GrowNet',
    'slug' => 'grownet',
    'description' => '7-level professional network with commissions',
    'category' => 'network',
    'status' => 'active',
    'version' => '1.0.0',
    'account_types' => ['member'],
    'requires_subscription' => true,
    'icon' => 'UsersIcon',
    'color' => 'green',

    'feature_labels' => [
        'network_levels' => 'Network Levels',
        'commissions' => 'Commission Tiers',
        'starter_kits' => 'Starter Kits',
        'learning_hub' => 'Learning Hub',
        'priority_support' => 'Priority Support',
        'advanced_analytics' => 'Advanced Analytics',
    ],

    'limit_labels' => [
        'network_levels' => 'Network Levels',
        'team_members' => 'Team Members',
    ],

    'tiers' => [
        'free' => [
            'name' => 'Free',
            'description' => 'Get started with the basic network',
            'price_monthly' => 0,
            'price_annual' => 0,
            'popular' => false,
            'sort_order' => 0,
            'limits' => [
                'network_levels' => 1,
                'team_members' => 1,
            ],
            'features' => [
                'network_levels',
                'commissions',
            ],
        ],

        'starter' => [
            'name' => 'Starter',
            'description' => 'Grow your network and earn commissions',
            'price_monthly' => 99,
            'price_annual' => 950,
            'popular' => true,
            'sort_order' => 1,
            'limits' => [
                'network_levels' => 3,
                'team_members' => 3,
            ],
            'features' => [
                'network_levels',
                'commissions',
                'starter_kits',
            ],
        ],

        'business' => [
            'name' => 'Business',
            'description' => 'Scale your network with advanced tools',
            'price_monthly' => 299,
            'price_annual' => 2870,
            'popular' => false,
            'sort_order' => 2,
            'limits' => [
                'network_levels' => 7,
                'team_members' => 10,
            ],
            'features' => [
                'network_levels',
                'commissions',
                'starter_kits',
                'learning_hub',
                'advanced_analytics',
            ],
        ],
    ],

    'usage_metrics' => [
        'network_levels' => [
            'label' => 'Network Levels',
            'period' => 'lifetime',
        ],
    ],
];
