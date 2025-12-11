<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Module Definitions
    |--------------------------------------------------------------------------
    |
    | Define all available modules in the MyGrowNet platform.
    | Each module can be core (free) or subscription-based.
    |
    */

    'core' => [
        'name' => 'MyGrowNet Core',
        'slug' => 'core',
        'category' => 'core',
        'description' => 'Core MLM platform features including dashboard, network, and earnings',
        'icon' => 'home',
        'color' => 'blue',
        'account_types' => ['member'],
        'price' => 0,
        'requires_subscription' => false,
        'routes' => [
            'integrated' => '/dashboard',
        ],
        'pwa' => [
            'enabled' => false,
        ],
        'features' => [
            'offline' => false,
            'dataSync' => false,
            'notifications' => true,
        ],
    ],

    'sme-accounting' => [
        'name' => 'SME Accounting',
        'slug' => 'accounting',
        'category' => 'sme',
        'description' => 'Complete accounting solution for small and medium enterprises',
        'icon' => 'calculator',
        'color' => 'purple',
        'thumbnail' => '/images/modules/accounting.png',
        'account_types' => ['business'],
        'subscription_tiers' => [
            'basic' => [
                'name' => 'Basic',
                'price' => 100,
                'billing_cycle' => 'monthly',
                'user_limit' => 3,
                'storage_limit_mb' => 1000,
                'features' => [
                    'invoicing' => true,
                    'expenses' => true,
                    'reports' => 'basic',
                    'multi_currency' => false,
                ],
            ],
            'pro' => [
                'name' => 'Professional',
                'price' => 200,
                'billing_cycle' => 'monthly',
                'user_limit' => 10,
                'storage_limit_mb' => 5000,
                'features' => [
                    'invoicing' => true,
                    'expenses' => true,
                    'reports' => 'advanced',
                    'multi_currency' => true,
                    'inventory' => true,
                ],
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => 500,
                'billing_cycle' => 'monthly',
                'user_limit' => null, // unlimited
                'storage_limit_mb' => 20000,
                'features' => [
                    'invoicing' => true,
                    'expenses' => true,
                    'reports' => 'advanced',
                    'multi_currency' => true,
                    'inventory' => true,
                    'api_access' => true,
                    'custom_integrations' => true,
                ],
            ],
        ],
        'routes' => [
            'integrated' => '/modules/accounting',
            'standalone' => '/apps/accounting',
        ],
        'pwa' => [
            'enabled' => true,
            'installable' => true,
            'offline_capable' => true,
        ],
        'features' => [
            'offline' => true,
            'dataSync' => true,
            'notifications' => true,
            'multiUser' => true,
        ],
    ],

    'personal-finance' => [
        'name' => 'Personal Finance Tracker',
        'slug' => 'finance',
        'category' => 'personal',
        'description' => 'Track your personal income, expenses, and savings goals',
        'icon' => 'wallet',
        'color' => 'green',
        'thumbnail' => '/images/modules/finance.png',
        'account_types' => ['member'],
        'subscription_tiers' => [
            'free' => [
                'name' => 'Free',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'features' => [
                    'expense_tracking' => true,
                    'budget_planning' => 'basic',
                    'reports' => 'basic',
                ],
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => 50,
                'billing_cycle' => 'monthly',
                'features' => [
                    'expense_tracking' => true,
                    'budget_planning' => 'advanced',
                    'reports' => 'advanced',
                    'goal_tracking' => true,
                    'investment_tracking' => true,
                ],
            ],
        ],
        'routes' => [
            'integrated' => '/modules/finance',
            'standalone' => '/apps/finance',
        ],
        'pwa' => [
            'enabled' => true,
            'installable' => true,
            'offline_capable' => true,
        ],
        'features' => [
            'offline' => true,
            'dataSync' => true,
            'notifications' => true,
        ],
    ],

    'mygrow-save' => [
        'name' => 'MyGrow Save',
        'slug' => 'mygrow-save',
        'category' => 'personal',
        'description' => 'Digital wallet for savings, transactions, and financial goals',
        'icon' => 'wallet',
        'color' => 'emerald',
        'thumbnail' => '/images/modules/mygrow-save.png',
        'account_types' => ['member', 'business'],
        'subscription_tiers' => [
            'free' => [
                'name' => 'Free',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'features' => [
                    'basic_wallet' => true,
                    'transactions' => 10,
                    'savings_goals' => false,
                ],
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => 25,
                'billing_cycle' => 'monthly',
                'features' => [
                    'unlimited_transactions' => true,
                    'savings_goals' => true,
                    'analytics' => true,
                    'auto_save' => true,
                ],
            ],
        ],
        'routes' => [
            'integrated' => '/wallet',
            'standalone' => '/apps/wallet',
        ],
        'pwa' => [
            'enabled' => true,
            'installable' => true,
            'offline_capable' => true,
        ],
        'features' => [
            'offline' => true,
            'dataSync' => true,
            'notifications' => true,
        ],
    ],

    'wedding-planner' => [
        'name' => 'Wedding Planner',
        'slug' => 'wedding-planner',
        'category' => 'personal',
        'description' => 'Plan your perfect wedding with budgets, vendors, and checklists',
        'icon' => 'heart',
        'color' => 'pink',
        'thumbnail' => '/images/modules/wedding.png',
        'account_types' => ['member'],
        'subscription_tiers' => [
            'basic' => [
                'name' => 'Basic',
                'price' => 75,
                'billing_cycle' => 'monthly',
                'features' => [
                    'checklist' => true,
                    'budget' => true,
                    'vendors' => 10,
                ],
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => 150,
                'billing_cycle' => 'monthly',
                'features' => [
                    'checklist' => true,
                    'budget' => true,
                    'vendors' => 'unlimited',
                    'guest_management' => true,
                    'seating_chart' => true,
                ],
            ],
        ],
        'routes' => [
            'integrated' => '/modules/wedding',
            'standalone' => '/apps/wedding',
        ],
        'pwa' => [
            'enabled' => true,
            'installable' => true,
            'offline_capable' => true,
        ],
        'features' => [
            'offline' => true,
            'dataSync' => true,
            'notifications' => true,
        ],
    ],

    'growstart' => [
        'name' => 'GrowStart',
        'slug' => 'growstart',
        'category' => 'sme',
        'description' => 'Guided startup journey - from idea to launch in 8 stages',
        'icon' => 'rocket',
        'color' => '#10B981',
        'thumbnail' => '/images/modules/growstart.png',
        'account_types' => ['member', 'business'],
        'subscription_tiers' => [
            'free' => [
                'name' => 'Free',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'features' => [
                    'journey_tracking' => true,
                    'basic_templates' => true,
                    'provider_directory' => true,
                    'badges' => true,
                ],
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => 99,
                'billing_cycle' => 'monthly',
                'features' => [
                    'journey_tracking' => true,
                    'all_templates' => true,
                    'provider_directory' => true,
                    'badges' => true,
                    'financial_planning' => true,
                    'collaboration' => true,
                    'priority_support' => true,
                ],
            ],
        ],
        'routes' => [
            'integrated' => '/growstart',
            'standalone' => '/apps/growstart',
        ],
        'pwa' => [
            'enabled' => true,
            'installable' => true,
            'offline_capable' => true,
        ],
        'features' => [
            'offline' => true,
            'dataSync' => true,
            'notifications' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    */

    'settings' => [
        'trial_period_days' => 14,
        'grace_period_days' => 7,
        'enable_pwa' => true,
        'enable_offline_mode' => true,
        'default_currency' => 'ZMW',
        'supported_currencies' => ['ZMW', 'USD'],
        'billing_cycles' => ['monthly', 'quarterly', 'annual'],
        'annual_discount_percent' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Categories
    |--------------------------------------------------------------------------
    */

    'categories' => [
        'core' => [
            'name' => 'Core Platform',
            'description' => 'Essential platform features included with membership',
            'icon' => 'home',
            'color' => 'blue',
        ],
        'personal' => [
            'name' => 'Personal Finance',
            'description' => 'Tools for personal financial management',
            'icon' => 'wallet',
            'color' => 'green',
        ],
        'sme' => [
            'name' => 'Business & SME',
            'description' => 'Solutions for small and medium enterprises',
            'icon' => 'briefcase',
            'color' => 'purple',
        ],
        'enterprise' => [
            'name' => 'Enterprise Solutions',
            'description' => 'Advanced tools for larger organizations',
            'icon' => 'building',
            'color' => 'indigo',
        ],
    ],
];
