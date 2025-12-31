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
        
        // Pricing per documentation: Free=0, Starter=K120, Business=K350, Agency=K900
        // Features match the approved UI design exactly
        $tiers = [
            [
                'tier_key' => 'free',
                'name' => 'Free',
                'description' => 'For testing and exploration',
                'price_monthly' => 0,
                'price_annual' => 0,
                'is_default' => true,
                'sort_order' => 0,
                'features' => [
                    ['key' => 'websites', 'name' => '1 website', 'type' => 'boolean', 'value' => true],
                    ['key' => 'subdomain', 'name' => 'Subdomain: username.mygrownet.com', 'type' => 'boolean', 'value' => true],
                    ['key' => 'templates', 'name' => 'Limited templates', 'type' => 'boolean', 'value' => true],
                    ['key' => 'storage', 'name' => '500MB storage', 'type' => 'boolean', 'value' => true],
                    ['key' => 'custom_domain', 'name' => 'Custom domain', 'type' => 'boolean', 'value' => false],
                    ['key' => 'payment_integration', 'name' => 'Payment integration', 'type' => 'boolean', 'value' => false],
                    ['key' => 'ecommerce', 'name' => 'E-commerce', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'starter',
                'name' => 'Starter',
                'description' => 'For small businesses',
                'price_monthly' => 120,
                'price_annual' => 1200,
                'sort_order' => 1,
                'features' => [
                    ['key' => 'websites', 'name' => '1 website', 'type' => 'boolean', 'value' => true],
                    ['key' => 'custom_domain', 'name' => 'Custom domain (buy or bring your own)', 'type' => 'boolean', 'value' => true],
                    ['key' => 'products', 'name' => 'Up to 20 products (basic e-commerce)', 'type' => 'boolean', 'value' => true],
                    ['key' => 'email_support', 'name' => 'Basic email support', 'type' => 'boolean', 'value' => true],
                    ['key' => 'manual_payments', 'name' => 'Manual/offline payments only', 'type' => 'boolean', 'value' => true],
                    ['key' => 'shared_smtp', 'name' => 'Shared SMTP (200 emails/month)', 'type' => 'boolean', 'value' => true],
                    ['key' => 'full_payment_gateways', 'name' => 'Full payment gateways', 'type' => 'boolean', 'value' => false],
                    ['key' => 'marketing_tools', 'name' => 'Marketing tools', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'business',
                'name' => 'Business',
                'description' => 'For growing businesses',
                'price_monthly' => 350,
                'price_annual' => 3500,
                'is_popular' => true,
                'sort_order' => 2,
                'features' => [
                    ['key' => 'websites', 'name' => '1 website', 'type' => 'boolean', 'value' => true],
                    ['key' => 'free_domain', 'name' => 'Free domain after 3 months prepayment', 'type' => 'boolean', 'value' => true],
                    ['key' => 'unlimited_products', 'name' => 'Unlimited store products', 'type' => 'boolean', 'value' => true],
                    ['key' => 'payment_integrations', 'name' => 'Full payment integrations (MTN, Airtel, Visa)', 'type' => 'boolean', 'value' => true],
                    ['key' => 'marketing_tools', 'name' => 'Marketing tools & email campaigns', 'type' => 'boolean', 'value' => true],
                    ['key' => 'priority_support', 'name' => 'Priority support', 'type' => 'boolean', 'value' => true],
                    ['key' => 'remove_branding', 'name' => 'Remove MyGrowNet branding', 'type' => 'boolean', 'value' => true],
                    ['key' => 'own_smtp', 'name' => 'Connect own SMTP', 'type' => 'boolean', 'value' => true],
                ],
            ],
            [
                'tier_key' => 'agency',
                'name' => 'Agency',
                'description' => 'For agencies & resellers',
                'price_monthly' => 900,
                'price_annual' => 9000,
                'sort_order' => 3,
                'features' => [
                    ['key' => 'multi_sites', 'name' => 'Build/manage 10-20 websites', 'type' => 'boolean', 'value' => true],
                    ['key' => 'white_label', 'name' => 'White-label option (your branding)', 'type' => 'boolean', 'value' => true],
                    ['key' => 'billing_options', 'name' => 'Monthly or annual billing', 'type' => 'boolean', 'value' => true],
                    ['key' => 'payment_choice', 'name' => 'Choose: you pay or client pays directly', 'type' => 'boolean', 'value' => true],
                    ['key' => 'commission', 'name' => 'Commission integrated with MyGrowNet', 'type' => 'boolean', 'value' => true],
                    ['key' => 'sms_discount', 'name' => 'Discounted SMS credits', 'type' => 'boolean', 'value' => true],
                    ['key' => 'business_features', 'name' => 'All Business plan features', 'type' => 'boolean', 'value' => true],
                    ['key' => 'dedicated_onboarding', 'name' => 'Dedicated onboarding', 'type' => 'boolean', 'value' => true],
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
