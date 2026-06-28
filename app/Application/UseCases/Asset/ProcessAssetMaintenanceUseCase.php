<?php

namespace App\Application\UseCases\Asset;

use App\Models\PhysicalRewardAllocation;
use App\Models\User;
use App\Domain\Reward\ValueObjects\MaintenanceStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessAssetMaintenanceUseCase
{
    public function execute(?int $allocationId = null): array
    {
        $query = PhysicalRewardAllocation::with(['user', 'physicalReward'])
            ->whereIn('status', ['pending', 'active']);

        if ($allocationId) {
            $query->where('id', $allocationId);
        }

        $allocations = $query->get();
        $results = [
            'processed' => 0,
            'maintained' => 0,
            'violated' => 0,
            'completed' => 0,
            'forfeited' => 0,
            'details' => []
        ];

        foreach ($allocations as $allocation) {
            try {
                $result = $this->processAllocationMaintenance($allocation);
                $results['processed']++;
                $results[$result['action']]++;
                $results['details'][] = $result;
            } catch (\Exception $e) {
                Log::error("Asset maintenance processing failed", [
                    'allocation_id' => $allocation->id,
                    'user_id' => $allocation->user_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    private function processAllocationMaintenance(PhysicalRewardAllocation $allocation): array
    {
        $user = $allocation->user;
        $asset = $allocation->physicalReward;
        
        // Check if maintenance period is complete
        if ($this->isMaintenancePeriodComplete($allocation)) {
            return $this->completeAssetOwnership($allocation);
        }

        // Check maintenance requirements
        $maintenanceCheck = $this->checkMaintenanceRequirements($allocation);
        
        if ($maintenanceCheck['meets_requirements']) {
            return $this->maintainAssetAllocation($allocation, $maintenanceCheck);
        } else {
            return $this->handleMaintenanceViolation($allocation, $maintenanceCheck);
        }
    }

    private function isMaintenancePeriodComplete(PhysicalRewardAllocation $allocation): bool
    {
        $monthsElapsed = $allocation->allocated_at->diffInMonths(now());
        return $monthsElapsed >= $allocation->maintenance_period_months;
    }

    private function checkMaintenanceRequirements(PhysicalRewardAllocation $allocation): array
    {
        $user = $allocation->user;
        $asset = $allocation->physicalReward;
        
        // Get current user stats
        $currentReferrals = $user->activeReferrals()->count();
        $currentTeamVolume = $user->monthly_team_volume ?? 0;
        $currentTier = $user->investmentTier;

        // Get original requirements
        $requirements = $this->getAssetMaintenanceRequirements($asset->type);
        
        $meetsReferrals = $currentReferrals >= $requirements['min_referrals'];
        $meetsTeamVolume = $currentTeamVolume >= $requirements['min_team_volume'];
        $maintainsTier = $currentTier && $currentTier->name === $requirements['tier'];

        return [
            'meets_requirements' => $meetsReferrals && $meetsTeamVolume && $maintainsTier,
            'current_referrals' => $currentReferrals,
            'required_referrals' => $requirements['min_referrals'],
            'current_team_volume' => $currentTeamVolume,
            'required_team_volume' => $requirements['min_team_volume'],
            'current_tier' => $currentTier?->name,
            'required_tier' => $requirements['tier'],
            'meets_referrals' => $meetsReferrals,
            'meets_team_volume' => $meetsTeamVolume,
            'maintains_tier' => $maintainsTier
        ];
    }

    private function getAssetMaintenanceRequirements(string $assetType): array
    {
        return match ($assetType) {
            'STARTER_KIT' => [
                'tier' => 'Bronze Member',
                'min_referrals' => 1,
                'min_team_volume' => 0
            ],
            'SMARTPHONE', 'TABLET' => [
                'tier' => 'Silver Member',
                'min_referrals' => 3,
                'min_team_volume' => 15000
            ],
            'MOTORBIKE' => [
                'tier' => 'Gold Member',
                'min_referrals' => 10,
                'min_team_volume' => 50000
            ],
            'CAR' => [
                'tier' => 'Diamond Member',
                'min_referrals' => 25,
                'min_team_volume' => 150000
            ],
            'PROPERTY' => [
                'tier' => 'Elite Member',
                'min_referrals' => 50,
                'min_team_volume' => 500000
            ],
            default => [
                'tier' => 'Bronze Member',
                'min_referrals' => 0,
                'min_team_volume' => 0
            ]
        };
    }

    private function completeAssetOwnership(PhysicalRewardAllocation $allocation): array
    {
        DB::transaction(function () use ($allocation) {
            $allocation->update([
                'status' => 'completed',
                'completed_at' => now(),
                'maintenance_status' => 'completed'
            ]);

            $allocation->physicalReward->update([
                'status' => 'transferred',
                'owner_id' => $allocation->user_id
            ]);
        });

        Log::info("Asset ownership completed", [
            'allocation_id' => $allocation->id,
            'user_id' => $allocation->user_id,
            'asset_type' => $allocation->physicalReward->type
        ]);

        return [
            'action' => 'completed',
            'allocation_id' => $allocation->id,
            'user_id' => $allocation->user_id,
            'asset_type' => $allocation->physicalReward->type,
            'message' => 'Asset ownership transferred successfully'
        ];
    }

    private function maintainAssetAllocation(PhysicalRewardAllocation $allocation, array $maintenanceCheck): array
    {
        $allocation->update([
            'status' => 'active',
            'maintenance_status' => 'maintained',
            'last_maintenance_check' => now()
        ]);

        return [
            'action' => 'maintained',
            'allocation_id' => $allocation->id,
            'user_id' => $allocation->user_id,
            'asset_type' => $allocation->physicalReward->type,
            'maintenance_check' => $maintenanceCheck,
            'message' => 'Asset maintenance requirements met'
        ];
    }

    private function handleMaintenanceViolation(PhysicalRewardAllocation $allocation, array $maintenanceCheck): array
    {
        // Check if this is a repeated violation
        $violationCount = $this->getViolationCount($allocation);
        
        if ($violationCount >= 2) {
            // Forfeit asset after multiple violations
            return $this->forfeitAsset($allocation, $maintenanceCheck);
        } else {
            // Record violation and give warning
            return $this->recordMaintenanceViolation($allocation, $maintenanceCheck);
        }
    }

    private function getViolationCount(PhysicalRewardAllocation $allocation): int
    {
        // Count maintenance violations in the last 3 months
        return PhysicalRewardAllocation::where('id', $allocation->id)
            ->where('maintenance_status', 'violated')
            ->where('last_maintenance_check', '>=', now()->subMonths(3))
            ->count();
    }

    private function recordMaintenanceViolation(PhysicalRewardAllocation $allocation, array $maintenanceCheck): array
    {
        $allocation->update([
            'maintenance_status' => 'violated',
            'last_maintenance_check' => now(),
            'violation_details' => json_encode($maintenanceCheck)
        ]);

        Log::warning("Asset maintenance violation", [
            'allocation_id' => $allocation->id,
            'user_id' => $allocation->user_id,
            'asset_type' => $allocation->physicalReward->type,
            'violation_details' => $maintenanceCheck
        ]);

        return [
            'action' => 'violated',
            'allocation_id' => $allocation->id,
            'user_id' => $allocation->user_id,
            'asset_type' => $allocation->physicalReward->type,
            'maintenance_check' => $maintenanceCheck,
            'message' => 'Asset maintenance requirements not met - warning issued'
        ];
    }

    private function forfeitAsset(PhysicalRewardAllocation $allocation, array $maintenanceCheck): array
    {
        DB::transaction(function () use ($allocation) {
            $allocation->update([
                'status' => 'forfeited',
                'maintenance_status' => 'forfeited',
                'forfeited_at' => now(),
                'last_maintenance_check' => now()
            ]);

            $allocation->physicalReward->update([
                'status' => 'available',
                'owner_id' => null
            ]);
        });

        Log::warning("Asset forfeited due to maintenance violations", [
            'allocation_id' => $allocation->id,
            'user_id' => $allocation->user_id,
            'asset_type' => $allocation->physicalReward->type
        ]);

        return [
            'action' => 'forfeited',
            'allocation_id' => $allocation->id,
            'user_id' => $allocation->user_id,
            'asset_type' => $allocation->physicalReward->type,
            'maintenance_check' => $maintenanceCheck,
            'message' => 'Asset forfeited due to repeated maintenance violations'
        ];
    }

    public function getAssetMaintenanceStatus(int $userId): array
    {
        $allocations = PhysicalRewardAllocation::with(['physicalReward'])
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'active', 'completed'])
            ->get();

        $status = [];
        
        foreach ($allocations as $allocation) {
            $maintenanceCheck = $this->checkMaintenanceRequirements($allocation);
            $monthsRemaining = max(0, $allocation->maintenance_period_months - $allocation->allocated_at->diffInMonths(now()));
            
            $status[] = [
                'allocation_id' => $allocation->id,
                'asset_type' => $allocation->physicalReward->type,
                'asset_value' => $allocation->physicalReward->value,
                'status' => $allocation->status,
                'maintenance_status' => $allocation->maintenance_status,
                'months_remaining' => $monthsRemaining,
                'maintenance_check' => $maintenanceCheck,
                'allocated_at' => $allocation->allocated_at,
                'completed_at' => $allocation->completed_at
            ];
        }

        return $status;
    }
}