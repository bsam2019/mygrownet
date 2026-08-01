<?php

return [
    'id' => 'growmart',
    'name' => 'GrowMart',
    'slug' => 'growmart',
    'description' => 'Online store and product sales',
    'category' => 'commerce',
    'status' => 'active',
    'version' => '1.0.0',
    'account_types' => ['member', 'business'],
    'requires_subscription' => true,
    'icon' => 'ShoppingBagIcon',
    'color' => 'rose',

    'feature_labels' => [
        'storefront' => 'Storefront',
        'orders' => 'Orders',
        'inventory' => 'Inventory',
        'coupons' => 'Coupons & Discounts',
        'customer_reviews' => 'Customer Reviews',
        'analytics' => 'Sales Analytics',
        'custom_domain' => 'Custom Domain',
        'priority_support' => 'Priority Support',
    ],

    'limit_labels' => [
        'products' => 'Products',
        'orders' => 'Orders / month',
        'storage_mb' => 'Storage (MB)',
    ],

    'tiers' => [
        'free' => [
            'name' => 'Free',
            'description' => 'Sell a few items with a basic storefront',
            'price_monthly' => 0,
            'price_annual' => 0,
            'popular' => false,
            'sort_order' => 0,
            'limits' => [
                'products' => 10,
                'orders' => 20,
                'storage_mb' => 100,
            ],
            'features' => [
                'storefront',
                'orders',
            ],
        ],

        'basic' => [
            'name' => 'Basic',
            'description' => 'A real online store for growing sales',
            'price_monthly' => 199,
            'price_annual' => 1910,
            'popular' => true,
            'sort_order' => 1,
            'limits' => [
                'products' => 100,
                'orders' => 500,
                'storage_mb' => 1024,
            ],
            'features' => [
                'storefront',
                'orders',
                'inventory',
                'coupons',
                'customer_reviews',
                'analytics',
            ],
        ],

        'business' => [
            'name' => 'Business',
            'description' => 'Full commerce toolkit for serious sellers',
            'price_monthly' => 499,
            'price_annual' => 4790,
            'popular' => false,
            'sort_order' => 2,
            'limits' => [
                'products' => -1,
                'orders' => -1,
                'storage_mb' => 5120,
            ],
            'features' => [
                'storefront',
                'orders',
                'inventory',
                'coupons',
                'customer_reviews',
                'analytics',
                'custom_domain',
                'priority_support',
            ],
        ],
    ],

    'usage_metrics' => [
        'orders' => [
            'label' => 'Orders',
            'period' => 'monthly',
            'reset_day' => 1,
        ],
    ],
];
