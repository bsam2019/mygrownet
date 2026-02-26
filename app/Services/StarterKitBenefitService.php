<?php

namespace App\Services;

use App\Infrastructure\Persistence\Eloquent\Benefit;
use App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitPurchaseModel;
use App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitTierConfig;
use Illuminate\Support\Facades\Log;

class StarterKitBenefitService
{
    /**
     * Assign benefits to a starter kit purchase based on tier
     */
    public function assignBenefitsToKit(StarterKitPurchaseModel $purchase): void
    {
        $tier = $purchase->tier;
        
        Log::info('Assigning benefits to starter kit', [
            'purchase_id' => $purchase->id,
            'tier' => $tier,
        ]);

        // Try to get benefits from tier configuration first
        $tierConfig = StarterKitTierConfig::where('tier_key', $tier)
            ->where('is_active', true)
            ->with('benefits')
            ->first();

        $benefitsToAttach = [];

        if ($tierConfig) {
            // Use database tier configuration
            Log::info('Using database tier configuration for benefits', [
                'tier_config_id' => $tierConfig->id,
            ]);

            foreach ($tierConfig->benefits as $benefit) {
                $benefitsToAttach[$benefit->id] = [
                    'included' => $benefit->pivot->is_included,
                    'limit_value' => $benefit->pivot->limit_value,
                ];

                // Add fulfillment status for physical items
                if ($benefit->benefit_type === 'physical_item') {
                    $benefitsToAttach[$benefit->id]['fulfillment_status'] = 'pending';
                }
            }
        } else {
            // Fallback to legacy tier_allocations method
            Log::warning('Tier config not found, using legacy benefit allocation', [
                'tier' => $tier,
            ]);

            // Get all starter kit benefits
            $starterKitBenefits = Benefit::where('benefit_type', 'starter_kit')
                ->where('is_active', true)
                ->get();

            // Get physical items for this tier
            $physicalItems = Benefit::where('benefit_type', 'physical_item')
                ->where('is_active', true)
                ->get();

            // Process starter kit benefits
            foreach ($starterKitBenefits as $benefit) {
                $shouldInclude = true;
                $limitValue = null;

                // Check tier allocations
                if ($benefit->tier_allocations) {
                    $allocation = $benefit->tier_allocations[$tier] ?? null;
                    
                    if ($allocation === null || $allocation === false) {
                        $shouldInclude = false;
                    } elseif (is_numeric($allocation)) {
                        $limitValue = $allocation;
                    }
                }

                if ($shouldInclude) {
                    $benefitsToAttach[$benefit->id] = [
                        'included' => true,
                        'limit_value' => $limitValue,
                    ];
                }
            }

            // Process physical items
            foreach ($physicalItems as $item) {
                $shouldInclude = false;

                if ($item->tier_allocations) {
                    $allocation = $item->tier_allocations[$tier] ?? null;
                    
                    if ($allocation === true || is_numeric($allocation)) {
                        $shouldInclude = true;
                    }
                }

                if ($shouldInclude) {
                    $benefitsToAttach[$item->id] = [
                        'included' => true,
                        'limit_value' => null,
                        'fulfillment_status' => 'pending',
                    ];
                }
            }
        }

        // Attach benefits to purchase
        $purchase->benefits()->sync($benefitsToAttach);

        Log::info('Benefits assigned successfully', [
            'purchase_id' => $purchase->id,
            'benefits_count' => count($benefitsToAttach),
        ]);
    }

    /**
     * Get storage allocation for a tier
     */
    public function getStorageAllocation(string $tier): int
    {
        // Try to get from database tier config first
        $tierConfig = StarterKitTierConfig::where('tier_key', $tier)
            ->where('is_active', true)
            ->first();

        if ($tierConfig) {
            return $tierConfig->storage_gb;
        }

        // Fallback to benefit tier_allocations
        $storageBenefit = Benefit::where('slug', 'cloud-storage-allocation')
            ->where('is_active', true)
            ->first();

        if (!$storageBenefit || !$storageBenefit->tier_allocations) {
            // Final fallback to default allocations
            return match($tier) {
                'lite' => 5,
                'basic' => 10,
                'growth_plus' => 25,
                'pro' => 50,
                default => 5,
            };
        }

        return $storageBenefit->tier_allocations[$tier] ?? 5;
    }

    /**
     * Check if a user has access to a specific benefit
     */
    public function userHasBenefit(int $userId, string $benefitSlug): bool
    {
        $benefit = Benefit::where('slug', $benefitSlug)->first();
        
        if (!$benefit) {
            return false;
        }

        $purchase = StarterKitPurchaseModel::where('user_id', $userId)
            ->where('status', 'completed')
            ->latest()
            ->first();

        if (!$purchase) {
            return false;
        }

        return $purchase->benefits()
            ->where('benefit_id', $benefit->id)
            ->wherePivot('included', true)
            ->exists();
    }

    /**
     * Get user's benefit allocation value
     */
    public function getUserBenefitAllocation(int $userId, string $benefitSlug): mixed
    {
        $benefit = Benefit::where('slug', $benefitSlug)->first();
        
        if (!$benefit) {
            return null;
        }

        $purchase = StarterKitPurchaseModel::where('user_id', $userId)
            ->where('status', 'completed')
            ->latest()
            ->first();

        if (!$purchase) {
            return null;
        }

        $pivot = $purchase->benefits()
            ->where('benefit_id', $benefit->id)
            ->wherePivot('included', true)
            ->first()?->pivot;

        return $pivot?->limit_value;
    }
}
