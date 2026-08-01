<?php

return [
    'id' => 'growmarket',
    'name' => 'GrowMarket',
    'slug' => 'growmarket',
    'description' => 'Marketplace for buying and selling',
    'category' => 'commerce',
    'status' => 'active',
    'version' => '1.0.0',
    'account_types' => ['member', 'business'],
    'requires_subscription' => true,
    'icon' => 'ShoppingCartIcon',
    'color' => 'teal',

    'feature_labels' => [
        'listings' => 'Listings',
        'storefront' => 'Seller Storefront',
        'escrow' => 'Escrow Payments',
        'promoted' => 'Promoted Listings',
        'analytics' => 'Seller Analytics',
        'priority_support' => 'Priority Support',
    ],

    'limit_labels' => [
        'listings' => 'Active Listings',
        'orders' => 'Sales / month',
    ],

    'tiers' => [
        'free' => [
            'name' => 'Free',
            'description' => 'Buy and sell with a few listings',
            'price_monthly' => 0,
            'price_annual' => 0,
            'popular' => false,
            'sort_order' => 0,
            'limits' => [
                'listings' => 5,
                'orders' => 10,
            ],
            'features' => [
                'listings',
            ],
        ],

        'basic' => [
            'name' => 'Basic',
            'description' => 'A professional seller storefront',
            'price_monthly' => 149,
            'price_annual' => 1429,
            'popular' => true,
            'sort_order' => 1,
            'limits' => [
                'listings' => 100,
                'orders' => 200,
            ],
            'features' => [
                'listings',
                'storefront',
                'escrow',
                'analytics',
            ],
        ],

        'business' => [
            'name' => 'Business',
            'description' => 'Scale your marketplace sales',
            'price_monthly' => 449,
            'price_annual' => 4310,
            'popular' => false,
            'sort_order' => 2,
            'limits' => [
                'listings' => -1,
                'orders' => -1,
            ],
            'features' => [
                'listings',
                'storefront',
                'escrow',
                'promoted',
                'analytics',
                'priority_support',
            ],
        ],
    ],

    'usage_metrics' => [
        'listings' => [
            'label' => 'Active Listings',
            'period' => 'lifetime',
        ],
    ],
];
