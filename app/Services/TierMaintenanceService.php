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
use Carbon\Carbon;

class TierMaintenanceService
{
    /**
     * Check and maintain tier status for all users
     */
    public function processTierMaintenance(): array
    {
        $maintained = [];
        $downgraded = [];
        $failed = [];

        $users = User::with(['membershipTier', 'teamVolumes'])
            ->whereNotNull('current_investment_tier_id')
            ->where('status', 'active')
            ->get();

        foreach ($users as $user) {
            try {
                $result = $this->checkAndMaintainUserTier($user);
                
                if ($result['action'] === 'maintained') {
                    $maintained[] = [
                        'user_id' => $user->id,
                        'tier' => $result['tier'],
                        'consecutive_months' => $result['consecutive_months']
                    ];
                } elseif ($result['action'] === 'downgraded') {
                    $downgraded[] = [
                        'user_id' => $user->id,
                        'from_tier' => $result['from_tier'],
                        'to_tier' => $result['to_tier'],
                        'reason' => $result['reason']
                    ];
                }
            } catch (\Exception $e) {
                $failed[] = [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ];
                Log::error('Tier maintenance failed for user ' . $user->id, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return [
            'maintained' => $maintained,
            'downgraded' => $downgraded,
            'failed' => $failed,
            'total_maintained' => count($maintained),
            'total_downgraded' => count($downgraded),
            'total_failed' => count($failed)
        ];
    }

    /**
     * Check and maintain tier status for a specific user
     */
    public function checkAndMaintainUserTier(User $user): array
    {
        $currentTier = $user->membershipTier;
        if (!$currentTier) {
            return ['action' => 'no_tier', 'message' => 'User has no current tier'];
        }

        // Check if user meets current tier requirements
        $currentVolume = $user->getCurrentTeamVolume();
        $meetsCurrentRequirements = $this->meetsCurrentTierRequirements($user, $currentTier, $currentVolume);

        if ($meetsCurrentRequirements) {
            // Check consecutive months for permanent status
            $consecutiveMonths = $this->getConsecutiveMonthsQualified($user, $currentTier);
            
            if ($consecutiveMonths >= $currentTier->consecutive_months_required) {
                // Mark as permanent tier status
                $this->markTierAsPermanent($user, $currentTier);
                
                return [
                    'action' => 'maintained',
                    'tier' => $currentTier->name,
                    'consecutive_months' => $consecutiveMonths,
                    'permanent_status' => true
                ];
            }

            return [
                'action' => 'maintained',
                'tier' => $currentTier->name,
                'consecutive_months' => $consecutiveMonths,
                'permanent_status' => false
            ];
        }

        // User doesn't meet current tier requirements - check for downgrade
        $shouldDowngrade = $this->shouldDowngradeTier($user, $currentTier);
        
        if ($shouldDowngrade) {
            $newTier = $this->findAppropriateDowngradeTier($user, $currentTier);
            if ($newTier) {
                $this->downgradeTier($user, $currentTier, $newTier, 'performance_requirements_not_met');
                
                return [
                    'action' => 'downgraded',
                    'from_tier' => $currentTier->name,
                    'to_tier' => $newTier->name,
                    'reason' => 'Performance requirements not met'
                ];
            }
        }

        return [
            'action' => 'warning',
            'tier' => $currentTier->name,
            'message' => 'Requirements not met but within grace period'
        ];
    }

    /**
     * Check if user meets current tier requirements
     */
    private function meetsCurrentTierRequirements(User $user, InvestmentTier $tier, ?TeamVolume $currentVolume): bool
    {
        if (!$currentVolume) {
            return false;
        }

        return $currentVolume->active_referrals_count >= $tier->required_active_referrals &&
               $currentVolume->team_volume >= $tier->required_team_volume;
    }

    /**
     * Get consecutive months user has qualified for current tier
     */
    private function getConsecutiveMonthsQualified(User $user, InvestmentTier $tier): int
    {
        $consecutiveMonths = 0;
        $currentMonth = now()->startOfMonth();

        // Check backwards from current month
        for ($i = 0; $i < 12; $i++) { // Check up to 12 months back
            $checkMonth = $currentMonth->copy()->subMonths($i);
            $monthEnd = $checkMonth->copy()->endOfMonth();

            $teamVolume = TeamVolume::where('user_id', $user->id)
                ->where('period_start', '<=', $checkMonth)
                ->where('period_end', '>=', $monthEnd)
                ->first();

            if (!$teamVolume || !$this->meetsCurrentTierRequirements($user, $tier, $teamVolume)) {
                break;
            }

            $consecutiveMonths++;
        }

        return $consecutiveMonths;
    }

    /**
     * Mark tier as permanent status
     */
    private function markTierAsPermanent(User $user, InvestmentTier $tier): void
    {
        // Update user's tier history to mark as permanent
        $tierHistory = $user->getTierHistory();
        $tierHistory[] = [
            'tier_id' => $tier->id,
            'tier_name' => $tier->name,
            'status' => 'permanent',
            'achieved_at' => now()->toISOString(),
            'consecutive_months' => $this->getConsecutiveMonthsQualified($user, $tier)
        ];

        $user->update(['tier_history' => json_encode($tierHistory)]);

        $user->recordActivity(
            'tier_permanent_status',
            "Achieved permanent {$tier->name} tier status after maintaining requirements for {$tier->consecutive_months_required} consecutive months"
        );
    }

    /**
     * Check if user should be downgraded
     */
    private function shouldDowngradeTier(User $user, InvestmentTier $currentTier): bool
    {
        // Check if user has been below requirements for grace period
        $gracePeriodMonths = 2; // 2-month grace period
        $monthsBelowRequirements = 0;
        $currentMonth = now()->startOfMonth();

        for ($i = 0; $i < $gracePeriodMonths; $i++) {
            $checkMonth = $currentMonth->copy()->subMonths($i);
            $monthEnd = $checkMonth->copy()->endOfMonth();

            $teamVolume = TeamVolume::where('user_id', $user->id)
                ->where('period_start', '<=', $checkMonth)
                ->where('period_end', '>=', $monthEnd)
                ->first();

            if (!$teamVolume || !$this->meetsCurrentTierRequirements($user, $currentTier, $teamVolume)) {
                $monthsBelowRequirements++;
            }
        }

        return $monthsBelowRequirements >= $gracePeriodMonths;
    }

    /**
     * Find appropriate tier for downgrade
     */
    private function findAppropriateDowngradeTier(User $user, InvestmentTier $currentTier): ?InvestmentTier
    {
        $currentVolume = $user->getCurrentTeamVolume();
        if (!$currentVolume) {
            // Default to Bronze if no volume data
            return InvestmentTier::where('name', 'Bronze')->first();
        }

        // Find the highest tier the user qualifies for
        $tiers = InvestmentTier::where('order', '<', $currentTier->order)
            ->orderBy('order', 'desc')
            ->get();

        foreach ($tiers as $tier) {
            if ($this->meetsCurrentTierRequirements($user, $tier, $currentVolume)) {
                return $tier;
            }
        }

        // If user doesn't qualify for any tier, default to Bronze
        return InvestmentTier::where('name', 'Bronze')->first();
    }

    /**
     * Downgrade user to lower tier
     */
    private function downgradeTier(User $user, InvestmentTier $fromTier, InvestmentTier $toTier, string $reason): void
    {
        DB::beginTransaction();
        try {
            // Update user's tier
            $user->update([
                'current_investment_tier_id' => $toTier->id,
                'tier_upgraded_at' => now()
            ]);

            // Record the downgrade
            TierUpgrade::create([
                'user_id' => $user->id,
                'from_tier_id' => $fromTier->id,
                'to_tier_id' => $toTier->id,
                'total_investment_amount' => $user->total_investment_amount ?? 0,
                'team_volume' => $user->getCurrentTeamVolume()?->team_volume ?? 0,
                'active_referrals' => $user->getCurrentTeamVolume()?->active_referrals_count ?? 0,
                'achievement_bonus_awarded' => 0, // No bonus for downgrades
                'consecutive_months_qualified' => 0,
                'upgrade_reason' => $reason,
                'processed_at' => now()
            ]);

            // Update tier history
            $user->addTierHistory($toTier->id, $reason);

            // Record activity
            $user->recordActivity(
                'tier_downgraded',
                "Downgraded from {$fromTier->name} to {$toTier->name} tier due to: {$reason}"
            );

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get tier maintenance statistics
     */
    public function getTierMaintenanceStats(): array
    {
        $stats = [];
        $tiers = InvestmentTier::where('name', 'in', ['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite'])
            ->orderBy('order')
            ->get();

        foreach ($tiers as $tier) {
            $usersInTier = User::where('current_investment_tier_id', $tier->id)->count();
            $usersAtRisk = $this->countUsersAtRiskOfDowngrade($tier);
            $permanentStatusUsers = $this->countPermanentStatusUsers($tier);

            $stats[] = [
                'tier_name' => $tier->name,
                'total_users' => $usersInTier,
                'users_at_risk' => $usersAtRisk,
                'permanent_status_users' => $permanentStatusUsers,
                'requirements' => [
                    'active_referrals' => $tier->required_active_referrals,
                    'team_volume' => $tier->required_team_volume,
                    'consecutive_months' => $tier->consecutive_months_required
                ]
            ];
        }

        return $stats;
    }

    /**
     * Count users at risk of downgrade for a specific tier
     */
    private function countUsersAtRiskOfDowngrade(InvestmentTier $tier): int
    {
        return User::where('current_investment_tier_id', $tier->id)
            ->whereHas('teamVolumes', function ($query) use ($tier) {
                $query->where('period_start', '>=', now()->subMonth()->startOfMonth())
                      ->where(function ($subQuery) use ($tier) {
                          $subQuery->where('team_volume', '<', $tier->required_team_volume)
                                   ->orWhere('active_referrals_count', '<', $tier->required_active_referrals);
                      });
            })
            ->count();
    }

    /**
     * Count users with permanent status for a specific tier
     */
    private function countPermanentStatusUsers(InvestmentTier $tier): int
    {
        return User::where('current_investment_tier_id', $tier->id)
            ->where('tier_history', 'like', '%"status":"permanent"%')
            ->count();
    }

    /**
     * Get users requiring tier maintenance attention
     */
    public function getUsersRequiringAttention(): Collection
    {
        return User::with(['membershipTier', 'teamVolumes'])
            ->whereNotNull('current_investment_tier_id')
            ->where('status', 'active')
            ->get()
            ->filter(function ($user) {
                $currentTier = $user->membershipTier;
                $currentVolume = $user->getCurrentTeamVolume();
                
                if (!$currentTier || !$currentVolume) {
                    return true; // Needs attention
                }

                return !$this->meetsCurrentTierRequirements($user, $currentTier, $currentVolume);
            });
    }

    /**
     * Process tier qualification warnings
     */
    public function processQualificationWarnings(): array
    {
        $warnings = [];
        $usersAtRisk = $this->getUsersRequiringAttention();

        foreach ($usersAtRisk as $user) {
            $currentTier = $user->membershipTier;
            $currentVolume = $user->getCurrentTeamVolume();
            
            if (!$currentTier) {
                continue;
            }

            $shortfall = [
                'referrals' => max(0, $currentTier->required_active_referrals - ($currentVolume?->active_referrals_count ?? 0)),
                'team_volume' => max(0, $currentTier->required_team_volume - ($currentVolume?->team_volume ?? 0))
            ];

            $warnings[] = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'current_tier' => $currentTier->name,
                'shortfall' => $shortfall,
                'grace_period_remaining' => $this->getGracePeriodRemaining($user, $currentTier)
            ];

            // Send notification to user
            $this->sendTierMaintenanceWarning($user, $currentTier, $shortfall);
        }

        return $warnings;
    }

    /**
     * Get remaining grace period for user
     */
    private function getGracePeriodRemaining(User $user, InvestmentTier $tier): int
    {
        $gracePeriodMonths = 2;
        $monthsBelowRequirements = 0;
        $currentMonth = now()->startOfMonth();

        for ($i = 0; $i < $gracePeriodMonths; $i++) {
            $checkMonth = $currentMonth->copy()->subMonths($i);
            $monthEnd = $checkMonth->copy()->endOfMonth();

            $teamVolume = TeamVolume::where('user_id', $user->id)
                ->where('period_start', '<=', $checkMonth)
                ->where('period_end', '>=', $monthEnd)
                ->first();

            if (!$teamVolume || !$this->meetsCurrentTierRequirements($user, $tier, $teamVolume)) {
                $monthsBelowRequirements++;
            }
        }

        return max(0, $gracePeriodMonths - $monthsBelowRequirements);
    }

    /**
     * Send tier maintenance warning to user
     */
    private function sendTierMaintenanceWarning(User $user, InvestmentTier $tier, array $shortfall): void
    {
        $user->recordActivity(
            'tier_maintenance_warning',
            "Warning: {$tier->name} tier requirements not met. Need {$shortfall['referrals']} more referrals and K{$shortfall['team_volume']} more team volume."
        );

        // TODO: Send email/SMS notification
        Log::info('Tier maintenance warning sent', [
            'user_id' => $user->id,
            'tier' => $tier->name,
            'shortfall' => $shortfall
        ]);
    }
}