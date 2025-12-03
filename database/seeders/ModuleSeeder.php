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
            // MLM Dashboard - Blue
            [
                'id' => 'mlm-dashboard',
                'name' => 'MLM Dashboard',
                'slug' => 'mlm-dashboard',
                'category' => 'core',
                'description' => 'Your network marketing dashboard with earnings and team overview',
                'icon' => '👤',
                'color' => '#3B82F6', // Blue
                'thumbnail' => null,
                'account_types' => json_encode(['member']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/dashboard',
                    'standalone' => null,
                ]),
                'pwa_config' => json_encode(['enabled' => false]),
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
                    'integrated' => '/growbiz',
                    'standalone' => '/growbiz',
                    'setup' => '/growbiz/setup',
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
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Messaging - Purple
            [
                'id' => 'messaging',
                'name' => 'Messaging',
                'slug' => 'messaging',
                'category' => 'core',
                'description' => 'Connect with your team and network members',
                'icon' => '💬',
                'color' => '#8B5CF6', // Purple
                'thumbnail' => null,
                'account_types' => json_encode(['member', 'business']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/messages',
                    'standalone' => null,
                ]),
                'pwa_config' => json_encode(['enabled' => false]),
                'features' => json_encode(['notifications' => true]),
                'subscription_tiers' => null,
                'requires_subscription' => false,
                'version' => '1.0.0',
                'status' => 'active',
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
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Profile & Settings - Gray
            [
                'id' => 'settings',
                'name' => 'Profile & Settings',
                'slug' => 'settings',
                'category' => 'core',
                'description' => 'Manage your profile and account settings',
                'icon' => '⚙️',
                'color' => '#6B7280', // Gray
                'thumbnail' => null,
                'account_types' => json_encode(['member', 'business']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/settings/profile',
                    'standalone' => null,
                ]),
                'pwa_config' => json_encode(['enabled' => false]),
                'features' => json_encode([]),
                'subscription_tiers' => null,
                'requires_subscription' => false,
                'version' => '1.0.0',
                'status' => 'active',
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
                'status' => 'active',
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
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Inventory Management - Teal
            [
                'id' => 'inventory',
                'name' => 'Inventory Management',
                'slug' => 'inventory',
                'category' => 'sme',
                'description' => 'Track stock levels, orders, and suppliers for your business',
                'icon' => '📦',
                'color' => '#14B8A6', // Teal
                'thumbnail' => null,
                'account_types' => json_encode(['business']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/modules/inventory',
                    'standalone' => '/apps/inventory',
                ]),
                'pwa_config' => json_encode(['enabled' => true, 'installable' => true, 'offline_capable' => true]),
                'features' => json_encode(['offline' => true, 'dataSync' => true, 'multiUser' => true, 'barcode' => true]),
                'subscription_tiers' => json_encode([
                    'starter' => ['name' => 'Starter', 'price' => 75, 'billing_cycle' => 'monthly', 'user_limit' => 2, 'products' => 100],
                    'business' => ['name' => 'Business', 'price' => 150, 'billing_cycle' => 'monthly', 'user_limit' => 5, 'products' => 1000],
                    'enterprise' => ['name' => 'Enterprise', 'price' => 300, 'billing_cycle' => 'monthly', 'user_limit' => 'unlimited', 'products' => 'unlimited'],
                ]),
                'requires_subscription' => true,
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
                'status' => 'active',
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
                'status' => 'active',
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
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Points & Rewards - Amber
            [
                'id' => 'points-rewards',
                'name' => 'Points & Rewards',
                'slug' => 'points-rewards',
                'category' => 'core',
                'description' => 'Track your loyalty points and redeem rewards',
                'icon' => '⭐',
                'color' => '#F59E0B', // Amber
                'thumbnail' => null,
                'account_types' => json_encode(['member', 'business']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/points',
                    'standalone' => null,
                ]),
                'pwa_config' => json_encode(['enabled' => false]),
                'features' => json_encode(['notifications' => true]),
                'subscription_tiers' => null,
                'requires_subscription' => false,
                'version' => '1.0.0',
                'status' => 'active',
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
                    'integrated' => '/growfinance',
                    'standalone' => '/growfinance',
                    'setup' => '/growfinance/setup',
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
        ];

        DB::table('modules')->insert($modules);
        
        $this->command->info('Seeded ' . count($modules) . ' modules successfully!');
    }
}
