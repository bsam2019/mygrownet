<?php

namespace App\Application\Services;

use App\Application\UseCases\Asset\ProcessAssetAllocationUseCase;
use App\Application\UseCases\Asset\ProcessAssetMaintenanceUseCase;
use App\Models\User;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation;
use App\Services\AssetIncomeTrackingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetAllocationService
{
    public function __construct(
        private ProcessAssetAllocationUseCase $processAssetAllocationUseCase,
        private ProcessAssetMaintenanceUseCase $processAssetMaintenanceUseCase,
        private AssetIncomeTrackingService $assetIncomeTrackingService
    ) {}

    /**
     * Process asset allocation for a specific user
     */
    public function processUserAssetAllocation(int $userId): array
    {
        try {
            return $this->processAssetAllocationUseCase->execute($userId);
        } catch (\Exception $e) {
            Log::error("Asset allocation failed", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process asset allocations for all eligible users
     */
    public function processAllEligibleAllocations(): array
    {
        $results = [
            'processed' => 0,
            'allocated' => 0,
            'failed' => 0,
            'allocations' => []
        ];

        $eligibleUsers = $this->getEligibleUsers();

        foreach ($eligibleUsers as $user) {
            try {
                $result = $this->processAssetAllocationUseCase->execute($user->id);
                $results['processed']++;

                if ($result['success'] && $result['total_allocated'] > 0) {
                    $results['allocated']++;
                    $results['allocations'] = array_merge($results['allocations'], $result['allocations']);
                }
            } catch (\Exception $e) {
                $results['failed']++;
                Log::error("Asset allocation failed for user", [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Process asset maintenance for all active allocations
     */
    public function processAssetMaintenance(?int $allocationId = null): array
    {
        return $this->processAssetMaintenanceUseCase->execute($allocationId);
    }

    /**
     * Get asset allocation status for a user
     */
    public function getUserAssetStatus(int $userId): array
    {
        $user = User::with('investmentTier')->findOrFail($userId);
        
        $allocations = PhysicalRewardAllocation::with(['physicalReward'])
            ->where('user_id', $userId)
            ->get();

        $maintenanceStatus = $this->processAssetMaintenanceUseCase->getAssetMaintenanceStatus($userId);
        $incomeTracking = $this->getAssetIncomeTracking($userId);

        return [
            'user_id' => $userId,
            'tier' => $user->investmentTier?->name,
            'total_allocations' => $allocations->count(),
            'active_allocations' => $allocations->where('status', 'active')->count(),
            'completed_allocations' => $allocations->where('status', 'completed')->count(),
            'forfeited_allocations' => $allocations->where('status', 'forfeited')->count(),
            'total_asset_value' => $allocations->sum(fn($a) => $a->physicalReward->value),
            'maintenance_status' => $maintenanceStatus,
            'income_tracking' => $incomeTracking,
            'allocations' => $allocations->map(function ($allocation) {
                return [
                    'id' => $allocation->id,
                    'asset_type' => $allocation->physicalReward->type,
                    'asset_value' => $allocation->physicalReward->value,
                    'status' => $allocation->status,
                    'maintenance_status' => $allocation->maintenance_status,
                    'allocated_at' => $allocation->allocated_at,
                    'completed_at' => $allocation->completed_at,
                    'forfeited_at' => $allocation->forfeited_at
                ];
            })
        ];
    }

    /**
     * Get available assets for allocation
     */
    public function getAvailableAssets(): array
    {
        $assets = PhysicalReward::where('status', 'available')
            ->orderBy('type')
            ->orderBy('value')
            ->get();

        return $assets->groupBy('type')->map(function ($typeAssets, $type) {
            return [
                'type' => $type,
                'count' => $typeAssets->count(),
                'value_range' => [
                    'min' => $typeAssets->min('value'),
                    'max' => $typeAssets->max('value')
                ],
                'assets' => $typeAssets->map(function ($asset) {
                    return [
                        'id' => $asset->id,
                        'model' => $asset->model,
                        'value' => $asset->value,
                        'tier_requirement' => $asset->tier_requirement
                    ];
                })
            ];
        })->toArray();
    }

    /**
     * Create asset buyback offer
     */
    public function createBuybackOffer(int $allocationId, float $offerAmount): array
    {
        $allocation = PhysicalRewardAllocation::with(['physicalReward', 'user'])
            ->findOrFail($allocationId);

        if ($allocation->status !== 'completed') {
            return [
                'success' => false,
                'error' => 'Asset must be completed to be eligible for buyback'
            ];
        }

        $marketValue = $this->calculateMarketValue($allocation);
        $maxOffer = $marketValue * 0.9; // 90% of market value

        if ($offerAmount > $maxOffer) {
            return [
                'success' => false,
                'error' => "Offer amount exceeds maximum buyback value of K{$maxOffer}"
            ];
        }

        // Create buyback record (you might want to create a separate table for this)
        $buyback = [
            'allocation_id' => $allocationId,
            'user_id' => $allocation->user_id,
            'asset_type' => $allocation->physicalReward->type,
            'original_value' => $allocation->physicalReward->value,
            'market_value' => $marketValue,
            'offer_amount' => $offerAmount,
            'status' => 'pending',
            'created_at' => now()
        ];

        Log::info("Buyback offer created", $buyback);

        return [
            'success' => true,
            'buyback_offer' => $buyback
        ];
    }

    /**
     * Get asset income tracking for a user
     */
    private function getAssetIncomeTracking(int $userId): array
    {
        return $this->assetIncomeTrackingService->getUserAssetIncome($userId);
    }

    /**
     * Calculate current market value of an asset
     */
    private function calculateMarketValue(PhysicalRewardAllocation $allocation): float
    {
        $originalValue = $allocation->physicalReward->value;
        $monthsOwned = $allocation->completed_at?->diffInMonths(now()) ?? 0;
        
        // Apply depreciation based on asset type
        $depreciationRate = match ($allocation->physicalReward->type) {
            'SMARTPHONE', 'TABLET' => 0.02, // 2% per month
            'MOTORBIKE' => 0.015, // 1.5% per month
            'CAR' => 0.01, // 1% per month
            'PROPERTY' => -0.005, // Property appreciates 0.5% per month
            default => 0.01
        };

        $depreciationFactor = 1 - ($depreciationRate * $monthsOwned);
        return max($originalValue * 0.3, $originalValue * $depreciationFactor); // Minimum 30% of original value
    }

    /**
     * Get users eligible for asset allocation
     */
    private function getEligibleUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('investmentTier')
            ->where('monthly_team_volume', '>', 0)
            ->whereHas('referrals', function ($query) {
                $query->whereHas('investmentTier'); // Has active referrals
            })
            ->whereDoesntHave('physicalRewardAllocations', function ($query) {
                $query->whereIn('status', ['pending', 'active']);
            })
            ->get();
    }

    /**
     * Get asset allocation statistics
     */
    public function getAssetAllocationStatistics(): array
    {
        $totalAllocations = PhysicalRewardAllocation::count();
        $activeAllocations = PhysicalRewardAllocation::where('status', 'active')->count();
        $completedAllocations = PhysicalRewardAllocation::where('status', 'completed')->count();
        $forfeitedAllocations = PhysicalRewardAllocation::where('status', 'forfeited')->count();

        $allocationsByType = PhysicalRewardAllocation::join('physical_rewards', 'physical_reward_allocations.physical_reward_id', '=', 'physical_rewards.id')
            ->selectRaw('physical_rewards.type, COUNT(*) as count, SUM(physical_rewards.value) as total_value')
            ->groupBy('physical_rewards.type')
            ->get();

        $allocationsByTier = PhysicalRewardAllocation::join('investment_tiers', 'physical_reward_allocations.tier_id', '=', 'investment_tiers.id')
            ->selectRaw('investment_tiers.name as tier_name, COUNT(*) as count')
            ->groupBy('investment_tiers.name')
            ->get();

        return [
            'total_allocations' => $totalAllocations,
            'active_allocations' => $activeAllocations,
            'completed_allocations' => $completedAllocations,
            'forfeited_allocations' => $forfeitedAllocations,
            'completion_rate' => $totalAllocations > 0 ? ($completedAllocations / $totalAllocations) * 100 : 0,
            'forfeiture_rate' => $totalAllocations > 0 ? ($forfeitedAllocations / $totalAllocations) * 100 : 0,
            'allocations_by_type' => $allocationsByType,
            'allocations_by_tier' => $allocationsByTier,
            'total_asset_value' => PhysicalRewardAllocation::join('physical_rewards', 'physical_reward_allocations.physical_reward_id', '=', 'physical_rewards.id')
                ->sum('physical_rewards.value')
        ];
    }

    /**
     * Process asset recovery for forfeited assets
     */
    public function processAssetRecovery(int $allocationId): array
    {
        $allocation = PhysicalRewardAllocation::with(['physicalReward', 'user'])
            ->findOrFail($allocationId);

        if ($allocation->status !== 'forfeited') {
            return [
                'success' => false,
                'error' => 'Only forfeited assets can be recovered'
            ];
        }

        DB::transaction(function () use ($allocation) {
            // Reset asset to available
            $allocation->physicalReward->update([
                'status' => 'available',
                'owner_id' => null
            ]);

            // Update allocation status
            $allocation->update([
                'status' => 'recovered',
                'recovered_at' => now()
            ]);
        });

        Log::info("Asset recovered", [
            'allocation_id' => $allocationId,
            'asset_type' => $allocation->physicalReward->type,
            'original_user_id' => $allocation->user_id
        ]);

        return [
            'success' => true,
            'message' => 'Asset recovered and made available for reallocation'
        ];
    }
}