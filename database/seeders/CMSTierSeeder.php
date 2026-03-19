<?php

namespace Database\Seeders;

use App\Models\ModuleTier;
use App\Models\ModuleTierFeature;
use Illuminate\Database\Seeder;

/**
 * Seeds CMS (Company Management System) subscription tiers
 */
class CMSTierSeeder extends Seeder
{
    public function run(): void
    {
        $moduleId = 'cms';

        $tiers = [
            [
                'tier_key' => 'free',
                'name' => 'Free',
                'description' => 'Basic invoicing for small businesses',
                'price_monthly' => 0,
                'price_annual' => 0,
                'is_default' => true,
                'sort_order' => 0,
                'features' => [
                    ['key' => 'invoices', 'name' => '10 invoices per month', 'type' => 'limit', 'limit' => 10],
                    ['key' => 'clients', 'name' => '5 clients', 'type' => 'limit', 'limit' => 5],
                    ['key' => 'products', 'name' => '20 products', 'type' => 'limit', 'limit' => 20],
                    ['key' => 'basic_reports', 'name' => 'Basic reports', 'type' => 'boolean', 'value' => true],
                    ['key' => 'email_support', 'name' => 'Email support', 'type' => 'boolean', 'value' => true],
                    ['key' => 'advanced_reports', 'name' => 'Advanced reports', 'type' => 'boolean', 'value' => false],
                    ['key' => 'inventory_management', 'name' => 'Inventory management', 'type' => 'boolean', 'value' => false],
                    ['key' => 'multi_currency', 'name' => 'Multi-currency support', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'starter',
                'name' => 'Starter',
                'description' => 'For growing small businesses',
                'price_monthly' => 99,
                'price_annual' => 990,
                'sort_order' => 1,
                'features' => [
                    ['key' => 'invoices', 'name' => '100 invoices per month', 'type' => 'limit', 'limit' => 100],
                    ['key' => 'clients', 'name' => '50 clients', 'type' => 'limit', 'limit' => 50],
                    ['key' => 'products', 'name' => '200 products', 'type' => 'limit', 'limit' => 200],
                    ['key' => 'basic_reports', 'name' => 'Basic reports', 'type' => 'boolean', 'value' => true],
                    ['key' => 'advanced_reports', 'name' => 'Advanced reports', 'type' => 'boolean', 'value' => true],
                    ['key' => 'email_support', 'name' => 'Priority email support', 'type' => 'boolean', 'value' => true],
                    ['key' => 'inventory_management', 'name' => 'Basic inventory management', 'type' => 'boolean', 'value' => true],
                    ['key' => 'multi_currency', 'name' => 'Multi-currency support', 'type' => 'boolean', 'value' => false],
                ],
            ],
            [
                'tier_key' => 'business',
                'name' => 'Business',
                'description' => 'For established businesses',
                'price_monthly' => 199,
                'price_annual' => 1990,
                'is_popular' => true,
                'sort_order' => 2,
                'features' => [
                    ['key' => 'invoices', 'name' => 'Unlimited invoices', 'type' => 'limit', 'limit' => -1],
                    ['key' => 'clients', 'name' => 'Unlimited clients', 'type' => 'limit', 'limit' => -1],
                    ['key' => 'products', 'name' => 'Unlimited products', 'type' => 'limit', 'limit' => -1],
                    ['key' => 'basic_reports', 'name' => 'Basic reports', 'type' => 'boolean', 'value' => true],
                    ['key' => 'advanced_reports', 'name' => 'Advanced reports & analytics', 'type' => 'boolean', 'value' => true],
                    ['key' => 'inventory_management', 'name' => 'Full inventory management', 'type' => 'boolean', 'value' => true],
                    ['key' => 'multi_currency', 'name' => 'Multi-currency support', 'type' => 'boolean', 'value' => true],
                    ['key' => 'api_access', 'name' => 'API access', 'type' => 'boolean', 'value' => true],
                    ['key' => 'priority_support', 'name' => 'Priority phone & email support', 'type' => 'boolean', 'value' => true],
                ],
            ],
            [
                'tier_key' => 'enterprise',
                'name' => 'Enterprise',
                'description' => 'For large organizations',
                'price_monthly' => 399,
                'price_annual' => 3990,
                'sort_order' => 3,
                'features' => [
                    ['key' => 'invoices', 'name' => 'Unlimited invoices', 'type' => 'limit', 'limit' => -1],
                    ['key' => 'clients', 'name' => 'Unlimited clients', 'type' => 'limit', 'limit' => -1],
                    ['key' => 'products', 'name' => 'Unlimited products', 'type' => 'limit', 'limit' => -1],
                    ['key' => 'users', 'name' => 'Unlimited users', 'type' => 'limit', 'limit' => -1],
                    ['key' => 'basic_reports', 'name' => 'Basic reports', 'type' => 'boolean', 'value' => true],
                    ['key' => 'advanced_reports', 'name' => 'Advanced reports & analytics', 'type' => 'boolean', 'value' => true],
                    ['key' => 'inventory_management', 'name' => 'Full inventory management', 'type' => 'boolean', 'value' => true],
                    ['key' => 'multi_currency', 'name' => 'Multi-currency support', 'type' => 'boolean', 'value' => true],
                    ['key' => 'api_access', 'name' => 'Full API access', 'type' => 'boolean', 'value' => true],
                    ['key' => 'white_label', 'name' => 'White-label branding', 'type' => 'boolean', 'value' => true],
                    ['key' => 'dedicated_support', 'name' => 'Dedicated account manager', 'type' => 'boolean', 'value' => true],
                    ['key' => 'custom_integrations', 'name' => 'Custom integrations', 'type' => 'boolean', 'value' => true],
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

            $this->command->info("Created CMS tier: {$tierData['tier_key']}");
        }
    }
}