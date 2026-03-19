<?php

namespace Database\Seeders;

use App\Models\ModuleTier;
use App\Models\ModuleTierFeature;
use Illuminate\Database\Seeder;

/**
 * Adds numeric limits to GrowBuilder tiers for pages, products, and storage
 * This complements the existing boolean features with actual usage limits
 */
class GrowBuilderTierLimitsSeeder extends Seeder
{
    public function run(): void
    {
        $moduleId = 'growbuilder';

        $tierLimits = [
            'free' => [
                'pages' => 5,
                'products' => 0,
                'storage_mb' => 500,
                'sites' => 1,
            ],
            'starter' => [
                'pages' => 10,
                'products' => 20,
                'storage_mb' => 2000, // 2GB
                'sites' => 1,
            ],
            'business' => [
                'pages' => 50,
                'products' => -1, // unlimited
                'storage_mb' => 10000, // 10GB
                'sites' => 1,
            ],
            'agency' => [
                'pages' => -1, // unlimited
                'products' => -1, // unlimited
                'storage_mb' => 50000, // 50GB
                'sites' => 20,
            ],
        ];

        foreach ($tierLimits as $tierKey => $limits) {
            $tier = ModuleTier::where('module_id', $moduleId)
                ->where('tier_key', $tierKey)
                ->first();

            if (!$tier) {
                $this->command->warn("Tier '{$tierKey}' not found for module '{$moduleId}'");
                continue;
            }

            foreach ($limits as $limitKey => $limitValue) {
                ModuleTierFeature::updateOrCreate(
                    [
                        'module_tier_id' => $tier->id,
                        'feature_key' => $limitKey,
                    ],
                    [
                        'feature_name' => $this->getFeatureName($limitKey, $limitValue),
                        'feature_type' => 'limit',
                        'value_limit' => $limitValue,
                        'is_active' => true,
                    ]
                );
            }

            $this->command->info("Added limits for tier: {$tierKey}");
        }

        $this->command->info('GrowBuilder tier limits seeded successfully!');
    }

    private function getFeatureName(string $key, int $value): string
    {
        return match($key) {
            'pages' => 'Pages',
            'products' => 'Products', 
            'storage_mb' => 'Storage',
            'sites' => 'Sites',
            default => ucfirst($key),
        };
    }

    private function formatStorage(int $mb): string
    {
        // Return just "Storage" as the feature name should be generic
        // The value will be displayed separately
        return 'Storage';
    }
}
