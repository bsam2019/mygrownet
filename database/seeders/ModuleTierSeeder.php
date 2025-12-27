<?php

namespace Database\Seeders;

use App\Models\ModuleTier;
use App\Models\ModuleTierFeature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleTierSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedGrowBizTiers();
            $this->seedBizBoostTiers();
            $this->seedGrowFinanceTiers();
            $this->seedLifePlusTiers();
            $this->seedGrowBuilderTiers();
        });
    }

    private function seedGrowBizTiers(): void
    {
        $moduleId = 'growbiz';
        
        $tiers = [
            [
                'tier_key' => 'free',
                'name' => 'Free',
                'description' => 'Get started with basic features',
                'price_monthly' => 0,
                'price_annual' => 0,
                'user_limit' => 1,
                'is_default' => true,
                'sort_order' => 0,
                'features' => [
                    ['key' => 'business_profiles', 'name' => 'Business Profiles', 'type' => 'limit', 'limit' => 1],
                    ['key' => 'employees', 'name' => 'Employees', 'type' => 'limit', 'limit' => 5],
                    ['key' => 'basic_pos', 'name' => 'Basic POS', 'type' => 'boolean', 'value' => true],
                    ['key' => 'basic_reports', 'name' => 'Basic Reports', 'type' => 'boolean', 'value' => true],
                    ['key' => 'inventory_management', 'name' => 'Inventory Management', 'type' => 'boolean', 'value' => false],
                    ['key' => 'advanced_analytics', 'name' => 'Advanced Analytics', 'type' => 'boolean', 'value' => false],
                    ['key' => 'priority_support', 'name' => 'Priority Support', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'starter',
                'name' => 'Starter',
                'description' => 'Perfect for small businesses',
                'price_monthly' => 50,
                'price_annual' => 500,
                'user_limit' => 10,
                'sort_order' => 1,
                'features' => [
                    ['key' => 'business_profiles', 'name' => 'Business Profiles', 'type' => 'limit', 'limit' => 1],
                    ['key' => 'employees', 'name' => 'Employees', 'type' => 'limit', 'limit' => 10],
                    ['key' => 'full_pos', 'name' => 'Full POS Features', 'type' => 'boolean', 'value' => true],
                    ['key' => 'inventory_management', 'name' => 'Inventory Management', 'type' => 'boolean', 'value' => true],
                    ['key' => 'basic_reports', 'name' => 'Basic Reports', 'type' => 'boolean', 'value' => true],
                    ['key' => 'advanced_analytics', 'name' => 'Advanced Analytics', 'type' => 'boolean', 'value' => false],
                    ['key' => 'priority_support', 'name' => 'Priority Support', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'professional',
                'name' => 'Professional',
                'description' => 'For growing businesses',
                'price_monthly' => 150,
                'price_annual' => 1500,
                'user_limit' => null,
                'sort_order' => 2,
                'features' => [
                    ['key' => 'business_profiles', 'name' => 'Business Profiles', 'type' => 'limit', 'limit' => 3],
                    ['key' => 'employees', 'name' => 'Employees', 'type' => 'limit', 'limit' => null],
                    ['key' => 'full_pos', 'name' => 'Full POS Features', 'type' => 'boolean', 'value' => true],
                    ['key' => 'inventory_management', 'name' => 'Inventory Management', 'type' => 'boolean', 'value' => true],
                    ['key' => 'advanced_analytics', 'name' => 'Advanced Analytics', 'type' => 'boolean', 'value' => true],
                    ['key' => 'custom_reports', 'name' => 'Custom Reports', 'type' => 'boolean', 'value' => true],
                    ['key' => 'priority_support', 'name' => 'Priority Support', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'enterprise',
                'name' => 'Enterprise',
                'description' => 'For large organizations',
                'price_monthly' => 300,
                'price_annual' => 3000,
                'user_limit' => null,
                'sort_order' => 3,
                'features' => [
                    ['key' => 'business_profiles', 'name' => 'Business Profiles', 'type' => 'limit', 'limit' => null],
                    ['key' => 'employees', 'name' => 'Employees', 'type' => 'limit', 'limit' => null],
                    ['key' => 'full_pos', 'name' => 'Full POS Features', 'type' => 'boolean', 'value' => true],
                    ['key' => 'inventory_management', 'name' => 'Inventory Management', 'type' => 'boolean', 'value' => true],
                    ['key' => 'advanced_analytics', 'name' => 'Advanced Analytics', 'type' => 'boolean', 'value' => true],
                    ['key' => 'custom_reports', 'name' => 'Custom Reports', 'type' => 'boolean', 'value' => true],
                    ['key' => 'priority_support', 'name' => 'Priority Support', 'type' => 'boolean', 'value' => true],
                    ['key' => 'api_access', 'name' => 'API Access', 'type' => 'boolean', 'value' => true],
                ],
            ],
        ];

        $this->createTiersWithFeatures($moduleId, $tiers);
    }


    private function seedBizBoostTiers(): void
    {
        $moduleId = 'bizboost';
        
        $tiers = [
            [
                'tier_key' => 'free',
                'name' => 'Free',
                'description' => 'Get started with marketing basics',
                'price_monthly' => 0,
                'price_annual' => 0,
                'is_default' => true,
                'sort_order' => 0,
                'features' => [
                    ['key' => 'business_profiles', 'name' => 'Business Profiles', 'type' => 'limit', 'limit' => 1],
                    ['key' => 'posts_per_month', 'name' => 'Posts/Month', 'type' => 'limit', 'limit' => 10],
                    ['key' => 'basic_templates', 'name' => 'Basic Templates', 'type' => 'boolean', 'value' => true],
                    ['key' => 'social_media', 'name' => 'Social Media Integration', 'type' => 'boolean', 'value' => false],
                    ['key' => 'ai_content', 'name' => 'AI Content Generator', 'type' => 'boolean', 'value' => false],
                    ['key' => 'analytics', 'name' => 'Analytics Dashboard', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'starter',
                'name' => 'Starter',
                'description' => 'For small businesses starting out',
                'price_monthly' => 79,
                'price_annual' => 790,
                'sort_order' => 1,
                'features' => [
                    ['key' => 'business_profiles', 'name' => 'Business Profiles', 'type' => 'limit', 'limit' => 1],
                    ['key' => 'posts_per_month', 'name' => 'Posts/Month', 'type' => 'limit', 'limit' => 50],
                    ['key' => 'all_templates', 'name' => 'All Templates', 'type' => 'boolean', 'value' => true],
                    ['key' => 'social_media', 'name' => 'Social Media Integration', 'type' => 'boolean', 'value' => true],
                    ['key' => 'ai_content', 'name' => 'AI Content Generator', 'type' => 'boolean', 'value' => false],
                    ['key' => 'analytics', 'name' => 'Analytics Dashboard', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'professional',
                'name' => 'Professional',
                'description' => 'For growing businesses',
                'price_monthly' => 199,
                'price_annual' => 1990,
                'sort_order' => 2,
                'features' => [
                    ['key' => 'business_profiles', 'name' => 'Business Profiles', 'type' => 'limit', 'limit' => 3],
                    ['key' => 'posts_per_month', 'name' => 'Posts/Month', 'type' => 'limit', 'limit' => null],
                    ['key' => 'all_templates', 'name' => 'All Templates', 'type' => 'boolean', 'value' => true],
                    ['key' => 'social_media', 'name' => 'Social Media Integration', 'type' => 'boolean', 'value' => true],
                    ['key' => 'ai_content', 'name' => 'AI Content Generator', 'type' => 'boolean', 'value' => true],
                    ['key' => 'analytics', 'name' => 'Analytics Dashboard', 'type' => 'boolean', 'value' => true],
                ],
            ],
            [
                'tier_key' => 'business',
                'name' => 'Business',
                'description' => 'For agencies & enterprises',
                'price_monthly' => 399,
                'price_annual' => 3990,
                'sort_order' => 3,
                'features' => [
                    ['key' => 'business_profiles', 'name' => 'Business Profiles', 'type' => 'limit', 'limit' => null],
                    ['key' => 'posts_per_month', 'name' => 'Posts/Month', 'type' => 'limit', 'limit' => null],
                    ['key' => 'custom_templates', 'name' => 'Custom Templates', 'type' => 'boolean', 'value' => true],
                    ['key' => 'all_integrations', 'name' => 'All Integrations', 'type' => 'boolean', 'value' => true],
                    ['key' => 'ai_content', 'name' => 'AI Content Generator', 'type' => 'boolean', 'value' => true],
                    ['key' => 'white_label', 'name' => 'White-label Options', 'type' => 'boolean', 'value' => true],
                ],
            ],
        ];

        $this->createTiersWithFeatures($moduleId, $tiers);
    }

    private function seedGrowFinanceTiers(): void
    {
        $moduleId = 'growfinance';
        
        $tiers = [
            [
                'tier_key' => 'free',
                'name' => 'Free',
                'description' => 'Basic financial tracking',
                'price_monthly' => 0,
                'price_annual' => 0,
                'is_default' => true,
                'sort_order' => 0,
                'features' => [
                    ['key' => 'transactions', 'name' => 'Transactions/Month', 'type' => 'limit', 'limit' => 50],
                    ['key' => 'invoices', 'name' => 'Invoices/Month', 'type' => 'limit', 'limit' => 5],
                    ['key' => 'basic_reports', 'name' => 'Basic Reports', 'type' => 'boolean', 'value' => true],
                    ['key' => 'recurring', 'name' => 'Recurring Transactions', 'type' => 'boolean', 'value' => false],
                    ['key' => 'budgets', 'name' => 'Budget Tracking', 'type' => 'boolean', 'value' => false],
                    ['key' => 'pdf_exports', 'name' => 'PDF Exports', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'basic',
                'name' => 'Basic',
                'description' => 'For freelancers & small businesses',
                'price_monthly' => 49,
                'price_annual' => 490,
                'sort_order' => 1,
                'features' => [
                    ['key' => 'transactions', 'name' => 'Transactions/Month', 'type' => 'limit', 'limit' => 500],
                    ['key' => 'invoices', 'name' => 'Invoices/Month', 'type' => 'limit', 'limit' => 50],
                    ['key' => 'basic_reports', 'name' => 'Basic Reports', 'type' => 'boolean', 'value' => true],
                    ['key' => 'recurring', 'name' => 'Recurring Transactions', 'type' => 'boolean', 'value' => false],
                    ['key' => 'budgets', 'name' => 'Budget Tracking', 'type' => 'boolean', 'value' => false],
                    ['key' => 'pdf_exports', 'name' => 'PDF Exports', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'professional',
                'name' => 'Professional',
                'description' => 'For growing businesses',
                'price_monthly' => 149,
                'price_annual' => 1490,
                'sort_order' => 2,
                'features' => [
                    ['key' => 'transactions', 'name' => 'Transactions/Month', 'type' => 'limit', 'limit' => null],
                    ['key' => 'invoices', 'name' => 'Invoices/Month', 'type' => 'limit', 'limit' => null],
                    ['key' => 'advanced_reports', 'name' => 'Advanced Reports', 'type' => 'boolean', 'value' => true],
                    ['key' => 'recurring', 'name' => 'Recurring Transactions', 'type' => 'boolean', 'value' => true],
                    ['key' => 'budgets', 'name' => 'Budget Tracking', 'type' => 'boolean', 'value' => true],
                    ['key' => 'pdf_exports', 'name' => 'PDF Exports', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'business',
                'name' => 'Business',
                'description' => 'For established businesses',
                'price_monthly' => 299,
                'price_annual' => 2990,
                'sort_order' => 3,
                'features' => [
                    ['key' => 'transactions', 'name' => 'Transactions/Month', 'type' => 'limit', 'limit' => null],
                    ['key' => 'invoices', 'name' => 'Invoices/Month', 'type' => 'limit', 'limit' => null],
                    ['key' => 'advanced_reports', 'name' => 'Advanced Reports', 'type' => 'boolean', 'value' => true],
                    ['key' => 'recurring', 'name' => 'Recurring Transactions', 'type' => 'boolean', 'value' => true],
                    ['key' => 'budgets', 'name' => 'Budget Tracking', 'type' => 'boolean', 'value' => true],
                    ['key' => 'pdf_exports', 'name' => 'PDF Exports', 'type' => 'boolean', 'value' => true],
                    ['key' => 'multi_currency', 'name' => 'Multi-Currency', 'type' => 'boolean', 'value' => true],
                ],
            ],
        ];

        $this->createTiersWithFeatures($moduleId, $tiers);
    }

    private function seedLifePlusTiers(): void
    {
        $moduleId = 'lifeplus';
        
        $tiers = [
            [
                'tier_key' => 'free',
                'name' => 'Free',
                'description' => 'Basic life management tools',
                'price_monthly' => 0,
                'price_annual' => 0,
                'is_default' => true,
                'sort_order' => 0,
                'features' => [
                    ['key' => 'tasks', 'name' => 'Tasks', 'type' => 'limit', 'limit' => 10],
                    ['key' => 'habits', 'name' => 'Habit Trackers', 'type' => 'limit', 'limit' => 1],
                    ['key' => 'expense_tracking', 'name' => 'Basic Expense Tracking', 'type' => 'boolean', 'value' => true],
                    ['key' => 'chilimba_groups', 'name' => 'Chilimba Groups', 'type' => 'limit', 'limit' => 0],
                    ['key' => 'community_posting', 'name' => 'Community Posting', 'type' => 'boolean', 'value' => false],
                    ['key' => 'budget_planning', 'name' => 'Budget Planning', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'premium',
                'name' => 'Premium',
                'description' => 'Full access to all Life+ features',
                'price_monthly' => 25,
                'price_annual' => 250,
                'sort_order' => 1,
                'features' => [
                    ['key' => 'tasks', 'name' => 'Tasks', 'type' => 'limit', 'limit' => null],
                    ['key' => 'habits', 'name' => 'Habit Trackers', 'type' => 'limit', 'limit' => null],
                    ['key' => 'expense_tracking', 'name' => 'Full Expense Tracking', 'type' => 'boolean', 'value' => true],
                    ['key' => 'chilimba_groups', 'name' => 'Chilimba Groups', 'type' => 'limit', 'limit' => 2],
                    ['key' => 'community_posting', 'name' => 'Community Posting', 'type' => 'boolean', 'value' => true],
                    ['key' => 'budget_planning', 'name' => 'Budget Planning & Analytics', 'type' => 'boolean', 'value' => true],
                ],
            ],
            [
                'tier_key' => 'member_free',
                'name' => 'MyGrowNet Member',
                'description' => 'Included with MyGrowNet membership',
                'price_monthly' => 0,
                'price_annual' => 0,
                'sort_order' => 2,
                'features' => [
                    ['key' => 'tasks', 'name' => 'Tasks', 'type' => 'limit', 'limit' => null],
                    ['key' => 'habits', 'name' => 'Habit Trackers', 'type' => 'limit', 'limit' => null],
                    ['key' => 'expense_tracking', 'name' => 'Full Expense Tracking', 'type' => 'boolean', 'value' => true],
                    ['key' => 'chilimba_groups', 'name' => 'Chilimba Groups', 'type' => 'limit', 'limit' => null],
                    ['key' => 'community_posting', 'name' => 'Community Posting', 'type' => 'boolean', 'value' => true],
                    ['key' => 'mygrownet_integration', 'name' => 'MyGrowNet Integration', 'type' => 'boolean', 'value' => true],
                    ['key' => 'earnings_tracking', 'name' => 'Earnings Tracking', 'type' => 'boolean', 'value' => true],
                ],
            ],
            [
                'tier_key' => 'elite',
                'name' => 'Elite',
                'description' => 'For Professional+ MyGrowNet members',
                'price_monthly' => 0,
                'price_annual' => 0,
                'sort_order' => 3,
                'features' => [
                    ['key' => 'all_member_features', 'name' => 'All Member Features', 'type' => 'boolean', 'value' => true],
                    ['key' => 'priority_support', 'name' => 'Priority Support', 'type' => 'boolean', 'value' => true],
                    ['key' => 'advanced_analytics', 'name' => 'Advanced Analytics', 'type' => 'boolean', 'value' => true],
                    ['key' => 'early_access', 'name' => 'Early Access to Features', 'type' => 'boolean', 'value' => true],
                ],
            ],
        ];

        $this->createTiersWithFeatures($moduleId, $tiers);
    }

    private function seedGrowBuilderTiers(): void
    {
        $moduleId = 'growbuilder';
        
        $tiers = [
            [
                'tier_key' => 'free',
                'name' => 'Free',
                'description' => 'Get started with a basic website',
                'price_monthly' => 0,
                'price_annual' => 0,
                'is_default' => true,
                'sort_order' => 0,
                'features' => [
                    ['key' => 'sites', 'name' => 'Websites', 'type' => 'limit', 'limit' => 1],
                    ['key' => 'pages_per_site', 'name' => 'Pages per Site', 'type' => 'limit', 'limit' => 3],
                    ['key' => 'storage_mb', 'name' => 'Storage (MB)', 'type' => 'limit', 'limit' => 50],
                    ['key' => 'custom_domain', 'name' => 'Custom Domain', 'type' => 'boolean', 'value' => false],
                    ['key' => 'ssl_certificate', 'name' => 'SSL Certificate', 'type' => 'boolean', 'value' => true],
                    ['key' => 'basic_templates', 'name' => 'Basic Templates', 'type' => 'boolean', 'value' => true],
                    ['key' => 'ai_content', 'name' => 'AI Content Generation', 'type' => 'boolean', 'value' => false],
                    ['key' => 'contact_forms', 'name' => 'Contact Forms', 'type' => 'boolean', 'value' => true],
                    ['key' => 'blog', 'name' => 'Blog', 'type' => 'boolean', 'value' => false],
                    ['key' => 'member_area', 'name' => 'Member Area', 'type' => 'boolean', 'value' => false],
                    ['key' => 'ecommerce', 'name' => 'E-commerce', 'type' => 'boolean', 'value' => false],
                    ['key' => 'analytics', 'name' => 'Analytics', 'type' => 'boolean', 'value' => false],
                    ['key' => 'remove_branding', 'name' => 'Remove Branding', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'member',
                'name' => 'Member',
                'description' => 'Free for MyGrowNet members',
                'price_monthly' => 0,
                'price_annual' => 0,
                'sort_order' => 1,
                'features' => [
                    ['key' => 'sites', 'name' => 'Websites', 'type' => 'limit', 'limit' => 2],
                    ['key' => 'pages_per_site', 'name' => 'Pages per Site', 'type' => 'limit', 'limit' => 10],
                    ['key' => 'storage_mb', 'name' => 'Storage (MB)', 'type' => 'limit', 'limit' => 200],
                    ['key' => 'custom_domain', 'name' => 'Custom Domain', 'type' => 'boolean', 'value' => true],
                    ['key' => 'ssl_certificate', 'name' => 'SSL Certificate', 'type' => 'boolean', 'value' => true],
                    ['key' => 'all_templates', 'name' => 'All Templates', 'type' => 'boolean', 'value' => true],
                    ['key' => 'ai_content', 'name' => 'AI Content Generation', 'type' => 'limit', 'limit' => 10],
                    ['key' => 'contact_forms', 'name' => 'Contact Forms', 'type' => 'boolean', 'value' => true],
                    ['key' => 'blog', 'name' => 'Blog', 'type' => 'boolean', 'value' => true],
                    ['key' => 'member_area', 'name' => 'Member Area', 'type' => 'boolean', 'value' => true],
                    ['key' => 'ecommerce', 'name' => 'E-commerce', 'type' => 'boolean', 'value' => false],
                    ['key' => 'analytics', 'name' => 'Analytics', 'type' => 'boolean', 'value' => true],
                    ['key' => 'remove_branding', 'name' => 'Remove Branding', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'standard',
                'name' => 'Standard',
                'description' => 'Perfect for professionals & small businesses',
                'price_monthly' => 49,
                'price_annual' => 490,
                'sort_order' => 2,
                'features' => [
                    ['key' => 'sites', 'name' => 'Websites', 'type' => 'limit', 'limit' => 5],
                    ['key' => 'pages_per_site', 'name' => 'Pages per Site', 'type' => 'limit', 'limit' => null],
                    ['key' => 'storage_mb', 'name' => 'Storage (MB)', 'type' => 'limit', 'limit' => 1000],
                    ['key' => 'custom_domain', 'name' => 'Custom Domain', 'type' => 'boolean', 'value' => true],
                    ['key' => 'ssl_certificate', 'name' => 'SSL Certificate', 'type' => 'boolean', 'value' => true],
                    ['key' => 'all_templates', 'name' => 'All Templates', 'type' => 'boolean', 'value' => true],
                    ['key' => 'ai_content', 'name' => 'AI Content Generation', 'type' => 'limit', 'limit' => 50],
                    ['key' => 'contact_forms', 'name' => 'Contact Forms', 'type' => 'boolean', 'value' => true],
                    ['key' => 'blog', 'name' => 'Blog', 'type' => 'boolean', 'value' => true],
                    ['key' => 'member_area', 'name' => 'Member Area', 'type' => 'boolean', 'value' => true],
                    ['key' => 'ecommerce', 'name' => 'E-commerce', 'type' => 'boolean', 'value' => false],
                    ['key' => 'analytics', 'name' => 'Advanced Analytics', 'type' => 'boolean', 'value' => true],
                    ['key' => 'remove_branding', 'name' => 'Remove Branding', 'type' => 'boolean', 'value' => true],
                    ['key' => 'priority_support', 'name' => 'Priority Support', 'type' => 'boolean', 'value' => true],
                ],
            ],
            [
                'tier_key' => 'ecommerce',
                'name' => 'E-commerce',
                'description' => 'Full online store capabilities',
                'price_monthly' => 99,
                'price_annual' => 990,
                'sort_order' => 3,
                'features' => [
                    ['key' => 'sites', 'name' => 'Websites', 'type' => 'limit', 'limit' => 10],
                    ['key' => 'pages_per_site', 'name' => 'Pages per Site', 'type' => 'limit', 'limit' => null],
                    ['key' => 'storage_mb', 'name' => 'Storage (MB)', 'type' => 'limit', 'limit' => 5000],
                    ['key' => 'custom_domain', 'name' => 'Custom Domain', 'type' => 'boolean', 'value' => true],
                    ['key' => 'ssl_certificate', 'name' => 'SSL Certificate', 'type' => 'boolean', 'value' => true],
                    ['key' => 'all_templates', 'name' => 'All Templates', 'type' => 'boolean', 'value' => true],
                    ['key' => 'ai_content', 'name' => 'AI Content Generation', 'type' => 'limit', 'limit' => null],
                    ['key' => 'contact_forms', 'name' => 'Contact Forms', 'type' => 'boolean', 'value' => true],
                    ['key' => 'blog', 'name' => 'Blog', 'type' => 'boolean', 'value' => true],
                    ['key' => 'member_area', 'name' => 'Member Area', 'type' => 'boolean', 'value' => true],
                    ['key' => 'ecommerce', 'name' => 'E-commerce', 'type' => 'boolean', 'value' => true],
                    ['key' => 'products', 'name' => 'Products', 'type' => 'limit', 'limit' => null],
                    ['key' => 'payment_gateways', 'name' => 'Payment Gateways', 'type' => 'boolean', 'value' => true],
                    ['key' => 'inventory_management', 'name' => 'Inventory Management', 'type' => 'boolean', 'value' => true],
                    ['key' => 'analytics', 'name' => 'Advanced Analytics', 'type' => 'boolean', 'value' => true],
                    ['key' => 'remove_branding', 'name' => 'Remove Branding', 'type' => 'boolean', 'value' => true],
                    ['key' => 'priority_support', 'name' => 'Priority Support', 'type' => 'boolean', 'value' => true],
                ],
            ],
        ];

        $this->createTiersWithFeatures($moduleId, $tiers);
    }


    private function createTiersWithFeatures(string $moduleId, array $tiers): void
    {
        foreach ($tiers as $tierData) {
            $features = $tierData['features'] ?? [];
            unset($tierData['features']);

            $tier = ModuleTier::updateOrCreate(
                [
                    'module_id' => $moduleId,
                    'tier_key' => $tierData['tier_key'],
                ],
                array_merge($tierData, [
                    'module_id' => $moduleId,
                    'is_active' => true,
                ])
            );

            // Clear existing features and recreate
            $tier->features()->delete();

            foreach ($features as $feature) {
                ModuleTierFeature::create([
                    'module_tier_id' => $tier->id,
                    'feature_key' => $feature['key'],
                    'feature_name' => $feature['name'],
                    'feature_type' => $feature['type'],
                    'value_boolean' => $feature['type'] === 'boolean' ? ($feature['value'] ?? false) : false,
                    'value_limit' => $feature['type'] === 'limit' ? $feature['limit'] : null,
                    'value_text' => $feature['type'] === 'text' ? ($feature['text'] ?? null) : null,
                    'is_active' => true,
                ]);
            }
        }
    }
}
