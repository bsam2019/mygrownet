<?php

/**
 * BizBoost Module Configuration
 * 
 * Centralized tier configuration for BizBoost marketing module.
 * All tier limits, features, and pricing defined here.
 */

return [
    'id' => 'bizboost',
    'name' => 'BizBoost',
    'slug' => 'bizboost',
    'description' => 'Marketing & growth assistant for SMEs',
    'category' => 'marketing',
    'status' => 'active',
    'version' => '1.0.0',
    'account_types' => ['sme'],
    'requires_subscription' => false, // Has free tier
    'icon' => 'MegaphoneIcon',
    'color' => 'violet',
    'routes' => [
        'integrated' => '/bizboost',
        'standalone' => '/bizboost',
    ],
    'pwa' => [
        'enabled' => true,
        'installable' => true,
    ],
    'features' => [
        'offline' => true,
        'dataSync' => true,
        'notifications' => true,
        'multiUser' => true,
    ],
    
    // Human-readable feature labels for display
    'feature_labels' => [
        'dashboard' => 'Dashboard',
        'basic_templates' => 'Basic Templates',
        'templates' => 'Marketing Templates',
        'template_editor' => 'Template Editor',
        'mini_website' => 'Mini Website',
        'manual_sharing' => 'Manual Sharing',
        'customer_list' => 'Customer List',
        'customer_management' => 'Customer Management',
        'email_notifications' => 'Email Notifications',
        'scheduling' => 'Post Scheduling',
        'basic_analytics' => 'Basic Analytics',
        'advanced_analytics' => 'Advanced Analytics',
        'industry_kits' => 'Industry Kits',
        'product_catalog' => 'Product Catalog',
        'auto_posting' => 'Auto Posting',
        'auto_campaigns' => 'Auto Campaigns',
        'sales_tracking' => 'Sales Tracking',
        'whatsapp_tools' => 'WhatsApp Tools',
        'qr_codes' => 'QR Codes',
        'marketing_calendar' => 'Marketing Calendar',
        'ai_content_generator' => 'AI Content Generator',
        'social_integrations' => 'Social Integrations',
        'multi_location' => 'Multi-Location',
        'team_accounts' => 'Team Accounts',
        'api_access' => 'API Access',
        'white_label' => 'White Label',
        'supplier_directory' => 'Supplier Directory',
        'certificates' => 'Certificates',
        'learning_hub' => 'Learning Hub',
        'ai_advisor' => 'AI Advisor',
        'custom_reports' => 'Custom Reports',
    ],
    
    // Limit labels for display
    'limit_labels' => [
        'posts_per_month' => 'Posts Per Month',
        'templates' => 'Templates',
        'ai_credits_per_month' => 'AI Credits Per Month',
        'customers' => 'Customers',
        'products' => 'Products',
        'campaigns' => 'Campaigns',
        'storage_mb' => 'Storage (MB)',
        'team_members' => 'Team Members',
        'locations' => 'Locations',
    ],
    
    'tiers' => [
        'free' => [
            'name' => 'Free',
            'description' => 'Get started with basic marketing tools',
            'price_monthly' => 0,
            'price_annual' => 0,
            'limits' => [
                'posts_per_month' => 10,
                'templates' => 5,
                'ai_credits_per_month' => 10,
                'customers' => 20,
                'products' => 10,
                'campaigns' => 0,
                'storage_mb' => 0,
                'team_members' => 1,
                'locations' => 1,
            ],
            'features' => [
                'dashboard',
                'basic_templates',
                'mini_website',
                'manual_sharing',
                'customer_list',
                'email_notifications',
            ],
        ],
        
        'basic' => [
            'name' => 'Basic',
            'description' => 'Essential marketing tools for growing businesses',
            'price_monthly' => 79,
            'price_annual' => 758, // 20% discount
            'limits' => [
                'posts_per_month' => 50,
                'templates' => 25,
                'ai_credits_per_month' => 50,
                'customers' => -1, // unlimited
                'products' => -1,
                'campaigns' => 3,
                'storage_mb' => 100,
                'team_members' => 1,
                'locations' => 1,
            ],
            'features' => [
                'dashboard',
                'templates',
                'mini_website',
                'scheduling',
                'customer_management',
                'basic_analytics',
                'industry_kits',
                'email_notifications',
                'product_catalog',
            ],
        ],
        
        'professional' => [
            'name' => 'Professional',
            'description' => 'Advanced marketing for established businesses',
            'price_monthly' => 199,
            'price_annual' => 1910, // 20% discount
            'popular' => true,
            'limits' => [
                'posts_per_month' => -1, // unlimited
                'templates' => -1,
                'ai_credits_per_month' => 200,
                'customers' => -1,
                'products' => -1,
                'campaigns' => -1,
                'storage_mb' => 1024,
                'team_members' => 3,
                'locations' => 1,
            ],
            'features' => [
                'dashboard',
                'templates',
                'template_editor',
                'mini_website',
                'auto_posting',
                'auto_campaigns',
                'customer_management',
                'sales_tracking',
                'advanced_analytics',
                'whatsapp_tools',
                'qr_codes',
                'email_notifications',
                'product_catalog',
                'marketing_calendar',
                'ai_content_generator',
                'social_integrations',
            ],
        ],
        
        'business' => [
            'name' => 'Business',
            'description' => 'Complete marketing solution for scaling businesses',
            'price_monthly' => 399,
            'price_annual' => 3830, // 20% discount
            'limits' => [
                'posts_per_month' => -1,
                'templates' => -1,
                'ai_credits_per_month' => -1, // unlimited
                'customers' => -1,
                'products' => -1,
                'campaigns' => -1,
                'storage_mb' => 5120,
                'team_members' => 10,
                'locations' => 5,
            ],
            'features' => [
                'dashboard',
                'templates',
                'template_editor',
                'mini_website',
                'auto_posting',
                'auto_campaigns',
                'customer_management',
                'sales_tracking',
                'advanced_analytics',
                'whatsapp_tools',
                'qr_codes',
                'multi_location',
                'team_accounts',
                'api_access',
                'white_label',
                'supplier_directory',
                'certificates',
                'email_notifications',
                'product_catalog',
                'marketing_calendar',
                'ai_content_generator',
                'social_integrations',
                'learning_hub',
                'ai_advisor',
                'custom_reports',
            ],
        ],
    ],
    
    // Usage metric definitions for this module
    'usage_metrics' => [
        'posts_per_month' => [
            'label' => 'Posts',
            'period' => 'monthly',
            'reset_day' => 1,
        ],
        'ai_credits_per_month' => [
            'label' => 'AI Credits',
            'period' => 'monthly',
            'reset_day' => 1,
        ],
        'templates' => [
            'label' => 'Custom Templates',
            'period' => 'lifetime',
        ],
        'customers' => [
            'label' => 'Customers',
            'period' => 'lifetime',
        ],
        'products' => [
            'label' => 'Products',
            'period' => 'lifetime',
        ],
        'campaigns' => [
            'label' => 'Campaigns',
            'period' => 'lifetime',
        ],
        'storage_mb' => [
            'label' => 'Storage',
            'period' => 'lifetime',
            'unit' => 'MB',
        ],
        'team_members' => [
            'label' => 'Team Members',
            'period' => 'lifetime',
        ],
        'locations' => [
            'label' => 'Locations',
            'period' => 'lifetime',
        ],
    ],
    
    // Industry kits available
    'industry_kits' => [
        'boutique' => 'Boutique & Fashion',
        'salon' => 'Salon & Beauty',
        'barbershop' => 'Barbershop',
        'restaurant' => 'Restaurant & Food',
        'grocery' => 'Grocery & Retail',
        'hardware' => 'Hardware Store',
        'photography' => 'Photography',
        'mobile_money' => 'Mobile Money Booth',
        'electronics' => 'Electronics',
        'general' => 'General Business',
    ],
];
