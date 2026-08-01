<?php

return [
    'id' => 'stockflow',
    'name' => 'StockFlow',
    'slug' => 'stockflow',
    'description' => 'Inventory, purchases, sales and audits',
    'category' => 'business',
    'status' => 'active',
    'version' => '1.0.0',
    'account_types' => ['member', 'business'],
    'requires_subscription' => true,
    'icon' => 'ArchiveBoxIcon',
    'color' => 'amber',

    'feature_labels' => [
        'items' => 'Stock Items',
        'purchases' => 'Purchase Orders',
        'sales' => 'Sales',
        'audits' => 'Stock Audits',
        'cash_register' => 'Cash Register',
        'suppliers' => 'Suppliers',
        'expiry_checks' => 'Expiry Checks',
        'multi_branch' => 'Multiple Branches',
        'reports' => 'Reports',
        'priority_support' => 'Priority Support',
    ],

    'limit_labels' => [
        'items' => 'Stock Items',
        'purchases' => 'Purchase Orders / month',
        'sales' => 'Sales / month',
        'branches' => 'Branches',
        'team_members' => 'Team Members',
    ],

    'tiers' => [
        'free' => [
            'name' => 'Free',
            'description' => 'Try StockFlow with basic tracking',
            'price_monthly' => 0,
            'price_annual' => 0,
            'popular' => false,
            'sort_order' => 0,
            'limits' => [
                'items' => 100,
                'purchases' => 10,
                'sales' => 10,
                'branches' => 1,
                'team_members' => 1,
            ],
            'features' => [
                'items',
                'sales',
            ],
        ],

        'basic' => [
            'name' => 'Basic',
            'description' => 'Complete stock management for one branch',
            'price_monthly' => 179,
            'price_annual' => 1717,
            'popular' => true,
            'sort_order' => 1,
            'limits' => [
                'items' => 1000,
                'purchases' => 100,
                'sales' => 100,
                'branches' => 1,
                'team_members' => 3,
            ],
            'features' => [
                'items',
                'purchases',
                'sales',
                'suppliers',
                'audits',
                'cash_register',
                'reports',
            ],
        ],

        'professional' => [
            'name' => 'Professional',
            'description' => 'Multi-branch inventory with audits',
            'price_monthly' => 449,
            'price_annual' => 4310,
            'popular' => false,
            'sort_order' => 2,
            'limits' => [
                'items' => -1,
                'purchases' => 500,
                'sales' => 500,
                'branches' => 3,
                'team_members' => 10,
            ],
            'features' => [
                'items',
                'purchases',
                'sales',
                'suppliers',
                'audits',
                'cash_register',
                'expiry_checks',
                'multi_branch',
                'reports',
                'priority_support',
            ],
        ],
    ],

    'usage_metrics' => [
        'purchases' => [
            'label' => 'Purchase Orders',
            'period' => 'monthly',
            'reset_day' => 1,
        ],
        'sales' => [
            'label' => 'Sales',
            'period' => 'monthly',
            'reset_day' => 1,
        ],
    ],
];
