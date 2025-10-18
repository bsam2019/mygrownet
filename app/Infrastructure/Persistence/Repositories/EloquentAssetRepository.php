<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Reward\Repositories\AssetRepository;
use App\Domain\Reward\ValueObjects\RewardId;
use App\Domain\Reward\ValueObjects\AssetType;
use App\Domain\Reward\ValueObjects\AssetValue;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\TeamVolumeAmount;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation;
use App\Models\User;
use App\Models\TeamVolume;
use App\Models\TierQualification;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class EloquentAssetRepository implements AssetRepository
{
    public function findById(RewardId $id): ?array
    {
        $asset = PhysicalReward::find($id->value());
        
        return $asset ? $this->toAssetArray($asset) : null;
    }

    public function findByType(AssetType $type): array
    {
        $assets = PhysicalReward::where('category', $type->value())
            ->active()
            ->get();

        return $assets->map(fn($asset) => $this->toAssetArray($asset))->toArray();
    }

    public function findAvailableAssets(): array
    {
        $assets = PhysicalReward::active()
            ->available()
            ->orderBy('estimated_value')
            ->get();

        return $assets->map(fn($asset) => $this->toAssetArray($asset))->toArray();
    }

    public function findAvailableForTier(string $tierName): array
    {
        $assets = PhysicalReward::active()
            ->available()
            ->forTier($tierName)
            ->orderBy('estimated_value')
            ->get();

        return $assets->map(fn($asset) => $this->toAssetArray($asset))->toArray();
    }

    public function isAssetAvailable(RewardId $assetId): bool
    {
        $asset = PhysicalReward::find($assetId->value());
        
        return $asset && $asset->isAvailable();
    }

    public function checkUserEligibility(
        UserId $userId, 
        RewardId $assetId,
        TeamVolumeAmount $teamVolume,
        int $activeReferrals,
        int $consecutiveMonths
    ): bool {
        $asset = PhysicalReward::find($assetId->value());
        $user = User::find($userId->value());
        
        if (!$asset || !$user) {
            return false;
        }

        // Check basic eligibility using the model method
        if (!$asset->isEligibleForUser($user)) {
            return false;
        }

        // Additional checks with provided parameters
        if ($teamVolume->value() < $asset->required_team_volume) {
            return false;
        }

        if ($activeReferrals < $asset->required_referrals) {
            return false;
        }

        if ($consecutiveMonths < $asset->required_sustained_months) {
            return false;
        }

        return true;
    }

    public function getEligibleAssetsForUser(
        UserId $userId,
        string $tierName,
        TeamVolumeAmount $teamVolume,
        int $activeReferrals,
        int $consecutiveMonths
    ): array {
        $user = User::find($userId->value());
        if (!$user) {
            return [];
        }

        $assets = PhysicalReward::active()
            ->available()
            ->forTier($tierName)
            ->where('required_team_volume', '<=', $teamVolume->value())
            ->where('required_referrals', '<=', $activeReferrals)
            ->where('required_sustained_months', '<=', $consecutiveMonths)
            ->orderBy('estimated_value')
            ->get();

        return $assets->filter(function ($asset) use ($user) {
            return $asset->isEligibleForUser($user);
        })->map(fn($asset) => $this->toAssetArray($asset))->values()->toArray();
    }

    public function reserveAsset(RewardId $assetId): bool
    {
        $asset = PhysicalReward::find($assetId->value());
        
        if (!$asset || !$asset->isAvailable()) {
            return false;
        }

        // Increment allocated quantity to reserve
        $asset->increment('allocated_quantity');
        
        return true;
    }

    public function releaseAssetReservation(RewardId $assetId): bool
    {
        $asset = PhysicalReward::find($assetId->value());
        
        if (!$asset || $asset->allocated_quantity <= 0) {
            return false;
        }

        // Decrement allocated quantity to release reservation
        $asset->decrement('allocated_quantity');
        
        return true;
    }

    public function updateAllocationCount(RewardId $assetId, int $change): void
    {
        $asset = PhysicalReward::find($assetId->value());
        
        if (!$asset) {
            return;
        }

        if ($change > 0) {
            $asset->increment('allocated_quantity', $change);
        } elseif ($change < 0) {
            $asset->decrement('allocated_quantity', abs($change));
        }
    }

    public function getAssetAllocationStats(): array
    {
        $stats = PhysicalReward::selectRaw('
            category,
            COUNT(*) as total_assets,
            SUM(available_quantity) as total_available,
            SUM(allocated_quantity) as total_allocated,
            AVG(estimated_value) as avg_value,
            SUM(estimated_value * allocated_quantity) as total_allocated_value
        ')
        ->active()
        ->groupBy('category')
        ->get();

        $overallStats = PhysicalReward::selectRaw('
            COUNT(*) as total_assets,
            SUM(available_quantity) as total_available,
            SUM(allocated_quantity) as total_allocated,
            AVG(estimated_value) as avg_value,
            SUM(estimated_value * allocated_quantity) as total_allocated_value
        ')
        ->active()
        ->first();

        return [
            'by_category' => $stats->mapWithKeys(function ($stat) {
                return [$stat->category => [
                    'total_assets' => $stat->total_assets,
                    'total_available' => $stat->total_available,
                    'total_allocated' => $stat->total_allocated,
                    'allocation_rate' => $stat->total_available > 0 
                        ? ($stat->total_allocated / $stat->total_available) * 100 
                        : 0,
                    'avg_value' => (float) $stat->avg_value,
                    'total_allocated_value' => (float) $stat->total_allocated_value,
                ]];
            })->toArray(),
            'overall' => [
                'total_assets' => $overallStats->total_assets,
                'total_available' => $overallStats->total_available,
                'total_allocated' => $overallStats->total_allocated,
                'allocation_rate' => $overallStats->total_available > 0 
                    ? ($overallStats->total_allocated / $overallStats->total_available) * 100 
                    : 0,
                'avg_value' => (float) $overallStats->avg_value,
                'total_allocated_value' => (float) $overallStats->total_allocated_value,
            ]
        ];
    }

    public function findByValueRange(AssetValue $minValue, AssetValue $maxValue): array
    {
        $assets = PhysicalReward::active()
            ->whereBetween('estimated_value', [$minValue->value(), $maxValue->value()])
            ->orderBy('estimated_value')
            ->get();

        return $assets->map(fn($asset) => $this->toAssetArray($asset))->toArray();
    }

    public function findIncomeGeneratingAssets(): array
    {
        $assets = PhysicalReward::active()
            ->where('income_generating', true)
            ->orderBy('estimated_monthly_income', 'desc')
            ->get();

        return $assets->map(fn($asset) => $this->toAssetArray($asset))->toArray();
    }

    public function findAssetsRequiringMaintenance(): array
    {
        $assets = PhysicalReward::active()
            ->where('requires_performance_maintenance', true)
            ->orderBy('maintenance_period_months')
            ->get();

        return $assets->map(fn($asset) => $this->toAssetArray($asset))->toArray();
    }

    public function getInventoryReport(): array
    {
        $assets = PhysicalReward::with(['allocations' => function ($query) {
            $query->whereIn('status', ['allocated', 'delivered', 'ownership_transferred']);
        }])
        ->get();

        return $assets->map(function ($asset) {
            $activeAllocations = $asset->allocations->count();
            $deliveredCount = $asset->allocations->where('status', 'delivered')->count();
            $transferredCount = $asset->allocations->where('status', 'ownership_transferred')->count();

            return [
                'id' => $asset->id,
                'name' => $asset->name,
                'category' => $asset->category,
                'available_quantity' => $asset->available_quantity,
                'allocated_quantity' => $asset->allocated_quantity,
                'remaining_quantity' => $asset->available_quantity - $asset->allocated_quantity,
                'allocation_rate' => $asset->available_quantity > 0 
                    ? ($asset->allocated_quantity / $asset->available_quantity) * 100 
                    : 0,
                'estimated_value' => $asset->estimated_value,
                'total_value_allocated' => $asset->estimated_value * $asset->allocated_quantity,
                'active_allocations' => $activeAllocations,
                'delivered_count' => $deliveredCount,
                'ownership_transferred_count' => $transferredCount,
                'is_active' => $asset->is_active,
                'requires_maintenance' => $asset->requires_performance_maintenance,
                'income_generating' => $asset->income_generating,
            ];
        })->toArray();
    }

    public function getAssetPerformanceMetrics(RewardId $assetId): array
    {
        $asset = PhysicalReward::with(['allocations'])->find($assetId->value());
        
        if (!$asset) {
            return [];
        }

        $allocations = $asset->allocations;
        $totalAllocations = $allocations->count();
        
        if ($totalAllocations === 0) {
            return [
                'asset_id' => $asset->id,
                'asset_name' => $asset->name,
                'total_allocations' => 0,
                'performance_metrics' => []
            ];
        }

        $deliveredCount = $allocations->where('status', 'delivered')->count();
        $transferredCount = $allocations->where('status', 'ownership_transferred')->count();
        $forfeitedCount = $allocations->where('status', 'forfeited')->count();
        
        $totalIncomeGenerated = $allocations->sum('total_income_generated');
        $avgMonthlyIncome = $allocations->where('monthly_income_average', '>', 0)->avg('monthly_income_average');
        
        $maintenanceCompliant = $allocations->where('maintenance_compliant', true)->count();
        $avgMaintenanceMonths = $allocations->avg('maintenance_months_completed');

        return [
            'asset_id' => $asset->id,
            'asset_name' => $asset->name,
            'total_allocations' => $totalAllocations,
            'performance_metrics' => [
                'delivery_rate' => ($deliveredCount / $totalAllocations) * 100,
                'ownership_transfer_rate' => ($transferredCount / $totalAllocations) * 100,
                'forfeiture_rate' => ($forfeitedCount / $totalAllocations) * 100,
                'maintenance_compliance_rate' => $totalAllocations > 0 
                    ? ($maintenanceCompliant / $totalAllocations) * 100 
                    : 0,
                'avg_maintenance_months' => (float) $avgMaintenanceMonths,
                'total_income_generated' => (float) $totalIncomeGenerated,
                'avg_monthly_income' => (float) $avgMonthlyIncome,
                'roi_percentage' => $asset->estimated_value > 0 
                    ? ($totalIncomeGenerated / ($asset->estimated_value * $totalAllocations)) * 100 
                    : 0,
            ]
        ];
    }

    public function findByCategory(string $category): array
    {
        $assets = PhysicalReward::byCategory($category)
            ->active()
            ->orderBy('estimated_value')
            ->get();

        return $assets->map(fn($asset) => $this->toAssetArray($asset))->toArray();
    }

    public function getLowStockAlerts(int $threshold = 5): array
    {
        $assets = PhysicalReward::active()
            ->whereRaw('(available_quantity - allocated_quantity) <= ?', [$threshold])
            ->orderBy('available_quantity')
            ->get();

        return $assets->map(function ($asset) {
            return [
                'id' => $asset->id,
                'name' => $asset->name,
                'category' => $asset->category,
                'remaining_quantity' => $asset->available_quantity - $asset->allocated_quantity,
                'available_quantity' => $asset->available_quantity,
                'allocated_quantity' => $asset->allocated_quantity,
                'estimated_value' => $asset->estimated_value,
                'urgency_level' => $this->calculateUrgencyLevel($asset->available_quantity - $asset->allocated_quantity),
            ];
        })->toArray();
    }

    public function updateAssetSpecifications(RewardId $assetId, array $specifications): void
    {
        PhysicalReward::where('id', $assetId->value())
            ->update(['specifications' => $specifications]);
    }

    public function deactivateAsset(RewardId $assetId): void
    {
        PhysicalReward::where('id', $assetId->value())
            ->update(['is_active' => false]);
    }

    public function reactivateAsset(RewardId $assetId): void
    {
        PhysicalReward::where('id', $assetId->value())
            ->update(['is_active' => true]);
    }

    private function toAssetArray(PhysicalReward $asset): array
    {
        return [
            'id' => $asset->id,
            'name' => $asset->name,
            'description' => $asset->description,
            'category' => $asset->category,
            'estimated_value' => $asset->estimated_value,
            'required_membership_tiers' => $asset->required_membership_tiers,
            'required_referrals' => $asset->required_referrals,
            'required_subscription_amount' => $asset->required_subscription_amount,
            'required_sustained_months' => $asset->required_sustained_months,
            'required_team_volume' => $asset->required_team_volume,
            'required_team_depth' => $asset->required_team_depth,
            'maintenance_period_months' => $asset->maintenance_period_months,
            'requires_performance_maintenance' => $asset->requires_performance_maintenance,
            'income_generating' => $asset->income_generating,
            'estimated_monthly_income' => $asset->estimated_monthly_income,
            'asset_management_options' => $asset->asset_management_options,
            'ownership_type' => $asset->ownership_type,
            'ownership_conditions' => $asset->ownership_conditions,
            'available_quantity' => $asset->available_quantity,
            'allocated_quantity' => $asset->allocated_quantity,
            'remaining_quantity' => $asset->available_quantity - $asset->allocated_quantity,
            'image_url' => $asset->image_url,
            'specifications' => $asset->specifications,
            'terms_and_conditions' => $asset->terms_and_conditions,
            'is_active' => $asset->is_active,
            'is_available' => $asset->isAvailable(),
        ];
    }

    private function calculateUrgencyLevel(int $remainingQuantity): string
    {
        return match(true) {
            $remainingQuantity <= 0 => 'CRITICAL',
            $remainingQuantity <= 2 => 'HIGH',
            $remainingQuantity <= 5 => 'MEDIUM',
            default => 'LOW'
        };
    }
}