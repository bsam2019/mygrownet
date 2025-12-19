<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks for truncate
        Schema::disableForeignKeyConstraints();
        
        // Clear existing modules
        DB::table('modules')->truncate();
        
        Schema::enableForeignKeyConstraints();

        $modules = [
            // GrowNet - Community & Referral Network - Blue
            [
                'id' => 'grownet',
                'name' => 'GrowNet',
                'slug' => 'grownet',
                'category' => 'core',
                'description' => 'Community network with referral rewards and team growth tracking',
                'icon' => '🌐',
                'color' => '#3B82F6', // Blue
                'thumbnail' => null,
                'account_types' => json_encode(['member']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/grownet',
                    'standalone' => '/grownet',
                ]),
                'pwa_config' => json_encode(['enabled' => true, 'installable' => true]),
                'features' => json_encode(['offline' => false, 'notifications' => true]),
                'subscription_tiers' => null,
                'requires_subscription' => false,
                'version' => '1.0.0',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // GrowBiz - Task & Staff Management - Emerald
            [
                'id' => 'growbiz',
                'name' => 'GrowBiz',
                'slug' => 'growbiz',
                'category' => 'sme',
                'description' => 'Task management and team collaboration for SMEs',
                'icon' => '📋',
                'color' => '#10B981', // Emerald
                'thumbnail' => null,
                'account_types' => json_encode(['business', 'member', 'client']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/growbiz/dashboard',
                    'standalone' => '/growbiz/dashboard',
                    'setup' => '/growbiz/setup',
                    'welcome' => '/growbiz',  // Public landing page (no auth required)
                ]),
                'pwa_config' => json_encode(['enabled' => true, 'installable' => true]),
                'features' => json_encode(['offline' => true, 'notifications' => true, 'requires_setup' => true]),
                'subscription_tiers' => json_encode([
                    'free' => ['name' => 'Free', 'price' => 0, 'billing_cycle' => 'monthly', 'employees' => 3, 'tasks' => 50],
                    'basic' => ['name' => 'Basic', 'price' => 50, 'billing_cycle' => 'monthly', 'employees' => 10, 'tasks' => 'unlimited'],
                    'pro' => ['name' => 'Pro', 'price' => 100, 'billing_cycle' => 'monthly', 'employees' => 'unlimited', 'tasks' => 'unlimited'],
                ]),
                'requires_subscription' => false,
                'version' => '1.0.0',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Smart Accounting - Green
            [
                'id' => 'smart-accounting',
                'name' => 'Smart Accounting',
                'slug' => 'smart-accounting',
                'category' => 'sme',
                'description' => 'Complete accounting solution for your business',
                'icon' => '📊',
                'color' => '#10B981', // Green
                'thumbnail' => null,
                'account_types' => json_encode(['business']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/modules/accounting',
                    'standalone' => '/apps/accounting',
                ]),
                'pwa_config' => json_encode(['enabled' => true, 'installable' => true, 'offline_capable' => true]),
                'features' => json_encode(['offline' => true, 'dataSync' => true, 'multiUser' => true]),
                'subscription_tiers' => json_encode([
                    'basic' => ['name' => 'Basic', 'price' => 100, 'billing_cycle' => 'monthly', 'user_limit' => 3],
                    'pro' => ['name' => 'Professional', 'price' => 200, 'billing_cycle' => 'monthly', 'user_limit' => 10],
                ]),
                'requires_subscription' => true,
                'version' => '1.0.0',
                'status' => 'coming_soon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
           
            // Marketplace - Orange
            [
                'id' => 'marketplace',
                'name' => 'Marketplace',
                'slug' => 'marketplace',
                'category' => 'core',
                'description' => 'Buy and sell products within the network',
                'icon' => '🛒',
                'color' => '#F59E0B', // Orange/Amber
                'thumbnail' => null,
                'account_types' => json_encode(['member', 'business']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/shop',
                    'standalone' => null,
                ]),
                'pwa_config' => json_encode(['enabled' => false]),
                'features' => json_encode(['notifications' => true]),
                'subscription_tiers' => null,
                'requires_subscription' => false,
                'version' => '1.0.0',
                'status' => 'coming_soon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // MyGrow Save - Digital Wallet - Emerald
            [
                'id' => 'mygrow-save',
                'name' => 'MyGrow Save',
                'slug' => 'mygrow-save',
                'category' => 'personal',
                'description' => 'Digital wallet for savings, transactions, and financial goals',
                'icon' => '💰',
                'color' => '#059669', // Emerald
                'thumbnail' => null,
                'account_types' => json_encode(['member', 'business']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/wallet',
                    'standalone' => '/apps/wallet',
                ]),
                'pwa_config' => json_encode(['enabled' => true, 'installable' => true, 'offline_capable' => true]),
                'features' => json_encode(['offline' => true, 'dataSync' => true, 'notifications' => true]),
                'subscription_tiers' => json_encode([
                    'free' => ['name' => 'Free', 'price' => 0, 'billing_cycle' => 'monthly', 'features' => ['basic_wallet' => true, 'transactions' => 10]],
                    'premium' => ['name' => 'Premium', 'price' => 25, 'billing_cycle' => 'monthly', 'features' => ['unlimited_transactions' => true, 'savings_goals' => true, 'analytics' => true]],
                ]),
                'requires_subscription' => false,
                'version' => '1.0.0',
                'status' => 'coming_soon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Wedding Planner - Pink
            [
                'id' => 'wedding-planner',
                'name' => 'Wedding Planner',
                'slug' => 'wedding-planner',
                'category' => 'personal',
                'description' => 'Plan your perfect wedding with budgets, vendors, and checklists',
                'icon' => '💒',
                'color' => '#EC4899', // Pink
                'thumbnail' => null,
                'account_types' => json_encode(['member']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/modules/wedding',
                    'standalone' => '/apps/wedding',
                ]),
                'pwa_config' => json_encode(['enabled' => true, 'installable' => true, 'offline_capable' => true]),
                'features' => json_encode(['offline' => true, 'dataSync' => true, 'notifications' => true]),
                'subscription_tiers' => json_encode([
                    'basic' => ['name' => 'Basic', 'price' => 75, 'billing_cycle' => 'monthly', 'features' => ['checklist' => true, 'budget' => true, 'vendors' => 10]],
                    'premium' => ['name' => 'Premium', 'price' => 150, 'billing_cycle' => 'monthly', 'features' => ['checklist' => true, 'budget' => true, 'vendors' => 'unlimited', 'guest_management' => true, 'seating_chart' => true]],
                ]),
                'requires_subscription' => true,
                'version' => '1.0.0',
                'status' => 'coming_soon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Inventory Management - Teal (Standalone + Integratable)
            [
                'id' => 'inventory',
                'name' => 'Inventory Management',
                'slug' => 'inventory',
                'category' => 'sme',
                'description' => 'Track stock levels, orders, and suppliers. Can be used standalone or integrated into GrowBiz, BizBoost, or E-Commerce.',
                'icon' => '📦',
                'color' => '#14B8A6', // Teal
                'thumbnail' => null,
                'account_types' => json_encode(['business', 'member']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/inventory',
                    'standalone' => '/inventory',
                    'setup' => '/inventory/setup',
                ]),
                'pwa_config' => json_encode(['enabled' => true, 'installable' => true, 'offline_capable' => true]),
                'features' => json_encode([
                    'offline' => true, 
                    'dataSync' => true, 
                    'multiUser' => true, 
                    'barcode' => true,
                    'integratable' => true, // Can be embedded in other modules
                    'integrates_with' => ['growbiz', 'bizboost', 'ecommerce', 'pos'],
                ]),
                'subscription_tiers' => json_encode([
                    'free' => ['name' => 'Free', 'price' => 0, 'billing_cycle' => 'monthly', 'products' => 50],
                    'starter' => ['name' => 'Starter', 'price' => 75, 'billing_cycle' => 'monthly', 'user_limit' => 2, 'products' => 500],
                    'business' => ['name' => 'Business', 'price' => 150, 'billing_cycle' => 'monthly', 'user_limit' => 5, 'products' => 2000],
                    'enterprise' => ['name' => 'Enterprise', 'price' => 300, 'billing_cycle' => 'monthly', 'user_limit' => 'unlimited', 'products' => 'unlimited'],
                ]),
                'requires_subscription' => false,
                'version' => '1.0.0',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Point of Sale (POS) - Purple (Standalone + Integratable)
            [
                'id' => 'pos',
                'name' => 'Point of Sale',
                'slug' => 'pos',
                'category' => 'sme',
                'description' => 'Simple POS for retail sales with shift management and reporting. Can be used standalone or integrated into GrowBiz, BizBoost, or E-Commerce.',
                'icon' => '🛒',
                'color' => '#8B5CF6', // Purple
                'thumbnail' => null,
                'account_types' => json_encode(['business', 'member']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/pos',
                    'standalone' => '/pos',
                    'setup' => '/pos/setup',
                    'terminal' => '/pos/terminal',
                ]),
                'pwa_config' => json_encode(['enabled' => true, 'installable' => true, 'offline_capable' => true]),
                'features' => json_encode([
                    'offline' => true, 
                    'dataSync' => true, 
                    'notifications' => true,
                    'integratable' => true, // Can be embedded in other modules
                    'integrates_with' => ['growbiz', 'bizboost', 'ecommerce'],
                    'requires_inventory' => false, // Can work without inventory
                    'optional_inventory' => true, // But integrates with inventory if available
                ]),
                'subscription_tiers' => json_encode([
                    'free' => ['name' => 'Free', 'price' => 0, 'billing_cycle' => 'monthly', 'sales_per_month' => 100],
                    'starter' => ['name' => 'Starter', 'price' => 49, 'billing_cycle' => 'monthly', 'sales_per_month' => 1000],
                    'business' => ['name' => 'Business', 'price' => 99, 'billing_cycle' => 'monthly', 'sales_per_month' => 5000],
                    'unlimited' => ['name' => 'Unlimited', 'price' => 199, 'billing_cycle' => 'monthly', 'sales_per_month' => 'unlimited'],
                ]),
                'requires_subscription' => false,
                'version' => '1.0.0',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // CRM - Indigo
            [
                'id' => 'crm',
                'name' => 'Customer CRM',
                'slug' => 'crm',
                'category' => 'sme',
                'description' => 'Manage customer relationships, leads, and sales pipeline',
                'icon' => '🤝',
                'color' => '#6366F1', // Indigo
                'thumbnail' => null,
                'account_types' => json_encode(['business']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/modules/crm',
                    'standalone' => '/apps/crm',
                ]),
                'pwa_config' => json_encode(['enabled' => true, 'installable' => true]),
                'features' => json_encode(['offline' => false, 'dataSync' => true, 'multiUser' => true, 'notifications' => true]),
                'subscription_tiers' => json_encode([
                    'starter' => ['name' => 'Starter', 'price' => 100, 'billing_cycle' => 'monthly', 'user_limit' => 3, 'contacts' => 500],
                    'growth' => ['name' => 'Growth', 'price' => 200, 'billing_cycle' => 'monthly', 'user_limit' => 10, 'contacts' => 5000],
                    'scale' => ['name' => 'Scale', 'price' => 400, 'billing_cycle' => 'monthly', 'user_limit' => 'unlimited', 'contacts' => 'unlimited'],
                ]),
                'requires_subscription' => true,
                'version' => '1.0.0',
                'status' => 'coming_soon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Learning Hub - Cyan
            [
                'id' => 'learning-hub',
                'name' => 'Learning Hub',
                'slug' => 'learning-hub',
                'category' => 'core',
                'description' => 'Access courses, tutorials, and educational content',
                'icon' => '📚',
                'color' => '#06B6D4', // Cyan
                'thumbnail' => null,
                'account_types' => json_encode(['member', 'business']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/learning',
                    'standalone' => null,
                ]),
                'pwa_config' => json_encode(['enabled' => false]),
                'features' => json_encode(['offline' => false, 'notifications' => true]),
                'subscription_tiers' => null,
                'requires_subscription' => false,
                'version' => '1.0.0',
                'status' => 'coming_soon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Venture Builder - Violet
            [
                'id' => 'venture-builder',
                'name' => 'Venture Builder',
                'slug' => 'venture-builder',
                'category' => 'enterprise',
                'description' => 'Co-invest in vetted business projects and track your portfolio',
                'icon' => '🚀',
                'color' => '#7C3AED', // Violet
                'thumbnail' => null,
                'account_types' => json_encode(['member', 'business']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/ventures',
                    'standalone' => null,
                ]),
                'pwa_config' => json_encode(['enabled' => false]),
                'features' => json_encode(['notifications' => true]),
                'subscription_tiers' => null,
                'requires_subscription' => false,
                'version' => '1.0.0',
                'status' => 'coming_soon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // HR Management - Rose
            [
                'id' => 'hr-management',
                'name' => 'HR Management',
                'slug' => 'hr-management',
                'category' => 'sme',
                'description' => 'Manage employees, payroll, leave, and HR processes',
                'icon' => '👥',
                'color' => '#F43F5E', // Rose
                'thumbnail' => null,
                'account_types' => json_encode(['business']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/modules/hr',
                    'standalone' => '/apps/hr',
                ]),
                'pwa_config' => json_encode(['enabled' => true, 'installable' => true]),
                'features' => json_encode(['multiUser' => true, 'notifications' => true]),
                'subscription_tiers' => json_encode([
                    'basic' => ['name' => 'Basic', 'price' => 150, 'billing_cycle' => 'monthly', 'employees' => 25],
                    'professional' => ['name' => 'Professional', 'price' => 300, 'billing_cycle' => 'monthly', 'employees' => 100],
                    'enterprise' => ['name' => 'Enterprise', 'price' => 500, 'billing_cycle' => 'monthly', 'employees' => 'unlimited'],
                ]),
                'requires_subscription' => true,
                'version' => '1.0.0',
                'status' => 'beta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // E-Commerce - Sky
            [
                'id' => 'ecommerce',
                'name' => 'E-Commerce Store',
                'slug' => 'ecommerce',
                'category' => 'sme',
                'description' => 'Create and manage your online store with ease',
                'icon' => '🏪',
                'color' => '#0EA5E9', // Sky
                'thumbnail' => null,
                'account_types' => json_encode(['business']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/modules/store',
                    'standalone' => '/apps/store',
                ]),
                'pwa_config' => json_encode(['enabled' => true, 'installable' => true]),
                'features' => json_encode(['multiUser' => true, 'notifications' => true, 'payments' => true]),
                'subscription_tiers' => json_encode([
                    'starter' => ['name' => 'Starter', 'price' => 100, 'billing_cycle' => 'monthly', 'products' => 50, 'transaction_fee' => '2%'],
                    'business' => ['name' => 'Business', 'price' => 250, 'billing_cycle' => 'monthly', 'products' => 500, 'transaction_fee' => '1%'],
                    'unlimited' => ['name' => 'Unlimited', 'price' => 500, 'billing_cycle' => 'monthly', 'products' => 'unlimited', 'transaction_fee' => '0.5%'],
                ]),
                'requires_subscription' => true,
                'version' => '1.0.0',
                'status' => 'coming_soon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // GrowFinance - Accounting & Financial Management - Emerald
            [
                'id' => 'growfinance',
                'name' => 'GrowFinance',
                'slug' => 'growfinance',
                'category' => 'sme',
                'description' => 'Track income, expenses, invoices, and generate financial reports',
                'icon' => '💵',
                'color' => '#059669', // Emerald
                'thumbnail' => null,
                'account_types' => json_encode(['business', 'member', 'client']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/growfinance/dashboard',
                    'standalone' => '/growfinance/dashboard',
                    'setup' => '/growfinance/setup',
                    'welcome' => '/growfinance',  // Public landing page (no auth required)
                ]),
                'pwa_config' => json_encode(['enabled' => true, 'installable' => true, 'offline_capable' => true]),
                'features' => json_encode(['offline' => true, 'dataSync' => true, 'notifications' => true, 'requires_setup' => true]),
                'subscription_tiers' => json_encode([
                    'free' => ['name' => 'Free', 'price' => 0, 'billing_cycle' => 'monthly', 'transactions' => 100, 'reports' => 'basic'],
                    'basic' => ['name' => 'Basic', 'price' => 99, 'billing_cycle' => 'monthly', 'transactions' => 'unlimited', 'reports' => 'advanced', 'receipt_storage' => '100MB'],
                    'professional' => ['name' => 'Professional', 'price' => 299, 'billing_cycle' => 'monthly', 'transactions' => 'unlimited', 'reports' => 'advanced', 'receipt_storage' => '1GB', 'export' => true],
                    'business' => ['name' => 'Business', 'price' => 599, 'billing_cycle' => 'monthly', 'transactions' => 'unlimited', 'reports' => 'advanced', 'receipt_storage' => '5GB', 'export' => true, 'multi_user' => true],
                ]),
                'requires_subscription' => false,
                'version' => '1.0.0',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // BizBoost - Marketing & Growth Assistant - Orange
            [
                'id' => 'bizboost',
                'name' => 'BizBoost',
                'slug' => 'bizboost',
                'category' => 'sme',
                'description' => 'Marketing & growth assistant for SMEs with AI content, social media, and customer management',
                'icon' => '🚀',
                'color' => '#F97316', // Orange
                'thumbnail' => null,
                'account_types' => json_encode(['business', 'member', 'client']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/bizboost',
                    'standalone' => '/bizboost',
                    'setup' => '/bizboost/setup',
                    'welcome' => '/bizboost/welcome',  // Public landing page (no auth required)
                ]),
                'pwa_config' => json_encode(['enabled' => true, 'installable' => true, 'offline_capable' => true]),
                'features' => json_encode(['offline' => true, 'dataSync' => true, 'notifications' => true, 'requires_setup' => true]),
                'subscription_tiers' => json_encode([
                    'free' => ['name' => 'Free', 'price' => 0, 'billing_cycle' => 'monthly', 'posts' => 10, 'ai_credits' => 5, 'customers' => 50],
                    'starter' => ['name' => 'Starter', 'price' => 49, 'billing_cycle' => 'monthly', 'posts' => 50, 'ai_credits' => 50, 'customers' => 500],
                    'growth' => ['name' => 'Growth', 'price' => 99, 'billing_cycle' => 'monthly', 'posts' => 'unlimited', 'ai_credits' => 200, 'customers' => 'unlimited'],
                    'pro' => ['name' => 'Pro', 'price' => 199, 'billing_cycle' => 'monthly', 'posts' => 'unlimited', 'ai_credits' => 'unlimited', 'customers' => 'unlimited', 'team_members' => 5],
                ]),
                'requires_subscription' => false,
                'version' => '1.0.0',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // LifePlus - Daily Life Companion App - Emerald/Teal
            // FREE for members with active subscriptions, PAID for clients/business users
            // Core starter kit product that justifies MLM membership
            [
                'id' => 'lifeplus',
                'name' => 'Life+',
                'slug' => 'lifeplus',
                'category' => 'personal',
                'description' => 'Your daily life companion - manage tasks, track expenses, build habits, connect with community, and organize Chilimba groups. Essential for personal productivity and financial wellness.',
                'icon' => '✨',
                'color' => '#10B981', // Emerald
                'thumbnail' => null,
                'account_types' => json_encode(['member', 'client', 'business', 'guest']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/lifeplus',
                    'standalone' => '/lifeplus',
                    'welcome' => '/lifeplus/onboarding',
                ]),
                'pwa_config' => json_encode(['enabled' => true, 'installable' => true, 'offline_capable' => true]),
                'features' => json_encode([
                    'offline' => true, 
                    'dataSync' => true, 
                    'notifications' => true,
                    'budget_with_income' => true,
                    'free_for_members' => true,
                    'chilimba_tracker' => true,
                    'habit_tracker' => true,
                    'task_manager' => true,
                    'community' => true,
                    'gig_finder' => true,
                ]),
                'subscription_tiers' => json_encode([
                    // Free tier for anyone - limited features
                    'free' => [
                        'name' => 'Free',
                        'price' => 0,
                        'billing_cycle' => 'monthly',
                        'eligible_account_types' => ['guest', 'client', 'business'],
                        'features' => [
                            'tasks' => true,
                            'tasks_limit' => 10,
                            'expenses' => true,
                            'expenses_history_months' => 1,
                            'habits' => true,
                            'habits_limit' => 1,
                            'community_view' => true,
                            'community_post' => false,
                            'gigs_view' => true,
                            'gigs_post' => false,
                            'chilimba' => false,
                            'budget_planning' => false,
                            'analytics' => false,
                            'data_export' => false,
                            'devices_limit' => 1,
                        ],
                        'limits' => [
                            'tasks' => 10,
                            'habits' => 1,
                            'expense_history_months' => 1,
                            'devices' => 1,
                        ]
                    ],
                    // Premium tier for non-MLM users - full features
                    'premium' => [
                        'name' => 'Premium',
                        'price' => 25,
                        'billing_cycle' => 'monthly',
                        'annual_price' => 250,
                        'eligible_account_types' => ['guest', 'client', 'business'],
                        'features' => [
                            'tasks' => true,
                            'tasks_limit' => -1, // unlimited
                            'expenses' => true,
                            'expenses_history_months' => -1, // unlimited
                            'habits' => true,
                            'habits_limit' => -1, // unlimited
                            'community_view' => true,
                            'community_post' => true,
                            'gigs_view' => true,
                            'gigs_post' => true,
                            'chilimba' => true,
                            'chilimba_groups_limit' => 2,
                            'budget_planning' => true,
                            'analytics' => true,
                            'data_export' => true,
                            'devices_limit' => 3,
                            'offline_mode' => true,
                            'priority_support' => true,
                        ],
                        'limits' => [
                            'tasks' => -1,
                            'habits' => -1,
                            'chilimba_groups' => 2,
                            'expense_history_months' => -1,
                            'devices' => 3,
                        ]
                    ],
                    // Member tier - FREE for MLM members with active subscription
                    'member_free' => [
                        'name' => 'Member',
                        'price' => 0,
                        'billing_cycle' => 'monthly',
                        'eligible_account_types' => ['member'],
                        'requires_active_subscription' => true,
                        'features' => [
                            'tasks' => true,
                            'tasks_limit' => -1,
                            'expenses' => true,
                            'expenses_history_months' => -1,
                            'habits' => true,
                            'habits_limit' => -1,
                            'community_view' => true,
                            'community_post' => true,
                            'gigs_view' => true,
                            'gigs_post' => true,
                            'chilimba' => true,
                            'chilimba_groups_limit' => -1, // unlimited for members
                            'budget_planning' => true,
                            'analytics' => true,
                            'data_export' => true,
                            'devices_limit' => -1, // unlimited
                            'offline_mode' => true,
                            'priority_support' => true,
                            'mygrownet_integration' => true,
                            'earnings_tracking' => true,
                            'referral_tracking' => true,
                        ],
                        'limits' => [
                            'tasks' => -1,
                            'habits' => -1,
                            'chilimba_groups' => -1,
                            'expense_history_months' => -1,
                            'devices' => -1,
                        ]
                    ],
                    // Elite tier - for Professional+ MLM members
                    'elite' => [
                        'name' => 'Elite',
                        'price' => 0,
                        'billing_cycle' => 'monthly',
                        'eligible_account_types' => ['member'],
                        'requires_professional_level' => true,
                        'features' => [
                            'tasks' => true,
                            'tasks_limit' => -1,
                            'expenses' => true,
                            'expenses_history_months' => -1,
                            'habits' => true,
                            'habits_limit' => -1,
                            'community_view' => true,
                            'community_post' => true,
                            'gigs_view' => true,
                            'gigs_post' => true,
                            'chilimba' => true,
                            'chilimba_groups_limit' => -1,
                            'budget_planning' => true,
                            'analytics' => true,
                            'analytics_ai' => true, // AI-powered insights
                            'data_export' => true,
                            'devices_limit' => -1,
                            'offline_mode' => true,
                            'priority_support' => true,
                            'mygrownet_integration' => true,
                            'earnings_tracking' => true,
                            'referral_tracking' => true,
                            'team_collaboration' => true,
                            'api_access' => true,
                            'custom_categories' => true,
                        ],
                        'limits' => [
                            'tasks' => -1,
                            'habits' => -1,
                            'chilimba_groups' => -1,
                            'expense_history_months' => -1,
                            'devices' => -1,
                        ]
                    ],
                ]),
                'requires_subscription' => false, // Has free tier, so doesn't require subscription
                'version' => '2.0.0',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('modules')->insert($modules);
        
        $this->command->info('Seeded ' . count($modules) . ' modules successfully!');
    }
}
