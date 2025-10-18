<?php

namespace App\Services;

use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\TeamVolume;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation;
use App\Domain\Reward\Services\PhysicalRewardAllocationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RetroactiveAssetAllocationService
{
    private PhysicalRewardAllocationService $allocationService;

    // Asset eligibility criteria based on MyGrowNet requirements
    private const ASSET_ELIGIBILITY_CRITERIA = [
        'Bronze' => [
            'min_months' => 1,
            'min_team_volume' => 0,
            'min_active_referrals' => 1,
            'asset_types' => ['STARTER_KIT']
        ],
        'Silver' => [
            'min_months' => 3,
            'min_team_volume' => 15000,
            'min_active_referrals' => 3,
            'asset_types' => ['SMARTPHONE', 'TABLET']
        ],
        'Gold' => [
            'min_months' => 6,
            'min_team_volume' => 50000,
            'min_active_referrals' => 10,
            'asset_types' => ['MOTORBIKE', 'OFFICE_EQUIPMENT']
        ],
        'Diamond' => [
            'min_months' => 9,
            'min_team_volume' => 150000,
            'min_active_referrals' => 25,
            'asset_types' => ['CAR', 'PROPERTY_DOWN_PAYMENT']
        ],
        'Elite' => [
            'min_months' => 12,
            'min_team_volume' => 500000,
            'min_active_referrals' => 50,
            'asset_types' => ['LUXURY_CAR', 'PROPERTY_INVESTMENT']
        ]
    ];

    public function __construct(PhysicalRewardAllocationService $allocationService)
    {
        $this->allocationService = $allocationService;
    }

    /**
     * Allocate assets to qualifying existing users based on historical performance
     */
    public function allocateRetroactiveAssets(bool $dryRun = false): array
    {
        $results = [
            'users_evaluated' => 0,
            'users_qualified' => 0,
            'assets_allocated' => 0,
            'maintenance_schedules_created' => 0,
            'allocations_by_tier' => [],
            'errors' => []
        ];

        try {
            if (!$dryRun) {
                DB::beginTransaction();
            }

            // Step 1: Identify high-tier users eligible for retroactive rewards
            $eligibleUsers = $this->identifyEligibleUsers();
            $results['users_evaluated'] = count($eligibleUsers);

            // Step 2: Evaluate each user's historical performance
            foreach ($eligibleUsers as $userEligibility) {
                try {
                    $allocationResult = $this->processUserAssetAllocation($userEligibility, $dryRun);
                    
                    if ($allocationResult['qualified']) {
                        $results['users_qualified']++;
                        $results['assets_allocated'] += $allocationResult['assets_allocated'];
                        $results['maintenance_schedules_created'] += $allocationResult['maintenance_schedules'];
                        
                        $tierName = $userEligibility['tier']->name;
                        if (!isset($results['allocations_by_tier'][$tierName])) {
                            $results['allocations_by_tier'][$tierName] = 0;
                        }
                        $results['allocations_by_tier'][$tierName] += $allocationResult['assets_allocated'];
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "User {$userEligibility['user']->id}: " . $e->getMessage();
                    Log::error('Asset allocation error for user', [
                        'user_id' => $userEligibility['user']->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if (!$dryRun) {
                DB::commit();
            }

            Log::info('Retroactive asset allocation completed', $results);

        } catch (\Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            
            $results['errors'][] = $e->getMessage();
            Log::error('Retroactive asset allocation failed', [
                'error' => $e->getMessage(),
                'results' => $results
            ]);
            
            throw $e;
        }

        return $results;
    }

    /**
     * Identify existing high-tier users eligible for retroactive asset rewards
     */
    public function identifyEligibleUsers(): array
    {
        $eligibleUsers = [];

        // Get users with Silver tier and above (asset-eligible tiers)
        $highTierUsers = User::whereHas('membershipTier', function ($query) {
            $query->whereIn('name', ['Silver', 'Gold', 'Diamond', 'Elite']);
        })
        ->with(['membershipTier', 'teamVolumes'])
        ->get();

        foreach ($highTierUsers as $user) {
            $tier = $user->membershipTier;
            if (!$tier) continue;

            $eligibility = $this->evaluateUserEligibility($user, $tier);
            
            if ($eligibility['eligible']) {
                $eligibleUsers[] = [
                    'user' => $user,
                    'tier' => $tier,
                    'eligibility' => $eligibility,
                    'historical_performance' => $this->calculateHistoricalPerformance($user)
                ];
            }
        }

        return $eligibleUsers;
    }

    /**
     * Evaluate if a user meets eligibility criteria for their tier
     */
    private function evaluateUserEligibility(User $user, InvestmentTier $tier): array
    {
        $criteria = self::ASSET_ELIGIBILITY_CRITERIA[$tier->name] ?? null;
        if (!$criteria) {
            return ['eligible' => false, 'reason' => 'No criteria defined for tier'];
        }

        // Calculate months at current tier
        $monthsAtTier = $this->calculateMonthsAtTier($user, $tier);
        
        // Get current team volume and active referrals
        $currentVolume = $user->getCurrentTeamVolume();
        $teamVolume = $currentVolume?->team_volume ?? 0;
        $activeReferrals = $currentVolume?->active_referrals_count ?? 0;

        // Check eligibility criteria
        $meetsMonths = $monthsAtTier >= $criteria['min_months'];
        $meetsVolume = $teamVolume >= $criteria['min_team_volume'];
        $meetsReferrals = $activeReferrals >= $criteria['min_active_referrals'];

        $eligible = $meetsMonths && $meetsVolume && $meetsReferrals;

        return [
            'eligible' => $eligible,
            'months_at_tier' => $monthsAtTier,
            'team_volume' => $teamVolume,
            'active_referrals' => $activeReferrals,
            'criteria' => $criteria,
            'meets_months' => $meetsMonths,
            'meets_volume' => $meetsVolume,
            'meets_referrals' => $meetsReferrals,
            'reason' => $eligible ? 'Meets all criteria' : $this->getIneligibilityReason($meetsMonths, $meetsVolume, $meetsReferrals)
        ];
    }

    /**
     * Calculate how many months a user has been at their current tier
     */
    private function calculateMonthsAtTier(User $user, InvestmentTier $tier): int
    {
        // Check tier history for when user reached this tier
        $tierHistory = $user->getTierHistory();
        
        $tierUpgradeDate = null;
        foreach (array_reverse($tierHistory) as $historyEntry) {
            if ($historyEntry['tier_id'] == $tier->id) {
                $tierUpgradeDate = Carbon::parse($historyEntry['date']);
                break;
            }
        }

        // If no history found, use tier_upgraded_at or account creation
        if (!$tierUpgradeDate) {
            $tierUpgradeDate = $user->tier_upgraded_at ?? $user->created_at;
        }

        return $tierUpgradeDate->diffInMonths(now());
    }

    /**
     * Calculate historical performance metrics for a user
     */
    private function calculateHistoricalPerformance(User $user): array
    {
        // Get historical team volumes
        $teamVolumes = TeamVolume::where('user_id', $user->id)
            ->orderBy('period_start', 'desc')
            ->limit(12) // Last 12 months
            ->get();

        $avgTeamVolume = $teamVolumes->avg('team_volume') ?? 0;
        $maxTeamVolume = $teamVolumes->max('team_volume') ?? 0;
        $avgActiveReferrals = $teamVolumes->avg('active_referrals_count') ?? 0;
        $consistentMonths = $teamVolumes->where('team_volume', '>', 0)->count();

        // Calculate total commissions earned
        $totalCommissions = $user->referralCommissions()
            ->where('status', 'paid')
            ->sum('amount') ?? 0;

        return [
            'avg_team_volume' => $avgTeamVolume,
            'max_team_volume' => $maxTeamVolume,
            'avg_active_referrals' => $avgActiveReferrals,
            'consistent_months' => $consistentMonths,
            'total_commissions' => $totalCommissions,
            'performance_score' => $this->calculatePerformanceScore($avgTeamVolume, $consistentMonths, $totalCommissions)
        ];
    }

    /**
     * Calculate a performance score for prioritizing asset allocation
     */
    private function calculatePerformanceScore(float $avgVolume, int $consistentMonths, float $totalCommissions): float
    {
        // Weighted score based on different performance metrics
        $volumeScore = min($avgVolume / 100000, 1) * 40; // Max 40 points for volume
        $consistencyScore = min($consistentMonths / 12, 1) * 30; // Max 30 points for consistency
        $commissionScore = min($totalCommissions / 50000, 1) * 30; // Max 30 points for commissions

        return $volumeScore + $consistencyScore + $commissionScore;
    }

    /**
     * Process asset allocation for a specific user
     */
    private function processUserAssetAllocation(array $userEligibility, bool $dryRun): array
    {
        $user = $userEligibility['user'];
        $tier = $userEligibility['tier'];
        $eligibility = $userEligibility['eligibility'];

        if (!$eligibility['eligible']) {
            return [
                'qualified' => false,
                'assets_allocated' => 0,
                'maintenance_schedules' => 0,
                'reason' => $eligibility['reason']
            ];
        }

        $assetsAllocated = 0;
        $maintenanceSchedules = 0;

        // Check if user already has assets allocated for this tier
        $existingAllocations = PhysicalRewardAllocation::where('user_id', $user->id)
            ->where('tier_id', $tier->id)
            ->exists();

        if ($existingAllocations) {
            return [
                'qualified' => false,
                'assets_allocated' => 0,
                'maintenance_schedules' => 0,
                'reason' => 'Already has asset allocations for this tier'
            ];
        }

        // Get available assets for this tier
        $criteria = self::ASSET_ELIGIBILITY_CRITERIA[$tier->name];
        $availableAssets = $this->getAvailableAssetsForTier($criteria['asset_types']);

        foreach ($availableAssets as $asset) {
            if (!$dryRun) {
                // Create asset allocation
                $allocation = PhysicalRewardAllocation::create([
                    'user_id' => $user->id,
                    'physical_reward_id' => $asset->id,
                    'tier_id' => $tier->id,
                    'team_volume_at_allocation' => $eligibility['team_volume'],
                    'active_referrals_at_allocation' => $eligibility['active_referrals'],
                    'months_at_tier' => $eligibility['months_at_tier'],
                    'allocation_status' => 'allocated',
                    'maintenance_status' => 'pending',
                    'maintenance_period_months' => 12,
                    'allocated_at' => now(),
                    'maintenance_due_at' => now()->addMonths(12)
                ]);

                // Create maintenance schedule
                $this->createAssetMaintenanceSchedule($allocation);
                $maintenanceSchedules++;

                // Record activity
                $user->recordActivity(
                    'retroactive_asset_allocated',
                    "Retroactively allocated {$asset->type} ({$asset->model}) based on historical performance"
                );
            }

            $assetsAllocated++;
            
            // Limit to one asset per tier for retroactive allocation
            break;
        }

        return [
            'qualified' => true,
            'assets_allocated' => $assetsAllocated,
            'maintenance_schedules' => $maintenanceSchedules,
            'reason' => 'Successfully allocated based on historical performance'
        ];
    }

    /**
     * Get available assets for specific asset types
     */
    private function getAvailableAssetsForTier(array $assetTypes): array
    {
        return PhysicalReward::whereIn('type', $assetTypes)
            ->where('status', 'available')
            ->orderBy('value', 'asc') // Start with lower value assets
            ->limit(1) // One asset per tier for retroactive allocation
            ->get()
            ->toArray();
    }

    /**
     * Create maintenance schedule for allocated asset
     */
    private function createAssetMaintenanceSchedule(PhysicalRewardAllocation $allocation): void
    {
        // Create maintenance milestones
        $milestones = [
            ['months' => 3, 'type' => 'performance_review'],
            ['months' => 6, 'type' => 'mid_term_assessment'],
            ['months' => 9, 'type' => 'pre_transfer_evaluation'],
            ['months' => 12, 'type' => 'ownership_transfer']
        ];

        foreach ($milestones as $milestone) {
            DB::table('asset_maintenance_schedules')->insert([
                'allocation_id' => $allocation->id,
                'milestone_type' => $milestone['type'],
                'due_date' => $allocation->allocated_at->addMonths($milestone['months']),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Get ineligibility reason for user
     */
    private function getIneligibilityReason(bool $meetsMonths, bool $meetsVolume, bool $meetsReferrals): string
    {
        $reasons = [];
        
        if (!$meetsMonths) $reasons[] = 'insufficient months at tier';
        if (!$meetsVolume) $reasons[] = 'insufficient team volume';
        if (!$meetsReferrals) $reasons[] = 'insufficient active referrals';

        return 'Does not meet criteria: ' . implode(', ', $reasons);
    }

    /**
     * Get asset allocation statistics
     */
    public function getAssetAllocationStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'high_tier_users' => User::whereHas('membershipTier', function ($query) {
                $query->whereIn('name', ['Silver', 'Gold', 'Diamond', 'Elite']);
            })->count(),
            'users_with_allocations' => PhysicalRewardAllocation::distinct('user_id')->count(),
            'total_allocations' => PhysicalRewardAllocation::count(),
            'allocations_by_tier' => PhysicalRewardAllocation::join('investment_tiers', 'physical_reward_allocations.tier_id', '=', 'investment_tiers.id')
                ->selectRaw('investment_tiers.name as tier_name, COUNT(*) as count')
                ->groupBy('investment_tiers.name')
                ->pluck('count', 'tier_name')
                ->toArray(),
            'allocations_by_status' => PhysicalRewardAllocation::selectRaw('allocation_status, COUNT(*) as count')
                ->groupBy('allocation_status')
                ->pluck('count', 'allocation_status')
                ->toArray(),
            'maintenance_due_soon' => PhysicalRewardAllocation::where('maintenance_due_at', '<=', now()->addDays(30))
                ->where('maintenance_status', 'pending')
                ->count(),
            'available_assets' => PhysicalReward::where('status', 'available')->count(),
            'allocated_assets' => PhysicalReward::where('status', 'allocated')->count()
        ];
    }

    /**
     * Validate asset allocations
     */
    public function validateAssetAllocations(): array
    {
        $issues = [];

        // Check for users with allocations but no qualifying performance
        $invalidAllocations = PhysicalRewardAllocation::whereHas('user', function ($query) {
            $query->whereNull('current_team_volume')
                  ->orWhere('current_team_volume', 0);
        })->count();

        if ($invalidAllocations > 0) {
            $issues[] = "Found {$invalidAllocations} allocations for users with no team volume";
        }

        // Check for overdue maintenance
        $overdueMaintenanceCount = PhysicalRewardAllocation::where('maintenance_due_at', '<', now())
            ->where('maintenance_status', 'pending')
            ->count();

        if ($overdueMaintenanceCount > 0) {
            $issues[] = "Found {$overdueMaintenanceCount} allocations with overdue maintenance";
        }

        // Check for duplicate allocations
        $duplicateAllocations = DB::table('physical_reward_allocations')
            ->select('user_id', 'tier_id')
            ->groupBy('user_id', 'tier_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($duplicateAllocations > 0) {
            $issues[] = "Found {$duplicateAllocations} users with duplicate allocations for the same tier";
        }

        return [
            'is_valid' => empty($issues),
            'issues' => $issues,
            'validation_timestamp' => now()
        ];
    }

    /**
     * Process maintenance schedules for existing allocations
     */
    public function processMaintenanceSchedules(bool $dryRun = false): array
    {
        $processed = 0;
        $completed = 0;

        $dueMaintenanceItems = DB::table('asset_maintenance_schedules')
            ->where('due_date', '<=', now())
            ->where('status', 'pending')
            ->get();

        foreach ($dueMaintenanceItems as $item) {
            if (!$dryRun) {
                $allocation = PhysicalRewardAllocation::find($item->allocation_id);
                if ($allocation) {
                    // Process based on milestone type
                    $this->procesMaintenanceMilestone($allocation, $item);
                    $completed++;
                }
            }
            $processed++;
        }

        return [
            'processed' => $processed,
            'completed' => $completed
        ];
    }

    /**
     * Process a specific maintenance milestone
     */
    private function procesMaintenanceMilestone(PhysicalRewardAllocation $allocation, $milestone): void
    {
        switch ($milestone->milestone_type) {
            case 'performance_review':
                $this->performPerformanceReview($allocation);
                break;
            case 'mid_term_assessment':
                $this->performMidTermAssessment($allocation);
                break;
            case 'pre_transfer_evaluation':
                $this->performPreTransferEvaluation($allocation);
                break;
            case 'ownership_transfer':
                $this->processOwnershipTransfer($allocation);
                break;
        }

        // Mark milestone as completed
        DB::table('asset_maintenance_schedules')
            ->where('id', $milestone->id)
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * Perform performance review for asset allocation
     */
    private function performPerformanceReview(PhysicalRewardAllocation $allocation): void
    {
        $user = $allocation->user;
        $currentVolume = $user->getCurrentTeamVolume();
        
        // Check if user still meets performance criteria
        $stillQualified = $currentVolume && 
                         $currentVolume->team_volume >= $allocation->team_volume_at_allocation * 0.8; // 80% threshold

        if ($stillQualified) {
            $allocation->update(['maintenance_status' => 'on_track']);
        } else {
            $allocation->update(['maintenance_status' => 'at_risk']);
            // Send warning notification
        }
    }

    /**
     * Perform mid-term assessment
     */
    private function performMidTermAssessment(PhysicalRewardAllocation $allocation): void
    {
        // Similar to performance review but with stricter criteria
        $this->performPerformanceReview($allocation);
    }

    /**
     * Perform pre-transfer evaluation
     */
    private function performPreTransferEvaluation(PhysicalRewardAllocation $allocation): void
    {
        $user = $allocation->user;
        $currentVolume = $user->getCurrentTeamVolume();
        
        // Final check before ownership transfer
        $qualifiedForTransfer = $currentVolume && 
                               $currentVolume->team_volume >= $allocation->team_volume_at_allocation;

        if ($qualifiedForTransfer) {
            $allocation->update(['maintenance_status' => 'transfer_ready']);
        } else {
            $allocation->update(['maintenance_status' => 'transfer_denied']);
        }
    }

    /**
     * Process ownership transfer
     */
    private function processOwnershipTransfer(PhysicalRewardAllocation $allocation): void
    {
        if ($allocation->maintenance_status === 'transfer_ready') {
            $allocation->update([
                'allocation_status' => 'transferred',
                'maintenance_status' => 'completed',
                'transferred_at' => now()
            ]);

            // Update physical reward status
            $allocation->physicalReward->update(['status' => 'transferred']);

            // Record activity
            $allocation->user->recordActivity(
                'asset_ownership_transferred',
                "Ownership of {$allocation->physicalReward->type} transferred after successful maintenance period"
            );
        }
    }
}