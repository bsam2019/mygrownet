<?php

return [
    'id' => 'bms',
    'name' => 'Company Management',
    'slug' => 'bms',
    'description' => 'Invoicing, inventory, and business management',
    'category' => 'business',
    'status' => 'active',
    'version' => '1.0.0',
    'account_types' => ['member', 'business'],
    'requires_subscription' => true,
    'icon' => 'BuildingOfficeIcon',
    'color' => 'indigo',

    'feature_labels' => [
        'invoices' => 'Invoices',
        'inventory' => 'Inventory',
        'customers' => 'Customers',
        'products' => 'Products',
        'contracts' => 'Contracts',
        'hr' => 'HR & Payroll',
        'reports' => 'Reports',
        'multi_company' => 'Multiple Companies',
        'api_access' => 'API Access',
        'priority_support' => 'Priority Support',
    ],

    'limit_labels' => [
        'invoices' => 'Invoices / month',
        'customers' => 'Customers',
        'products' => 'Products',
        'companies' => 'Companies',
        'team_members' => 'Team Members',
    ],

    'tiers' => [
        'free' => [
            'name' => 'Free',
            'description' => 'Essential tools for getting started',
            'price_monthly' => 0,
            'price_annual' => 0,
            'popular' => false,
            'sort_order' => 0,
            'limits' => [
                'invoices' => 10,
                'customers' => 20,
                'products' => 20,
                'companies' => 1,
                'team_members' => 1,
            ],
            'features' => [
                'invoices',
                'inventory',
            ],
        ],

        'basic' => [
            'name' => 'Basic',
            'description' => 'Essential business management tools',
            'price_monthly' => 149,
            'price_annual' => 1429,
            'popular' => true,
            'sort_order' => 1,
            'limits' => [
                'invoices' => 100,
                'customers' => 500,
                'products' => 500,
                'companies' => 1,
                'team_members' => 3,
            ],
            'features' => [
                'invoices',
                'inventory',
                'customers',
                'products',
                'contracts',
                'reports',
            ],
        ],

        'professional' => [
            'name' => 'Professional',
            'description' => 'For growing businesses that need more',
            'price_monthly' => 399,
            'price_annual' => 3830,
            'popular' => false,
            'sort_order' => 2,
            'limits' => [
                'invoices' => -1,
                'customers' => 2000,
                'products' => 2000,
                'companies' => 1,
                'team_members' => 10,
            ],
            'features' => [
                'invoices',
                'inventory',
                'customers',
                'products',
                'contracts',
                'hr',
                'reports',
                'priority_support',
            ],
        ],

        'business' => [
            'name' => 'Business',
            'description' => 'Complete suite for scaling businesses',
            'price_monthly' => 799,
            'price_annual' => 7670,
            'popular' => false,
            'sort_order' => 3,
            'limits' => [
                'invoices' => -1,
                'customers' => -1,
                'products' => -1,
                'companies' => 3,
                'team_members' => 20,
            ],
            'features' => [
                'invoices',
                'inventory',
                'customers',
                'products',
                'contracts',
                'hr',
                'reports',
                'multi_company',
                'api_access',
                'priority_support',
            ],
        ],
    ],

    'usage_metrics' => [
        'invoices' => [
            'label' => 'Invoices',
            'period' => 'monthly',
            'reset_day' => 1,
        ],
    ],
];
