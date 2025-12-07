<?php

/**
 * GrowFinance Module Configuration
 * 
 * Centralized tier configuration for GrowFinance accounting module.
 * All tier limits, features, and pricing defined here.
 */

return [
    'id' => 'growfinance',
    'name' => 'GrowFinance',
    'slug' => 'growfinance',
    'description' => 'Simple accounting and invoicing for small businesses',
    'category' => 'sme',
    'status' => 'active',
    'version' => '1.0.0',
    'account_types' => ['sme'],
    'requires_subscription' => false, // Has free tier
    'icon' => 'CurrencyDollarIcon',
    'color' => 'emerald',
    'routes' => [
        'integrated' => '/growfinance',
        'standalone' => '/growfinance',
    ],
    'pwa' => [
        'enabled' => true,
        'installable' => true,
    ],
    'features' => [
        'offline' => false,
        'dataSync' => false,
        'notifications' => true,
        'multiUser' => true,
    ],
    
    'tiers' => [
        'free' => [
            'name' => 'Free',
            'description' => 'Get started with basic accounting',
            'price_monthly' => 0,
            'price_annual' => 0,
            'limits' => [
                'transactions_per_month' => 100,
                'invoices_per_month' => 10,
                'customers' => 20,
                'vendors' => 20,
                'bank_accounts' => 1,
                'receipt_storage_mb' => 0,
                'invoice_templates' => 1,
                'team_members' => 1,
            ],
            'features' => [
                'dashboard',
                'basic_invoicing',
                'basic_expenses',
                'email_notifications',
            ],
            'reports' => [
                'profit-loss',
                'cash-flow',
            ],
        ],
        
        'basic' => [
            'name' => 'Basic',
            'description' => 'Essential tools for growing businesses',
            'price_monthly' => 79,
            'price_annual' => 758, // 20% discount
            'limits' => [
                'transactions_per_month' => -1, // unlimited
                'invoices_per_month' => -1,
                'customers' => -1,
                'vendors' => -1,
                'bank_accounts' => 3,
                'receipt_storage_mb' => 100,
                'invoice_templates' => 3,
                'team_members' => 1,
            ],
            'features' => [
                'dashboard',
                'invoicing',
                'expenses',
                'csv_export',
                'receipt_upload',
                'email_notifications',
                'customer_management',
                'vendor_management',
            ],
            'reports' => [
                'profit-loss',
                'cash-flow',
                'balance-sheet',
                'trial-balance',
                'general-ledger',
            ],
        ],
        
        'professional' => [
            'name' => 'Professional',
            'description' => 'Advanced features for established businesses',
            'price_monthly' => 199,
            'price_annual' => 1910, // 20% discount
            'popular' => true,
            'limits' => [
                'transactions_per_month' => -1,
                'invoices_per_month' => -1,
                'customers' => -1,
                'vendors' => -1,
                'bank_accounts' => -1,
                'receipt_storage_mb' => 1024,
                'invoice_templates' => -1,
                'team_members' => 3,
            ],
            'features' => [
                'dashboard',
                'invoicing',
                'expenses',
                'csv_export',
                'pdf_export',
                'receipt_upload',
                'recurring_transactions',
                'budgets',
                'backup',
                'email_notifications',
                'customer_management',
                'vendor_management',
                'bank_reconciliation',
            ],
            'reports' => [
                'profit-loss',
                'cash-flow',
                'balance-sheet',
                'trial-balance',
                'general-ledger',
                'aged-receivables',
                'aged-payables',
            ],
        ],
        
        'business' => [
            'name' => 'Business',
            'description' => 'Complete solution for scaling businesses',
            'price_monthly' => 399,
            'price_annual' => 3830, // 20% discount
            'limits' => [
                'transactions_per_month' => -1,
                'invoices_per_month' => -1,
                'customers' => -1,
                'vendors' => -1,
                'bank_accounts' => -1,
                'receipt_storage_mb' => 5120,
                'invoice_templates' => -1,
                'team_members' => 10,
            ],
            'features' => [
                'dashboard',
                'invoicing',
                'expenses',
                'csv_export',
                'pdf_export',
                'receipt_upload',
                'recurring_transactions',
                'budgets',
                'backup',
                'multi_user',
                'api_access',
                'white_label',
                'email_notifications',
                'customer_management',
                'vendor_management',
                'bank_reconciliation',
                'audit_trail',
                'custom_reports',
            ],
            'reports' => [
                'profit-loss',
                'cash-flow',
                'balance-sheet',
                'trial-balance',
                'general-ledger',
                'aged-receivables',
                'aged-payables',
                'tax-summary',
                'custom',
            ],
        ],
    ],
    
    // Usage metric definitions for this module
    'usage_metrics' => [
        'transactions_per_month' => [
            'label' => 'Transactions',
            'period' => 'monthly',
            'reset_day' => 1,
        ],
        'invoices_per_month' => [
            'label' => 'Invoices',
            'period' => 'monthly',
            'reset_day' => 1,
        ],
        'customers' => [
            'label' => 'Customers',
            'period' => 'lifetime',
        ],
        'vendors' => [
            'label' => 'Vendors',
            'period' => 'lifetime',
        ],
        'bank_accounts' => [
            'label' => 'Bank Accounts',
            'period' => 'lifetime',
        ],
        'receipt_storage_mb' => [
            'label' => 'Receipt Storage',
            'period' => 'lifetime',
            'unit' => 'MB',
        ],
    ],
];
