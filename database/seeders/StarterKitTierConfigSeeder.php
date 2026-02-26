<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitTierConfig;
use App\Infrastructure\Persistence\Eloquent\Benefit;
use Illuminate\Database\Seeder;

class StarterKitTierConfigSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'tier_key' => 'lite',
                'tier_name' => 'Lite',
                'description' => 'Entry level - Learning & Foundation Building',
                'price' => 300.00,
                'storage_gb' => 5,
                'earning_potential_percentage' => 5.00,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'tier_key' => 'basic',
                'tier_name' => 'Basic',
                'description' => 'Skilled Member - Application & Consistency',
                'price' => 500.00,
                'storage_gb' => 10,
                'earning_potential_percentage' => 10.00,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'tier_key' => 'growth_plus',
                'tier_name' => 'Growth Plus',
                'description' => 'Experienced Member - Team Building & Mentorship',
                'price' => 1000.00,
                'storage_gb' => 25,
                'earning_potential_percentage' => 15.00,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'tier_key' => 'pro',
                'tier_name' => 'Pro',
                'description' => 'Top Performer - Excellence & Innovation',
                'price' => 2000.00,
                'storage_gb' => 50,
                'earning_potential_percentage' => 20.00,
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($tiers as $tierData) {
            $tier = StarterKitTierConfig::updateOrCreate(
                ['tier_key' => $tierData['tier_key']],
                $tierData
            );

            // Attach benefits based on tier
            $this->attachBenefitsToTier($tier);
        }
    }

    private function attachBenefitsToTier(StarterKitTierConfig $tier): void
    {
        $benefitsToAttach = [];

        // Get all starter kit benefits
        $starterKitBenefits = Benefit::where('benefit_type', 'starter_kit')->get();

        foreach ($starterKitBenefits as $benefit) {
            $shouldInclude = true;
            $limitValue = null;

            // Check tier allocations from benefit
            if ($benefit->tier_allocations) {
                $allocation = $benefit->tier_allocations[$tier->tier_key] ?? null;
                
                if ($allocation === null || $allocation === false) {
                    $shouldInclude = false;
                } elseif (is_numeric($allocation)) {
                    $limitValue = $allocation;
                } elseif ($allocation === true) {
                    $limitValue = null;
                }
            }

            // Special handling for storage allocation
            if ($benefit->slug === 'cloud-storage-allocation') {
                $shouldInclude = true;
                $limitValue = $tier->storage_gb;
            }

            if ($shouldInclude) {
                $benefitsToAttach[$benefit->id] = [
                    'is_included' => true,
                    'limit_value' => $limitValue,
                ];
            }
        }

        // Get physical items for this tier
        $physicalItems = Benefit::where('benefit_type', 'physical_item')->get();

        foreach ($physicalItems as $item) {
            $shouldInclude = false;

            if ($item->tier_allocations) {
                $allocation = $item->tier_allocations[$tier->tier_key] ?? null;
                
                if ($allocation === true || is_numeric($allocation)) {
                    $shouldInclude = true;
                }
            }

            if ($shouldInclude) {
                $benefitsToAttach[$item->id] = [
                    'is_included' => true,
                    'limit_value' => null,
                ];
            }
        }

        // Sync benefits to tier
        $tier->benefits()->sync($benefitsToAttach);
    }
}
