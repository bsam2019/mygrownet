<?php

namespace Database\Seeders;

use App\Domain\Core\Models\Application;
use Illuminate\Database\Seeder;

class ApplicationRegistrySeeder extends Seeder
{
    /**
     * Seed application metadata for all registered applications.
     * Slugs MUST match actual DB records — uses updateOrCreate.
     */
    public function run(): void
    {
        $applications = [

            // === Business (org context) ===
            [
                'slug' => 'bms',
                'name' => 'BMS',
                'type' => 'business',
                'category' => 'business',
                'access_model' => 'organization_members',
                'context_support' => 'organization',
                'requires_organization_context' => true,
                'subscription_required' => false,
                'url' => 'https://bms.mygrownet.com',
                'lifecycle' => 'active',
                'operational_status' => 'online',
                'is_visible' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'stockflow',
                'name' => 'StockFlow',
                'type' => 'business',
                'category' => 'business',
                'access_model' => 'organization_members',
                'context_support' => 'organization',
                'requires_organization_context' => true,
                'subscription_required' => false,
                'url' => 'https://stockflow.mygrownet.com',
                'lifecycle' => 'active',
                'operational_status' => 'online',
                'is_visible' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'growfinance',
                'name' => 'GrowFinance',
                'type' => 'business',
                'category' => 'business',
                'access_model' => 'organization_members',
                'context_support' => 'organization',
                'requires_organization_context' => true,
                'subscription_required' => false,
                'url' => 'https://growfinance.mygrownet.com',
                'lifecycle' => 'active',
                'operational_status' => 'online',
                'is_visible' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'bizdocs',
                'name' => 'BizDocs',
                'type' => 'business',
                'category' => 'business',
                'access_model' => 'organization_members',
                'context_support' => 'organization',
                'requires_organization_context' => true,
                'subscription_required' => false,
                'url' => 'https://bizdocs.mygrownet.com',
                'lifecycle' => 'active',
                'operational_status' => 'online',
                'is_visible' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'growbuilder',
                'name' => 'GrowBuilder',
                'type' => 'business',
                'category' => 'business',
                'access_model' => 'both',
                'context_support' => 'both',
                'requires_organization_context' => false,
                'subscription_required' => false,
                'url' => 'https://growbuilder.mygrownet.com',
                'lifecycle' => 'active',
                'operational_status' => 'online',
                'is_visible' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'growmart',
                'name' => 'GrowMart',
                'type' => 'consumer',
                'category' => 'consumer',
                'access_model' => 'customer',
                'context_support' => 'personal',
                'requires_organization_context' => false,
                'subscription_required' => false,
                'url' => 'https://growmart.mygrownet.com',
                'lifecycle' => 'active',
                'operational_status' => 'online',
                'is_visible' => true,
                'is_active' => true,
            ],

            // === Consumer (personal context, subscription) ===
            [
                'slug' => 'grownet',
                'name' => 'GrowNet',
                'type' => 'consumer',
                'category' => 'consumer',
                'access_model' => 'customer',
                'context_support' => 'personal',
                'requires_organization_context' => false,
                'subscription_required' => false,
                'url' => 'https://grownet.mygrownet.com',
                'lifecycle' => 'active',
                'operational_status' => 'online',
                'is_visible' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'lifeplus',
                'name' => 'LifePlus',
                'type' => 'consumer',
                'category' => 'consumer',
                'access_model' => 'customer',
                'context_support' => 'personal',
                'requires_organization_context' => false,
                'subscription_required' => true,
                'url' => 'https://lifeplus.mygrownet.com',
                'lifecycle' => 'active',
                'operational_status' => 'online',
                'is_visible' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'zamstay',
                'name' => 'ZamStay',
                'type' => 'consumer',
                'category' => 'consumer',
                'access_model' => 'customer',
                'context_support' => 'personal',
                'requires_organization_context' => false,
                'subscription_required' => false,
                'url' => 'https://zamstay.mygrownet.com',
                'lifecycle' => 'active',
                'operational_status' => 'online',
                'is_visible' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'primeedge',
                'name' => 'PrimeEdge Advisory',
                'type' => 'consumer',
                'category' => 'consumer',
                'access_model' => 'customer',
                'context_support' => 'personal',
                'requires_organization_context' => false,
                'subscription_required' => false,
                'url' => 'https://primeedge.mygrownet.com',
                'lifecycle' => 'active',
                'operational_status' => 'online',
                'is_visible' => true,
                'is_active' => true,
            ],

            // === Shared (both contexts) ===
            [
                'slug' => 'growstorage',
                'name' => 'GrowStorage',
                'type' => 'shared',
                'category' => 'shared',
                'access_model' => 'both',
                'context_support' => 'both',
                'requires_organization_context' => false,
                'subscription_required' => true,
                'url' => 'https://growstorage.mygrownet.com',
                'lifecycle' => 'active',
                'operational_status' => 'online',
                'is_visible' => true,
                'is_active' => true,
            ],

            // === Legacy ===
            [
                'slug' => 'bizboost',
                'name' => 'BizBoost',
                'type' => 'business',
                'category' => 'business',
                'access_model' => 'organization_members',
                'context_support' => 'organization',
                'requires_organization_context' => true,
                'subscription_required' => false,
                'url' => 'https://bizboost.mygrownet.com',
                'lifecycle' => 'legacy',
                'operational_status' => 'maintenance',
                'is_visible' => false,
                'is_active' => true,
            ],
            [
                'slug' => 'quick-invoice',
                'name' => 'Quick Invoice',
                'type' => 'business',
                'category' => 'business',
                'access_model' => 'both',
                'context_support' => 'both',
                'requires_organization_context' => false,
                'subscription_required' => false,
                'url' => '/quick-invoice',
                'lifecycle' => 'active',
                'operational_status' => 'online',
                'is_visible' => true,
                'is_active' => true,
            ],
        ];

        foreach ($applications as $appData) {
            Application::updateOrCreate(
                ['slug' => $appData['slug']],
                $appData,
            );
        }

        $this->command->info('Synced ' . count($applications) . ' application records with metadata.');
    }
}
