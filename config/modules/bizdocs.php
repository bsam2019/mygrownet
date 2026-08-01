<?php

/**
 * BizDocs Module Configuration
 *
 * Centralized tier configuration for the BizDocs document management module.
 * Tiers, limits, features and pricing are defined here and consumed by the
 * unified subscription checkout flow (subscriptions.plans / subscriptions.checkout).
 *
 * Pricing mirrors the ModuleSeeder entry (free/starter/business/pro).
 */

return [
    'id' => 'bizdocs',
    'name' => 'BizDocs',
    'slug' => 'bizdocs',
    'description' => 'Professional document management - create invoices, receipts, quotations, and delivery notes with PDF generation and WhatsApp sharing',
    'category' => 'sme',
    'status' => 'active',
    'version' => '1.0.0',
    'account_types' => ['business', 'member'],
    'requires_subscription' => false, // Has free tier
    'icon' => 'DocumentTextIcon',
    'color' => 'blue',
    'routes' => [
        'integrated' => '/bizdocs/dashboard',
        'standalone' => '/bizdocs/dashboard',
        'setup' => '/bizdocs/setup',
    ],
    'pwa' => [
        'enabled' => true,
        'installable' => true,
    ],
    'features' => [
        'offline' => false,
        'notifications' => true,
        'requires_setup' => true,
        'pdf_generation' => true,
        'whatsapp_sharing' => true,
        'payment_tracking' => true,
    ],

    // Human-readable feature labels for display
    'feature_labels' => [
        'dashboard' => 'Dashboard',
        'documents' => 'Documents',
        'invoices' => 'Invoices',
        'receipts' => 'Receipts',
        'quotations' => 'Quotations',
        'delivery_notes' => 'Delivery Notes',
        'proforma_invoices' => 'Proforma Invoices',
        'pdf_generation' => 'PDF Generation',
        'whatsapp_sharing' => 'WhatsApp Sharing',
        'payment_tracking' => 'Payment Tracking',
        'customers' => 'Customer Management',
        'custom_templates' => 'Custom Templates',
        'team_members' => 'Team Members',
        'api_access' => 'API Access',
    ],

    // Limit labels for display
    'limit_labels' => [
        'documents' => 'Documents Per Month',
        'customers' => 'Customers',
        'team_members' => 'Team Members',
    ],

    'tiers' => [
        'free' => [
            'name' => 'Free',
            'description' => 'Basic document creation for small businesses',
            'price_monthly' => 0,
            'price_annual' => 0,
            'popular' => false,
            'sort_order' => 0,
            'limits' => [
                'documents' => 50,
                'customers' => 25,
                'team_members' => 1,
            ],
            'features' => [
                'dashboard',
                'documents',
                'invoices',
                'receipts',
                'quotations',
                'pdf_generation',
                'whatsapp_sharing',
            ],
        ],

        'starter' => [
            'name' => 'Starter',
            'description' => 'More documents and customers for growing teams',
            'price_monthly' => 49,
            'price_annual' => 470,
            'popular' => false,
            'sort_order' => 1,
            'limits' => [
                'documents' => 500,
                'customers' => 100,
                'team_members' => 1,
            ],
            'features' => [
                'dashboard',
                'documents',
                'invoices',
                'receipts',
                'quotations',
                'delivery_notes',
                'proforma_invoices',
                'pdf_generation',
                'whatsapp_sharing',
                'payment_tracking',
                'customers',
            ],
        ],

        'business' => [
            'name' => 'Business',
            'description' => 'Unlimited documents with custom templates',
            'price_monthly' => 99,
            'price_annual' => 950,
            'popular' => true,
            'sort_order' => 2,
            'limits' => [
                'documents' => -1,
                'customers' => -1,
                'team_members' => 3,
            ],
            'features' => [
                'dashboard',
                'documents',
                'invoices',
                'receipts',
                'quotations',
                'delivery_notes',
                'proforma_invoices',
                'pdf_generation',
                'whatsapp_sharing',
                'payment_tracking',
                'customers',
                'custom_templates',
                'team_members',
            ],
        ],

        'pro' => [
            'name' => 'Pro',
            'description' => 'Full power with API access and unlimited team',
            'price_monthly' => 199,
            'price_annual' => 1910,
            'popular' => false,
            'sort_order' => 3,
            'limits' => [
                'documents' => -1,
                'customers' => -1,
                'team_members' => 5,
            ],
            'features' => [
                'dashboard',
                'documents',
                'invoices',
                'receipts',
                'quotations',
                'delivery_notes',
                'proforma_invoices',
                'pdf_generation',
                'whatsapp_sharing',
                'payment_tracking',
                'customers',
                'custom_templates',
                'team_members',
                'api_access',
            ],
        ],
    ],

    // Usage metric definitions for this module
    'usage_metrics' => [
        'documents' => [
            'label' => 'Documents',
            'period' => 'monthly',
            'reset_day' => 1,
        ],
        'customers' => [
            'label' => 'Customers',
            'period' => 'lifetime',
        ],
    ],
];
