<?php

namespace App\Services;

use App\Models\User;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation;
use App\Models\InvestmentTier;
use App\Models\TeamVolume;
use App\Models\TierQualification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PhysicalRewardAllocationService
{

    /**
     * Check and allocate rewards for a user based on their current performance
     */
    public function checkAndAllocateRewards(User $user): Collection
    {
        $allocatedRewards = collect();
        
        // Get user's current tier and performance metrics
        $currentTier = $user->membershipTier;
        if (!$currentTier) {
            return $allocatedRewards;
        }

        $teamVolume = $user->getCurrentTeamVolume();
        if (!$teamVolume) {
            return $allocatedRewards;
        }

        // Get eligible rewards for user's tier
        $eligibleRewards = PhysicalReward::active()
            ->available()
            ->forTier($currentTier->name)
            ->get();

        foreach ($eligibleRewards as $reward) {
            if ($this->isUserEligibleForReward($user, $reward)) {
                // Check if user already has this reward
                $existingAllocation = PhysicalRewardAllocation::where('user_id', $user->id)
                    ->where('physical_reward_id', $reward->id)
                    ->whereIn('status', ['allocated', 'delivered', 'ownership_transferred'])
                    ->first();

                if (!$existingAllocation) {
                    $allocation = $this->allocateRewardToUser($user, $reward);
                    if ($allocation) {
                        $allocatedRewards->push($allocation);
                    }
                }
            }
        }

        return $allocatedRewards;
    }

    /**
     * Check if user is eligible for a specific reward
     */
    public function isUserEligibleForReward(User $user, PhysicalReward $reward): bool
    {
        $currentTier = $user->membershipTier;
        if (!$currentTier) {
            return false;
        }

        // Check tier eligibility
        if (!in_array($currentTier->name, $reward->required_membership_tiers)) {
            return false;
        }

        // Get team volume with optimized query to avoid memory issues
        $teamVolume = TeamVolume::where('user_id', $user->id)
            ->whereBetween('period_start', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->first();
            
        if (!$teamVolume) {
            return false;
        }

        // Check team volume requirements (if column exists)
        if (isset($reward->required_team_volume) && $teamVolume->team_volume < $reward->required_team_volume) {
            return false;
        }

        // Check referral requirements
        if ($teamVolume->active_referrals_count < $reward->required_referrals) {
            return false;
        }

        // Check team depth requirements (if column exists)
        if (isset($reward->required_team_depth) && $teamVolume->team_depth < $reward->required_team_depth) {
            return false;
        }

        // Check subscription amount requirements (if column exists)
        $monthlyFee = ($currentTier->monthly_fee > 0) ? $currentTier->monthly_fee : ($currentTier->minimum_investment ?? 0);
        if ($monthlyFee < $reward->required_subscription_amount) {
            return false;
        }

        // Check sustained months requirement
        if ($reward->required_sustained_months > 0) {
            $consecutiveMonths = $this->getConsecutiveMonthsAtTier($user, $currentTier->id);
            
            if ($consecutiveMonths < $reward->required_sustained_months) {
                return false;
            }
        }

        return true;
    }

    /**
     * Allocate a reward to a user
     */
    public function allocateRewardToUser(User $user, PhysicalReward $reward): ?PhysicalRewardAllocation
    {
        if (!$reward->isAvailable() || !$this->isUserEligibleForReward($user, $reward)) {
            return null;
        }

        try {
            DB::beginTransaction();

            $teamVolume = $user->getCurrentTeamVolume();
            
            $allocation = PhysicalRewardAllocation::create([
                'user_id' => $user->id,
                'physical_reward_id' => $reward->id,
                'tier_id' => $user->current_investment_tier_id,
                'team_volume_at_allocation' => $teamVolume->team_volume ?? 0,
                'active_referrals_at_allocation' => $teamVolume->active_referrals_count ?? 0,
                'team_depth_at_allocation' => $teamVolume->team_depth ?? 0,
                'status' => 'allocated',
                'allocated_at' => now(),
                'maintenance_compliant' => true
            ]);

            // Update reward allocated quantity
            $reward->increment('allocated_quantity');

            // Record user activity (avoid loading membershipTier again)
            $tierName = $user->membershipTier?->name ?? 'Unknown';
            $user->recordActivity(
                'physical_reward_allocated',
                "Physical reward '{$reward->name}' allocated for achieving {$tierName} tier performance"
            );

            DB::commit();

            Log::info("Physical reward allocated", [
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'reward_name' => $reward->name,
                'tier' => $tierName,
                'team_volume' => $teamVolume->team_volume ?? 0
            ]);

            return $allocation;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to allocate physical reward", [
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Process maintenance checks for all active allocations
     */
    public function processMaintenanceChecks(): array
    {
        $results = [
            'checked' => 0,
            'compliant' => 0,
            'violations' => 0,
            'forfeited' => 0
        ];

        // Process allocations in chunks to avoid memory issues
        PhysicalRewardAllocation::with(['physicalReward'])
            ->where('status', 'delivered')
            ->whereHas('physicalReward', function ($query) {
                $query->where('requires_performance_maintenance', true);
            })
            ->where(function ($query) {
                $query->whereNull('last_maintenance_check')
                      ->orWhere('last_maintenance_check', '<', now()->subMonth());
            })
            ->chunk(25, function ($allocations) use (&$results) {
                foreach ($allocations as $allocation) {
                    $results['checked']++;
                    
                    $isCompliant = $this->checkMaintenanceCompliance($allocation);
                    
                    if ($isCompliant) {
                        $results['compliant']++;
                        $allocation->updateMaintenanceStatus(true, 'Performance requirements met');
                        
                        // Check if eligible for ownership transfer
                        if ($allocation->isEligibleForOwnershipTransfer()) {
                            $allocation->transferOwnership();
                        }
                    } else {
                        $results['violations']++;
                        $allocation->updateMaintenanceStatus(false, 'Performance requirements not met');
                        
                        // Check if should be forfeited (multiple consecutive violations)
                        if ($this->shouldForfeitAllocation($allocation)) {
                            $allocation->forfeit('Multiple consecutive performance violations');
                            $results['forfeited']++;
                        }
                    }
                }
                
                // Force garbage collection after each chunk
                gc_collect_cycles();
            });

        return $results;
    }

    /**
     * Check if allocation meets maintenance compliance requirements
     */
    private function checkMaintenanceCompliance(PhysicalRewardAllocation $allocation): bool
    {
        // Load user with only necessary relationships to avoid memory issues
        $user = User::with(['membershipTier'])
            ->find($allocation->user_id);
            
        if (!$user) {
            return false;
        }
        
        $reward = $allocation->physicalReward;
        
        // Check if user still meets the reward requirements
        return $this->isUserEligibleForReward($user, $reward);
    }

    /**
     * Determine if allocation should be forfeited
     */
    private function shouldForfeitAllocation(PhysicalRewardAllocation $allocation): bool
    {
        // Forfeit if user has been non-compliant for 3+ consecutive months
        return !$allocation->maintenance_compliant && 
               $allocation->maintenance_months_completed == 0 &&
               $allocation->last_maintenance_check &&
               $allocation->last_maintenance_check->diffInMonths(now()) >= 3;
    }

    /**
     * Get available rewards for a user
     */
    public function getAvailableRewardsForUser(User $user): Collection
    {
        $currentTier = $user->membershipTier;
        if (!$currentTier) {
            return collect();
        }

        return PhysicalReward::active()
            ->available()
            ->forTier($currentTier->name)
            ->get()
            ->filter(function ($reward) use ($user) {
                // Check if user already has this reward
                $hasReward = PhysicalRewardAllocation::where('user_id', $user->id)
                    ->where('physical_reward_id', $reward->id)
                    ->whereIn('status', ['allocated', 'delivered', 'ownership_transferred'])
                    ->exists();

                return !$hasReward && $this->isUserEligibleForReward($user, $reward);
            });
    }

    /**
     * Get user's reward progress
     */
    public function getUserRewardProgress(User $user): array
    {
        $currentTier = $user->membershipTier;
        $teamVolume = $user->getCurrentTeamVolume();
        
        $progress = [
            'current_tier' => $currentTier?->name,
            'team_volume' => $teamVolume?->team_volume ?? 0,
            'active_referrals' => $teamVolume?->active_referrals_count ?? 0,
            'team_depth' => $teamVolume?->team_depth ?? 0,
            'allocated_rewards' => [],
            'available_rewards' => [],
            'next_tier_rewards' => []
        ];

        if (!$currentTier) {
            return $progress;
        }

        // Get allocated rewards
        $progress['allocated_rewards'] = PhysicalRewardAllocation::with('physicalReward')
            ->where('user_id', $user->id)
            ->whereIn('status', ['allocated', 'delivered', 'ownership_transferred'])
            ->get()
            ->map(function ($allocation) {
                return [
                    'reward' => $allocation->physicalReward,
                    'progress' => $allocation->getProgress()
                ];
            });

        // Get available rewards
        $progress['available_rewards'] = $this->getAvailableRewardsForUser($user);

        // Get next tier rewards (if applicable)
        $nextTier = $this->getNextTier($currentTier);
        if ($nextTier) {
            $progress['next_tier_rewards'] = PhysicalReward::active()
                ->forTier($nextTier->name)
                ->get();
        }

        return $progress;
    }

    /**
     * Get the next tier in the hierarchy
     */
    private function getNextTier(InvestmentTier $currentTier): ?InvestmentTier
    {
        $tierHierarchy = ['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite'];
        $currentIndex = array_search($currentTier->name, $tierHierarchy);
        
        if ($currentIndex !== false && $currentIndex < count($tierHierarchy) - 1) {
            return InvestmentTier::where('name', $tierHierarchy[$currentIndex + 1])->first();
        }

        return null;
    }

    /**
     * Record income generated from an asset
     */
    public function recordAssetIncome(PhysicalRewardAllocation $allocation, float $amount, string $source = null): bool
    {
        if (!$allocation->physicalReward->income_generating) {
            return false;
        }

        try {
            $allocation->recordIncomeGenerated($amount);

            // Record activity
            $allocation->user->recordActivity(
                'asset_income_recorded',
                "Income of K{$amount} recorded from {$allocation->physicalReward->name}" . 
                ($source ? " via {$source}" : "")
            );

            Log::info("Asset income recorded", [
                'allocation_id' => $allocation->id,
                'user_id' => $allocation->user_id,
                'amount' => $amount,
                'source' => $source,
                'total_income' => $allocation->total_income_generated
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to record asset income", [
                'allocation_id' => $allocation->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get asset income report for a user
     */
    public function getAssetIncomeReport(User $user): array
    {
        $allocations = PhysicalRewardAllocation::with('physicalReward')
            ->where('user_id', $user->id)
            ->whereIn('status', ['delivered', 'ownership_transferred'])
            ->whereHas('physicalReward', function ($query) {
                $query->where('income_generating', true);
            })
            ->get();

        $totalIncome = $allocations->sum('total_income_generated');
        $monthlyAverage = $allocations->avg('monthly_income_average');

        return [
            'total_income_generated' => $totalIncome,
            'monthly_average' => $monthlyAverage,
            'active_assets' => $allocations->count(),
            'asset_details' => $allocations->map(function ($allocation) {
                return [
                    'reward_name' => $allocation->physicalReward->name,
                    'category' => $allocation->physicalReward->category,
                    'total_income' => $allocation->total_income_generated,
                    'monthly_average' => $allocation->monthly_income_average,
                    'estimated_monthly' => $allocation->physicalReward->estimated_monthly_income,
                    'status' => $allocation->status,
                    'income_tracking_started' => $allocation->income_tracking_started
                ];
            })
        ];
    }

    /**
     * Get consecutive months at current tier for a user
     */
    private function getConsecutiveMonthsAtTier(User $user, int $tierId): int
    {
        $qualification = TierQualification::where('user_id', $user->id)
            ->where('tier_id', $tierId)
            ->where('qualifies', true)
            ->orderBy('qualification_month', 'desc')
            ->first();

        return $qualification?->consecutive_months ?? 0;
    }
}