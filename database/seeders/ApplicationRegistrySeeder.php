<?php

namespace Database\Seeders;

use App\Domain\Core\Models\Application;
use Illuminate\Database\Seeder;

class ApplicationRegistrySeeder extends Seeder
{
    /**
     * Seed or update application metadata for all registered applications.
     * Uses updateOrCreate to handle fresh databases and schema migrations.
     */
    public function run(): void
    {
        $now = now();
        $applications = [
            // Business Management Apps (Organization Context)
            [
                'slug' => 'bms',
                'category' => 'business',
                'access_model' => 'organization_members',
                'context_support' => 'organization',
                'requires_organization_context' => true,
                'lifecycle' => 'active',
                'is_visible' => true,
                'operational_status' => 'online',
                'sort_order' => 1,
            ],
            [
                'slug' => 'stockflow',
                'category' => 'business',
                'access_model' => 'organization_members',
                'context_support' => 'organization',
                'requires_organization_context' => true,
                'lifecycle' => 'active',
                'is_visible' => true,
                'operational_status' => 'online',
                'sort_order' => 2,
            ],
            [
                'slug' => 'grow-finance',
                'category' => 'business',
                'access_model' => 'organization_members',
                'context_support' => 'organization',
                'requires_organization_context' => true,
                'lifecycle' => 'active',
                'is_visible' => true,
                'operational_status' => 'online',
                'sort_order' => 3,
            ],
            [
                'slug' => 'employee-portal',
                'category' => 'business',
                'access_model' => 'organization_members',
                'context_support' => 'organization',
                'requires_organization_context' => true,
                'lifecycle' => 'active',
                'is_visible' => true,
                'operational_status' => 'online',
                'sort_order' => 4,
            ],
            [
                'slug' => 'grow-builder',
                'category' => 'business',
                'access_model' => 'organization_members',
                'context_support' => 'organization',
                'requires_organization_context' => true,
                'lifecycle' => 'active',
                'is_visible' => true,
                'operational_status' => 'online',
                'sort_order' => 5,
            ],
            [
                'slug' => 'bizdocs',
                'category' => 'business',
                'access_model' => 'organization_members',
                'context_support' => 'organization',
                'requires_organization_context' => true,
                'lifecycle' => 'active',
                'is_visible' => true,
                'operational_status' => 'online',
                'sort_order' => 6,
            ],
            [
                'slug' => 'grow-mart',
                'category' => 'business',
                'access_model' => 'organization_members',
                'context_support' => 'organization',
                'requires_organization_context' => true,
                'lifecycle' => 'active',
                'is_visible' => true,
                'operational_status' => 'online',
                'sort_order' => 7,
            ],
            
            // Consumer/Personal Apps (Personal Context)
            [
                'slug' => 'grownet',
                'category' => 'consumer',
                'access_model' => 'customer',
                'context_support' => 'personal',
                'requires_organization_context' => false,
                'lifecycle' => 'active',
                'is_visible' => true,
                'operational_status' => 'online',
                'subscription_required' => false, // Default free for all members
                'sort_order' => 100,
            ],
            [
                'slug' => 'library',
                'category' => 'consumer',
                'access_model' => 'customer',
                'context_support' => 'personal',
                'requires_organization_context' => false,
                'lifecycle' => 'active',
                'is_visible' => true,
                'operational_status' => 'online',
                'subscription_required' => false,
                'sort_order' => 101,
            ],
            [
                'slug' => 'zamstay',
                'category' => 'consumer',
                'access_model' => 'customer',
                'context_support' => 'personal',
                'requires_organization_context' => false,
                'lifecycle' => 'active',
                'is_visible' => true,
                'operational_status' => 'online',
                'subscription_required' => false,
                'sort_order' => 102,
            ],
            [
                'slug' => 'prime-edge',
                'category' => 'consumer',
                'access_model' => 'customer',
                'context_support' => 'personal',
                'requires_organization_context' => false,
                'lifecycle' => 'active',
                'is_visible' => true,
                'operational_status' => 'online',
                'subscription_required' => true, // Premium advisory service
                'sort_order' => 103,
            ],
            
            // Hybrid Apps (Both Contexts)
            [
                'slug' => 'messaging',
                'category' => 'shared',
                'access_model' => 'both',
                'context_support' => 'both',
                'requires_organization_context' => false,
                'lifecycle' => 'active',
                'is_visible' => true,
                'operational_status' => 'online',
                'subscription_required' => false,
                'sort_order' => 200,
            ],
            
            // Legacy/Migrating Apps
            [
                'slug' => 'cms',
                'category' => 'business',
                'access_model' => 'organization_members',
                'context_support' => 'organization',
                'requires_organization_context' => true,
                'lifecycle' => 'legacy', // Being migrated to BMS
                'is_visible' => false,
                'operational_status' => 'maintenance',
                'sort_order' => 999,
            ],
            [
                'slug' => 'bizboost',
                'category' => 'business',
                'access_model' => 'organization_members',
                'context_support' => 'organization',
                'requires_organization_context' => true,
                'lifecycle' => 'legacy', // Being migrated to BMS
                'is_visible' => false,
                'operational_status' => 'maintenance',
                'sort_order' => 998,
            ],
        ];

        $nameMap = [
            'bms' => 'Business Management',
            'stockflow' => 'StockFlow',
            'grow-finance' => 'GrowFinance',
            'employee-portal' => 'Employee Portal',
            'grow-builder' => 'GrowBuilder',
            'bizdocs' => 'BizDocs',
            'grow-mart' => 'GrowMart',
            'grownet' => 'GrowNet',
            'library' => 'Library',
            'zamstay' => 'ZamStay',
            'prime-edge' => 'PrimeEdge Advisory',
            'messaging' => 'Messaging',
            'cms' => 'CMS (Legacy)',
            'bizboost' => 'BizBoost (Legacy)',
        ];

        foreach ($applications as $appData) {
            Application::updateOrCreate(
                ['slug' => $appData['slug']],
                array_merge([
                    'name' => $nameMap[$appData['slug']] ?? ucfirst($appData['slug']),
                    'type' => $appData['category'] ?? 'platform',
                    'is_active' => true,
                    'url' => null,
                    'config' => null,
                ], $appData),
            );
        }

        $this->command->info('✅ Synced ' . count($applications) . ' application records.');
    }
}
