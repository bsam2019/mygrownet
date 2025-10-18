<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Reward\Repositories\PhysicalRewardAllocationRepository;
use App\Domain\Reward\Entities\PhysicalRewardAllocation;
use App\Domain\Reward\ValueObjects\RewardAllocationId;
use App\Domain\Reward\ValueObjects\RewardId;
use App\Domain\Reward\ValueObjects\AllocationStatus;
use App\Domain\Reward\ValueObjects\MaintenanceStatus;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\TeamVolumeAmount;
use App\Models\PhysicalRewardAllocation as PhysicalRewardAllocationModel;
use DateTimeImmutable;

class EloquentPhysicalRewardAllocationRepository implements PhysicalRewardAllocationRepository
{
    public function findById(RewardAllocationId $id): ?PhysicalRewardAllocation
    {
        $model = PhysicalRewardAllocationModel::find($id->value());
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByUserId(UserId $userId): array
    {
        $models = PhysicalRewardAllocationModel::where('user_id', $userId->value())
            ->with(['physicalReward', 'user'])
            ->orderBy('allocated_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findByRewardId(RewardId $rewardId): array
    {
        $models = PhysicalRewardAllocationModel::where('physical_reward_id', $rewardId->value())
            ->with(['physicalReward', 'user'])
            ->orderBy('allocated_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findByStatus(AllocationStatus $status): array
    {
        $models = PhysicalRewardAllocationModel::withStatus($status->value())
            ->with(['physicalReward', 'user'])
            ->orderBy('allocated_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findRequiringMaintenanceCheck(): array
    {
        $models = PhysicalRewardAllocationModel::maintenanceDue()
            ->with(['physicalReward', 'user'])
            ->orderBy('last_maintenance_check')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findEligibleForOwnershipTransfer(): array
    {
        $models = PhysicalRewardAllocationModel::eligibleForOwnershipTransfer()
            ->with(['physicalReward', 'user'])
            ->orderBy('allocated_at')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function userHasAllocationForReward(UserId $userId, RewardId $rewardId): bool
    {
        return PhysicalRewardAllocationModel::where('user_id', $userId->value())
            ->where('physical_reward_id', $rewardId->value())
            ->whereIn('status', ['allocated', 'delivered', 'ownership_transferred'])
            ->exists();
    }

    public function save(PhysicalRewardAllocation $allocation): void
    {
        $data = [
            'user_id' => $allocation->getUserId()->value(),
            'physical_reward_id' => $allocation->getRewardId()->value(),
            'team_volume_at_allocation' => $allocation->getPerformanceAtAllocation()['team_volume'],
            'active_referrals_at_allocation' => $allocation->getPerformanceAtAllocation()['active_referrals'],
            'team_depth_at_allocation' => $allocation->getPerformanceAtAllocation()['team_depth'],
            'status' => $allocation->getStatus()->value(),
            'allocated_at' => $allocation->getAllocatedAt()->format('Y-m-d H:i:s'),
            'delivered_at' => $allocation->getDeliveredAt()?->format('Y-m-d H:i:s'),
            'ownership_transferred_at' => $allocation->getOwnershipTransferredAt()?->format('Y-m-d H:i:s'),
            'maintenance_compliant' => $allocation->getMaintenanceStatus()->isCompliant(),
            'maintenance_months_completed' => $allocation->getMaintenanceStatus()->getMonthsCompleted(),
            'total_income_generated' => $allocation->getTotalIncomeGenerated(),
            'monthly_income_average' => $allocation->getMonthlyIncomeAverage(),
        ];

        if ($allocation->getId()->value()) {
            PhysicalRewardAllocationModel::where('id', $allocation->getId()->value())->update($data);
        } else {
            PhysicalRewardAllocationModel::create($data);
        }
    }

    public function delete(RewardAllocationId $id): void
    {
        PhysicalRewardAllocationModel::where('id', $id->value())->delete();
    }

    public function getStatistics(): array
    {
        $totalAllocations = PhysicalRewardAllocationModel::count();
        
        $statusStats = PhysicalRewardAllocationModel::selectRaw('
            status,
            COUNT(*) as count,
            AVG(team_volume_at_allocation) as avg_team_volume,
            AVG(active_referrals_at_allocation) as avg_referrals
        ')
        ->groupBy('status')
        ->get();

        $incomeStats = PhysicalRewardAllocationModel::selectRaw('
            SUM(total_income_generated) as total_income,
            AVG(monthly_income_average) as avg_monthly_income,
            COUNT(CASE WHEN total_income_generated > 0 THEN 1 END) as income_generating_count
        ')
        ->first();

        $maintenanceStats = PhysicalRewardAllocationModel::selectRaw('
            AVG(maintenance_months_completed) as avg_maintenance_months,
            COUNT(CASE WHEN maintenance_compliant = 1 THEN 1 END) as compliant_count,
            COUNT(CASE WHEN maintenance_compliant = 0 THEN 1 END) as non_compliant_count
        ')
        ->where('status', 'delivered')
        ->first();

        $rewardTypeStats = PhysicalRewardAllocationModel::join('physical_rewards', 'physical_reward_allocations.physical_reward_id', '=', 'physical_rewards.id')
            ->selectRaw('
                physical_rewards.category,
                COUNT(*) as allocation_count,
                AVG(physical_reward_allocations.total_income_generated) as avg_income_per_category
            ')
            ->groupBy('physical_rewards.category')
            ->get();

        return [
            'total_allocations' => $totalAllocations,
            'by_status' => $statusStats->mapWithKeys(function ($stat) {
                return [$stat->status => [
                    'count' => $stat->count,
                    'percentage' => $totalAllocations > 0 ? ($stat->count / $totalAllocations) * 100 : 0,
                    'avg_team_volume' => (float) $stat->avg_team_volume,
                    'avg_referrals' => (float) $stat->avg_referrals,
                ]];
            })->toArray(),
            'income_metrics' => [
                'total_income_generated' => (float) ($incomeStats->total_income ?? 0),
                'avg_monthly_income' => (float) ($incomeStats->avg_monthly_income ?? 0),
                'income_generating_allocations' => $incomeStats->income_generating_count ?? 0,
                'income_generation_rate' => $totalAllocations > 0 
                    ? (($incomeStats->income_generating_count ?? 0) / $totalAllocations) * 100 
                    : 0,
            ],
            'maintenance_metrics' => [
                'avg_maintenance_months' => (float) ($maintenanceStats->avg_maintenance_months ?? 0),
                'compliant_count' => $maintenanceStats->compliant_count ?? 0,
                'non_compliant_count' => $maintenanceStats->non_compliant_count ?? 0,
                'compliance_rate' => ($maintenanceStats->compliant_count + $maintenanceStats->non_compliant_count) > 0
                    ? (($maintenanceStats->compliant_count ?? 0) / (($maintenanceStats->compliant_count ?? 0) + ($maintenanceStats->non_compliant_count ?? 0))) * 100
                    : 0,
            ],
            'by_reward_type' => $rewardTypeStats->mapWithKeys(function ($stat) {
                return [$stat->category => [
                    'allocation_count' => $stat->allocation_count,
                    'avg_income_per_category' => (float) $stat->avg_income_per_category,
                ]];
            })->toArray(),
        ];
    }

    public function getUserIncomeReport(UserId $userId): array
    {
        $allocations = PhysicalRewardAllocationModel::where('user_id', $userId->value())
            ->with('physicalReward')
            ->get();

        $totalIncomeGenerated = $allocations->sum('total_income_generated');
        $avgMonthlyIncome = $allocations->where('monthly_income_average', '>', 0)->avg('monthly_income_average');
        
        $incomeByAssetType = $allocations->groupBy('physicalReward.category')
            ->map(function ($group, $category) {
                return [
                    'category' => $category,
                    'total_income' => $group->sum('total_income_generated'),
                    'avg_monthly_income' => $group->avg('monthly_income_average'),
                    'asset_count' => $group->count(),
                ];
            })->values();

        $monthlyIncomeHistory = $allocations->filter(function ($allocation) {
            return $allocation->income_tracking_started && $allocation->total_income_generated > 0;
        })->map(function ($allocation) {
            $monthsSinceStart = $allocation->income_tracking_started 
                ? now()->diffInMonths($allocation->income_tracking_started) + 1 
                : 1;
            
            return [
                'asset_name' => $allocation->physicalReward->name,
                'asset_category' => $allocation->physicalReward->category,
                'months_generating' => $monthsSinceStart,
                'total_income' => $allocation->total_income_generated,
                'monthly_average' => $allocation->monthly_income_average,
                'estimated_monthly' => $allocation->physicalReward->estimated_monthly_income,
                'performance_vs_estimate' => $allocation->physicalReward->estimated_monthly_income > 0
                    ? ($allocation->monthly_income_average / $allocation->physicalReward->estimated_monthly_income) * 100
                    : 0,
            ];
        });

        return [
            'user_id' => $userId->value(),
            'total_allocations' => $allocations->count(),
            'income_generating_allocations' => $allocations->where('total_income_generated', '>', 0)->count(),
            'total_income_generated' => (float) $totalIncomeGenerated,
            'avg_monthly_income' => (float) $avgMonthlyIncome,
            'income_by_asset_type' => $incomeByAssetType->toArray(),
            'asset_performance' => $monthlyIncomeHistory->toArray(),
            'roi_analysis' => [
                'total_asset_value' => $allocations->sum(function ($allocation) {
                    return $allocation->physicalReward->estimated_value;
                }),
                'total_income_generated' => (float) $totalIncomeGenerated,
                'overall_roi_percentage' => $allocations->sum(function ($allocation) {
                    return $allocation->physicalReward->estimated_value;
                }) > 0 ? ($totalIncomeGenerated / $allocations->sum(function ($allocation) {
                    return $allocation->physicalReward->estimated_value;
                })) * 100 : 0,
            ]
        ];
    }

    private function toDomainEntity(PhysicalRewardAllocationModel $model): PhysicalRewardAllocation
    {
        $allocation = PhysicalRewardAllocation::allocate(
            RewardAllocationId::fromInt($model->id),
            UserId::fromInt($model->user_id),
            RewardId::fromInt($model->physical_reward_id),
            TeamVolumeAmount::fromFloat($model->team_volume_at_allocation),
            $model->active_referrals_at_allocation,
            $model->team_depth_at_allocation ?? 0
        );

        // Update status if not allocated
        if ($model->status !== 'allocated') {
            $reflection = new \ReflectionClass($allocation);
            $statusProperty = $reflection->getProperty('status');
            $statusProperty->setAccessible(true);
            $statusProperty->setValue($allocation, AllocationStatus::fromString($model->status));

            if ($model->delivered_at) {
                $deliveredAtProperty = $reflection->getProperty('deliveredAt');
                $deliveredAtProperty->setAccessible(true);
                $deliveredAtProperty->setValue($allocation, new DateTimeImmutable($model->delivered_at));
            }

            if ($model->ownership_transferred_at) {
                $ownershipProperty = $reflection->getProperty('ownershipTransferredAt');
                $ownershipProperty->setAccessible(true);
                $ownershipProperty->setValue($allocation, new DateTimeImmutable($model->ownership_transferred_at));
            }
        }

        // Update maintenance status
        $allocation->updateMaintenanceStatus(
            $model->maintenance_compliant ?? true,
            $model->maintenance_months_completed ?? 0
        );

        // Update income if any
        if ($model->total_income_generated > 0) {
            $allocation->recordIncomeGenerated($model->total_income_generated);
        }

        return $allocation;
    }
}