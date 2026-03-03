<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinancialModulesSeeder extends Seeder
{
    /**
     * Seed the financial modules table with actual business unit modules.
     * 
     * IMPORTANT: Only modules that are independent business units with their own
     * revenue and expense streams should be included here. Features like wallet,
     * commissions, LGR, etc. are NOT modules - they are platform features.
     */
    public function run(): void
    {
        // Clear existing modules
        DB::table('financial_modules')->truncate();

        // Seed actual business unit modules (from ModuleSeeder)
        $modules = [
            // Core Business Units
            [
                'code' => 'grownet',
                'name' => 'GrowNet',
                'description' => 'MLM/Network Marketing - Community network with referral rewards',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'growbiz',
                'name' => 'GrowBiz',
                'description' => 'Task & Staff Management + HR - Task management, team collaboration, and HR for SMEs',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'growbuilder',
                'name' => 'GrowBuilder',
                'description' => 'Website Builder - Build professional websites with AI-powered content',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'growfinance',
                'name' => 'GrowFinance',
                'description' => 'Financial Management + Accounting - Track income, expenses, invoices, and reports',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'bizboost',
                'name' => 'BizBoost',
                'description' => 'Marketing & Growth + CRM - Marketing assistant with AI content, social media, and customer management',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'growmarket',
                'name' => 'GrowMarket',
                'description' => 'Marketplace + E-Commerce - Buy and sell products, online store management',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'learning-hub',
                'name' => 'Learning Hub',
                'description' => 'Online Learning - Courses, tutorials, and educational content',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'lifeplus',
                'name' => 'Life+',
                'description' => 'Daily Life Companion - Tasks, expenses, habits, and community',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 80,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'cms',
                'name' => 'CMS',
                'description' => 'Company Management System - Complete business management for SMEs',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 85,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // SME Business Units
            [
                'code' => 'inventory',
                'name' => 'Inventory Management',
                'description' => 'Stock Management - Track stock levels, orders, and suppliers',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 90,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'pos',
                'name' => 'Point of Sale',
                'description' => 'POS System - Simple POS for retail sales with reporting',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Enterprise & Specialized
            [
                'code' => 'venture-builder',
                'name' => 'Venture Builder',
                'description' => 'Investment Platform - Co-invest in vetted business projects',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 150,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ubumi',
                'name' => 'Ubumi',
                'description' => 'Family Platform - Family lineage and health check-in platform',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 160,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'growbackup',
                'name' => 'GrowBackup',
                'description' => 'Cloud Storage - Secure cloud storage and backup service',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 170,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Platform Operations (not a revenue module)
            [
                'code' => 'platform',
                'name' => 'Platform Operations',
                'description' => 'Core platform infrastructure and operational expenses',
                'is_revenue_module' => false,
                'is_active' => true,
                'display_order' => 200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('financial_modules')->insert($modules);

        $revenueModules = collect($modules)->where('is_revenue_module', true);
        
        $this->command->info('✅ Financial modules seeded successfully!');
        $this->command->info('📊 Seeded ' . count($modules) . ' modules (' . $revenueModules->count() . ' revenue-generating)');
        $this->command->newLine();
        $this->command->info('Business Unit Modules:');
        foreach ($revenueModules as $module) {
            $this->command->info("  • {$module['name']} ({$module['code']})");
        }
        $this->command->newLine();
        $this->command->warn('Note: Features like Wallet, Commissions, LGR, Loans, etc. are NOT modules.');
        $this->command->warn('They are platform features and should not be tracked as separate business units.');
    }
}

