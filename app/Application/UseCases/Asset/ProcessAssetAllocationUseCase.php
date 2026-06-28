<?php

namespace App\Application\UseCases\Asset;

use App\Domain\Reward\Repositories\AssetRepository;
use App\Domain\Reward\Repositories\PhysicalRewardAllocationRepository;
use App\Domain\Reward\ValueObjects\AssetType;
use App\Domain\Reward\ValueObjects\AllocationStatus;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessAssetAllocationUseCase
{
    public function __construct(
        private AssetRepository $assetRepository,
        private PhysicalRewardAllocationRepository $allocationRepository
    ) {}

    public function execute(int $userId): array
    {
        return DB::transaction(function () use ($userId) {
            $user = User::with('investmentTier')->findOrFail($userId);
            
            if (!$user->investmentTier) {
                return ['error' => 'User has no tier assigned'];
            }

            $eligibleAssets = $this->checkAssetEligibility($user);
            $allocations = [];

            foreach ($eligibleAssets as $assetType => $assetData) {
                $allocation = $this->allocateAsset($user, $assetType, $assetData);
                if ($allocation) {
                    $allocations[] = $allocation;
                }
            }

            return [
                'success' => true,
                'user_id' => $userId,
                'tier' => $user->investmentTier->name,
                'allocations' => $allocations,
                'total_allocated' => count($allocations)
            ];
        });
    }

    private function checkAssetEligibility(User $user): array
    {
        $tier = $user->investmentTier;
        $eligibleAssets = [];

        // Check tier-specific asset eligibility based on requirements
        $assetRequirements = $this->getAssetRequirements();
        
        foreach ($assetRequirements as $assetType => $requirements) {
            if ($this->meetsAssetRequirements($user, $tier, $requirements)) {
                $eligibleAssets[$assetType] = $requirements;
            }
        }

        return $eligibleAssets;
    }

    private function getAssetRequirements(): array
    {
        return [
            'STARTER_KIT' => [
                'tier' => 'Bronze Member',
                'months_required' => 1,
                'min_referrals' => 1,
                'min_team_volume' => 0,
                'value_range' => [500, 1000]
            ],
            'SMARTPHONE' => [
                'tier' => 'Silver Member',
                'months_required' => 3,
                'min_referrals' => 3,
                'min_team_volume' => 15000,
                'value_range' => [2000, 4000]
            ],
            'TABLET' => [
                'tier' => 'Silver Member',
                'months_required' => 3,
                'min_referrals' => 3,
                'min_team_volume' => 15000,
                'value_range' => [2000, 4000]
            ],
            'MOTORBIKE' => [
                'tier' => 'Gold Member',
                'months_required' => 6,
                'min_referrals' => 10,
                'min_team_volume' => 50000,
                'value_range' => [8000, 15000]
            ],
            'CAR' => [
                'tier' => 'Diamond Member',
                'months_required' => 9,
                'min_referrals' => 25,
                'min_team_volume' => 150000,
                'value_range' => [25000, 50000]
            ],
            'PROPERTY' => [
                'tier' => 'Elite Member',
                'months_required' => 12,
                'min_referrals' => 50,
                'min_team_volume' => 500000,
                'value_range' => [75000, 150000]
            ]
        ];
    }

    private function meetsAssetRequirements(User $user, InvestmentTier $tier, array $requirements): bool
    {
        // Check tier requirement
        if ($tier->name !== $requirements['tier']) {
            return false;
        }

        // Check months at tier
        $monthsAtTier = $this->getMonthsAtTier($user, $tier);
        if ($monthsAtTier < $requirements['months_required']) {
            return false;
        }

        // Check referrals
        $activeReferrals = $user->activeReferrals()->count();
        if ($activeReferrals < $requirements['min_referrals']) {
            return false;
        }

        // Check team volume
        $teamVolume = $user->monthly_team_volume ?? 0;
        if ($teamVolume < $requirements['min_team_volume']) {
            return false;
        }

        // Check if already allocated this type of asset
        $existingAllocation = PhysicalRewardAllocation::where('user_id', $user->id)
            ->whereHas('physicalReward', function ($query) use ($requirements) {
                $query->where('tier_requirement', $requirements['tier']);
            })
            ->where('status', '!=', 'forfeited')
            ->exists();

        return !$existingAllocation;
    }

    private function getMonthsAtTier(User $user, InvestmentTier $tier): int
    {
        // Calculate months since user reached this tier
        $tierQualification = $user->tierQualifications()
            ->where('tier_id', $tier->id)
            ->latest()
            ->first();

        if (!$tierQualification) {
            return 0;
        }

        return $tierQualification->created_at->diffInMonths(now());
    }

    private function allocateAsset(User $user, string $assetType, array $requirements): ?array
    {
        // Find available asset of this type
        $availableAsset = PhysicalReward::where('type', $assetType)
            ->where('tier_requirement', $requirements['tier'])
            ->where('status', 'available')
            ->first();

        if (!$availableAsset) {
            Log::warning("No available asset for allocation", [
                'user_id' => $user->id,
                'asset_type' => $assetType,
                'tier' => $requirements['tier']
            ]);
            return null;
        }

        // Create allocation
        $allocation = PhysicalRewardAllocation::create([
            'user_id' => $user->id,
            'physical_reward_id' => $availableAsset->id,
            'tier_id' => $user->investment_tier_id,
            'qualifying_team_volume' => $user->monthly_team_volume ?? 0,
            'maintenance_period_months' => 12, // 12 months for ownership transfer
            'status' => 'pending',
            'allocated_at' => now(),
        ]);

        // Update asset status
        $availableAsset->update(['status' => 'allocated']);

        Log::info("Asset allocated", [
            'user_id' => $user->id,
            'asset_id' => $availableAsset->id,
            'asset_type' => $assetType,
            'allocation_id' => $allocation->id
        ]);

        return [
            'allocation_id' => $allocation->id,
            'asset_type' => $assetType,
            'asset_value' => $availableAsset->value,
            'maintenance_period' => 12,
            'status' => 'pending'
        ];
    }
}