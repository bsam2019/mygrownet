<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\TeamVolume;
use App\Models\TierUpgrade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MyGrowNetTierAdvancementService
{
    /**
     * Check and process tier upgrades for all eligible users
     */
    public function processAutomaticTierUpgrades(): array
    {
        $processed = [];
        $failed = [];

        $users = User::with(['membershipTier', 'teamVolumes'])
            ->whereNotNull('current_investment_tier_id')
            ->where('status', 'active')
            ->get();

        foreach ($users as $user) {
            try {
                $result = $this->checkAndProcessUserTierUpgrade($user);
                if ($result['upgraded']) {
                    $processed[] = [
                        'user_id' => $user->id,
                        'from_tier' => $result['from_tier'],
                        'to_tier' => $result['to_tier'],
                        'achievement_bonus' => $result['achievement_bonus']
                    ];
                }
            } catch (\Exception $e) {
                $failed[] = [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ];
                Log::error('Tier upgrade failed for user ' . $user->id, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'total_processed' => count($processed),
            'total_failed' => count($failed)
        ];
    }

    /**
     * Check and process tier upgrade for a specific user
     */
    public function checkAndProcessUserTierUpgrade(User $user): array
    {
        $eligibility = $user->checkMyGrowNetTierUpgradeEligibility();
        
        if (!$eligibility['eligible'] || !$eligibility['next_tier']) {
            return [
                'upgraded' => false,
                'reason' => $eligibility['message'] ?? 'Not eligible for upgrade'
            ];
        }

        // Check if user has maintained requirements for consecutive months
        if (!$this->hasMetConsecutiveMonthRequirements($user, $eligibility['next_tier'])) {
            return [
                'upgraded' => false,
                'reason' => 'Consecutive month requirements not met'
            ];
        }

        $fromTier = $eligibility['current_tier'];
        $toTier = $eligibility['next_tier'];

        DB::beginTransaction();
        try {
            // Upgrade the user
            $upgraded = $user->upgradeToMyGrowNetTier($toTier, 'automatic_qualification');
            
            if ($upgraded) {
                // Record the tier upgrade
                TierUpgrade::create([
                    'user_id' => $user->id,
                    'from_tier_id' => $fromTier?->id,
                    'to_tier_id' => $toTier->id,
                    'total_investment_amount' => $user->total_investment_amount ?? 0,
                    'upgrade_reason' => 'mygrownet_qualification_met',
                    'team_volume' => $user->getCurrentTeamVolume()?->team_volume ?? 0,
                    'active_referrals' => $user->getCurrentTeamVolume()?->active_referrals_count ?? 0,
                    'achievement_bonus_awarded' => $toTier->achievement_bonus
                ]);

                DB::commit();

                return [
                    'upgraded' => true,
                    'from_tier' => $fromTier?->name ?? 'None',
                    'to_tier' => $toTier->name,
                    'achievement_bonus' => $toTier->achievement_bonus
                ];
            }

            DB::rollBack();
            return [
                'upgraded' => false,
                'reason' => 'Upgrade process failed'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if user has met consecutive month requirements
     */
    private function hasMetConsecutiveMonthRequirements(User $user, InvestmentTier $targetTier): bool
    {
        $requiredMonths = $targetTier->consecutive_months_required;
        
        if ($requiredMonths <= 1) {
            return true; // No consecutive requirement
        }

        // Get team volumes for the last N months
        $teamVolumes = TeamVolume::where('user_id', $user->id)
            ->where('period_start', '>=', now()->subMonths($requiredMonths)->startOfMonth())
            ->orderBy('period_start', 'desc')
            ->limit($requiredMonths)
            ->get();

        if ($teamVolumes->count() < $requiredMonths) {
            return false; // Not enough historical data
        }

        // Check if all months meet the requirements
        foreach ($teamVolumes as $volume) {
            if (!$targetTier->qualifiesForUpgrade($volume->active_referrals_count, $volume->team_volume)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get tier advancement statistics
     */
    public function getTierAdvancementStats(): array
    {
        $tiers = InvestmentTier::where('name', 'in', ['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite'])
            ->orderBy('order')
            ->get();

        $stats = [];
        
        foreach ($tiers as $tier) {
            $userCount = User::where('current_investment_tier_id', $tier->id)->count();
            $eligibleForUpgrade = $this->countEligibleForUpgrade($tier);
            
            $stats[] = [
                'tier_name' => $tier->name,
                'current_users' => $userCount,
                'eligible_for_upgrade' => $eligibleForUpgrade,
                'requirements' => $tier->getUpgradeRequirements()
            ];
        }

        return $stats;
    }

    /**
     * Count users eligible for upgrade from a specific tier
     */
    private function countEligibleForUpgrade(InvestmentTier $tier): int
    {
        $nextTier = $tier->getNextTier();
        if (!$nextTier) {
            return 0; // No higher tier available
        }

        return User::where('current_investment_tier_id', $tier->id)
            ->whereHas('teamVolumes', function ($query) use ($nextTier) {
                $query->where('team_volume', '>=', $nextTier->required_team_volume)
                      ->where('active_referrals_count', '>=', $nextTier->required_active_referrals)
                      ->where('period_start', '>=', now()->subMonths($nextTier->consecutive_months_required)->startOfMonth());
            })
            ->count();
    }

    /**
     * Get users ready for tier upgrade
     */
    public function getUsersReadyForUpgrade(): Collection
    {
        return User::with(['membershipTier', 'teamVolumes'])
            ->whereNotNull('current_investment_tier_id')
            ->where('status', 'active')
            ->get()
            ->filter(function ($user) {
                $eligibility = $user->checkMyGrowNetTierUpgradeEligibility();
                return $eligibility['eligible'] && 
                       $eligibility['next_tier'] && 
                       $this->hasMetConsecutiveMonthRequirements($user, $eligibility['next_tier']);
            });
    }

    /**
     * Award achievement bonuses for tier upgrades
     */
    public function awardAchievementBonus(User $user, InvestmentTier $tier): bool
    {
        if ($tier->achievement_bonus <= 0) {
            return false;
        }

        try {
            $user->increment('balance', $tier->achievement_bonus);
            $user->increment('total_earnings', $tier->achievement_bonus);

            $user->recordActivity(
                'achievement_bonus_awarded',
                "Achievement bonus of K{$tier->achievement_bonus} awarded for {$tier->name} tier upgrade"
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to award achievement bonus', [
                'user_id' => $user->id,
                'tier' => $tier->name,
                'bonus_amount' => $tier->achievement_bonus,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}