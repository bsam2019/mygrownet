<?php

/**
 * QuickInvoice Module Configuration
 *
 * Centralized tier configuration for the QuickInvoice module.
 * Tiers, limits, features and pricing are defined here and consumed by the
 * unified subscription checkout flow (subscriptions.plans / subscriptions.checkout).
 *
 * Pricing mirrors the QuickInvoiceSubscriptionTiersSeeder (Free/Basic/Pro/Enterprise).
 */

return [
    'id' => 'quickinvoice',
    'name' => 'QuickInvoice',
    'slug' => 'quickinvoice',
    'description' => 'Fast, professional invoicing - create invoices, quotations, receipts, and delivery notes with PDF sharing',
    'category' => 'sme',
    'status' => 'active',
    'version' => '1.0.0',
    'account_types' => ['business', 'member'],
    'requires_subscription' => false, // Has free tier
    'icon' => 'DocumentTextIcon',
    'color' => 'indigo',
    'routes' => [
        'integrated' => '/quick-invoice',
        'standalone' => '/quick-invoice',
    ],
    'pwa' => [
        'enabled' => true,
        'installable' => true,
    ],
    'features' => [
        'offline' => false,
        'notifications' => false,
        'requires_setup' => false,
        'pdf_generation' => true,
        'whatsapp_sharing' => true,
        'email_sharing' => true,
        'design_studio' => true,
    ],

    // Human-readable feature labels for display
    'feature_labels' => [
        'dashboard' => 'Dashboard',
        'documents' => 'Documents',
        'invoices' => 'Invoices',
        'quotations' => 'Quotations',
        'receipts' => 'Receipts',
        'delivery_notes' => 'Delivery Notes',
        'pdf_generation' => 'PDF Generation',
        'whatsapp_sharing' => 'WhatsApp Sharing',
        'email_sharing' => 'Email Sharing',
        'design_studio' => 'Design Studio',
        'custom_templates' => 'Custom Templates',
        'custom_branding' => 'Custom Branding',
        'white_label' => 'White Label',
        'advanced_analytics' => 'Advanced Analytics',
        'cms_integration' => 'CMS Integration',
        'api_access' => 'API Access',
        'priority_support' => 'Priority Support',
    ],

    // Limit labels for display
    'limit_labels' => [
        'documents_per_month' => 'Documents Per Month',
        'team_members' => 'Team Members',
    ],

    'tiers' => [
        'free' => [
            'name' => 'Free',
            'description' => 'Unlimited documents to get you started',
            'price_monthly' => 0,
            'price_annual' => 0,
            'popular' => false,
            'sort_order' => 0,
            'limits' => [
                'documents_per_month' => -1,
                'team_members' => 1,
            ],
            'features' => [
                'dashboard',
                'documents',
                'invoices',
                'quotations',
                'receipts',
                'delivery_notes',
                'pdf_generation',
                'whatsapp_sharing',
                'email_sharing',
            ],
        ],

        'basic' => [
            'name' => 'Basic',
            'description' => 'Affordable plan for growing businesses',
            'price_monthly' => 50,
            'price_annual' => 480,
            'popular' => false,
            'sort_order' => 1,
            'limits' => [
                'documents_per_month' => 25,
                'team_members' => 1,
            ],
            'features' => [
                'dashboard',
                'documents',
                'invoices',
                'quotations',
                'receipts',
                'delivery_notes',
                'pdf_generation',
                'whatsapp_sharing',
                'email_sharing',
            ],
        ],

        'pro' => [
            'name' => 'Pro',
            'description' => 'Design Studio and custom branding included',
            'price_monthly' => 150,
            'price_annual' => 1440,
            'popular' => true,
            'sort_order' => 2,
            'limits' => [
                'documents_per_month' => 100,
                'team_members' => 3,
            ],
            'features' => [
                'dashboard',
                'documents',
                'invoices',
                'quotations',
                'receipts',
                'delivery_notes',
                'pdf_generation',
                'whatsapp_sharing',
                'email_sharing',
                'design_studio',
                'custom_templates',
                'custom_branding',
                'priority_support',
            ],
        ],

        'enterprise' => [
            'name' => 'Enterprise',
            'description' => 'Unlimited everything for large teams',
            'price_monthly' => 500,
            'price_annual' => 4800,
            'popular' => false,
            'sort_order' => 3,
            'limits' => [
                'documents_per_month' => -1,
                'team_members' => 10,
            ],
            'features' => [
                'dashboard',
                'documents',
                'invoices',
                'quotations',
                'receipts',
                'delivery_notes',
                'pdf_generation',
                'whatsapp_sharing',
                'email_sharing',
                'design_studio',
                'custom_templates',
                'custom_branding',
                'white_label',
                'advanced_analytics',
                'cms_integration',
                'api_access',
                'priority_support',
            ],
        ],
    ],

    // Usage metric definitions for this module
    'usage_metrics' => [
        'documents_per_month' => [
            'label' => 'Documents',
            'period' => 'monthly',
            'reset_day' => 1,
        ],
    ],
];
