<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\TeamVolume;
use App\Models\ReferralCommission;
use App\Models\InvestmentTier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PerformanceBonusService
{
    /**
     * MyGrowNet performance bonus thresholds and rates
     */
    public const TEAM_VOLUME_BONUS_THRESHOLDS = [
        100000 => 10.0, // K100,000+ = 10% performance bonus
        50000 => 7.0,   // K50,000+ = 7% performance bonus
        25000 => 5.0,   // K25,000+ = 5% performance bonus
        10000 => 2.0,   // K10,000+ = 2% performance bonus
    ];

    /**
     * Leadership bonus rates based on team development
     */
    public const LEADERSHIP_BONUS_RATES = [
        'elite_leader' => 3.0,    // 3% for Elite tier with exceptional team development
        'diamond_leader' => 2.5,  // 2.5% for Diamond tier with strong team development
        'gold_leader' => 2.0,     // 2% for Gold tier with good team development
        'developing_leader' => 1.0 // 1% for emerging leaders
    ];

    /**
     * Process monthly performance bonuses for all eligible users
     */
    public function processMonthlyPerformanceBonuses(?Carbon $month = null): array
    {
        $month = $month ?? now()->subMonth(); // Previous month by default
        $processed = [];
        $failed = [];

        $eligibleUsers = $this->getEligibleUsersForPerformanceBonuses($month);

        foreach ($eligibleUsers as $user) {
            try {
                $result = $this->processUserPerformanceBonus($user, $month);
                if ($result['total_bonus'] > 0) {
                    $processed[] = [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'tier' => $result['tier'],
                        'team_volume' => $result['team_volume'],
                        'team_volume_bonus' => $result['team_volume_bonus'],
                        'leadership_bonus' => $result['leadership_bonus'],
                        'total_bonus' => $result['total_bonus']
                    ];
                }
            } catch (\Exception $e) {
                $failed[] = [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ];
                Log::error('Performance bonus processing failed for user ' . $user->id, [
                    'error' => $e->getMessage(),
                    'month' => $month->toDateString()
                ]);
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'month' => $month->format('Y-m'),
            'total_processed' => count($processed),
            'total_failed' => count($failed),
            'total_bonus_amount' => collect($processed)->sum('total_bonus')
        ];
    }

    /**
     * Process performance bonus for a specific user
     */
    public function processUserPerformanceBonus(User $user, Carbon $month): array
    {
        $tier = $user->membershipTier;
        if (!$tier) {
            throw new \Exception('User has no membership tier');
        }

        // Get team volume for the specified month
        $teamVolume = TeamVolume::where('user_id', $user->id)
            ->where('period_start', '<=', $month->startOfMonth())
            ->where('period_end', '>=', $month->endOfMonth())
            ->first();

        if (!$teamVolume) {
            return [
                'tier' => $tier->name,
                'team_volume' => 0,
                'team_volume_bonus' => 0,
                'leadership_bonus' => 0,
                'total_bonus' => 0
            ];
        }

        // Calculate team volume bonus
        $teamVolumeBonus = $this->calculateTeamVolumeBonus($teamVolume->team_volume);
        
        // Calculate leadership bonus
        $leadershipBonus = $this->calculateLeadershipBonus($user, $teamVolume, $tier);

        $totalBonus = $teamVolumeBonus + $leadershipBonus;

        if ($totalBonus > 0) {
            $this->awardPerformanceBonus($user, $totalBonus, $teamVolumeBonus, $leadershipBonus, $month);
        }

        return [
            'tier' => $tier->name,
            'team_volume' => $teamVolume->team_volume,
            'team_volume_bonus' => $teamVolumeBonus,
            'leadership_bonus' => $leadershipBonus,
            'total_bonus' => $totalBonus
        ];
    }

    /**
     * Calculate team volume bonus based on volume thresholds
     */
    public function calculateTeamVolumeBonus(float $teamVolume): float
    {
        foreach (self::TEAM_VOLUME_BONUS_THRESHOLDS as $threshold => $rate) {
            if ($teamVolume >= $threshold) {
                return $teamVolume * ($rate / 100);
            }
        }

        return 0;
    }

    /**
     * Calculate leadership bonus based on team development and tier
     */
    public function calculateLeadershipBonus(User $user, TeamVolume $teamVolume, InvestmentTier $tier): float
    {
        if (!$tier->leadership_bonus_eligible) {
            return 0;
        }

        $leadershipLevel = $this->assessLeadershipLevel($user, $teamVolume, $tier);
        $rate = self::LEADERSHIP_BONUS_RATES[$leadershipLevel] ?? 0;

        if ($rate > 0) {
            // Leadership bonus is calculated on team volume
            return $teamVolume->team_volume * ($rate / 100);
        }

        return 0;
    }

    /**
     * Assess leadership level based on team development metrics
     */
    private function assessLeadershipLevel(User $user, TeamVolume $teamVolume, InvestmentTier $tier): string
    {
        $activeReferrals = $teamVolume->active_referrals_count;
        $teamDepth = $teamVolume->team_depth;
        $teamVolume = $teamVolume->team_volume;

        // Elite tier with exceptional performance
        if ($tier->name === 'Elite' && $activeReferrals >= 75 && $teamDepth >= 5 && $teamVolume >= 200000) {
            return 'elite_leader';
        }

        // Diamond tier with strong performance
        if ($tier->name === 'Diamond' && $activeReferrals >= 40 && $teamDepth >= 4 && $teamVolume >= 75000) {
            return 'diamond_leader';
        }

        // Gold tier with good performance
        if ($tier->name === 'Gold' && $activeReferrals >= 20 && $teamDepth >= 3 && $teamVolume >= 25000) {
            return 'gold_leader';
        }

        // Developing leaders (any eligible tier with basic performance)
        if (in_array($tier->name, ['Gold', 'Diamond', 'Elite']) && $activeReferrals >= 15 && $teamDepth >= 3) {
            return 'developing_leader';
        }

        return 'no_leadership_bonus';
    }

    /**
     * Award performance bonus to user
     */
    private function awardPerformanceBonus(User $user, float $totalBonus, float $teamVolumeBonus, float $leadershipBonus, Carbon $month): void
    {
        DB::beginTransaction();
        try {
            // Create team volume bonus commission record
            if ($teamVolumeBonus > 0) {
                ReferralCommission::create([
                    'referrer_id' => $user->id,
                    'referred_id' => $user->id, // Self-referencing for performance bonus
                    'level' => 0, // Special level for performance bonuses
                    'amount' => $teamVolumeBonus,
                    'percentage' => $this->getTeamVolumePercentage($user->getCurrentTeamVolume()?->team_volume ?? 0),
                    'commission_type' => 'PERFORMANCE',
                    'package_type' => 'team_volume_performance_bonus',
                    'package_amount' => $user->getCurrentTeamVolume()?->team_volume ?? 0,
                    'team_volume' => $user->getCurrentTeamVolume()?->team_volume ?? 0,
                    'personal_volume' => $user->getCurrentTeamVolume()?->personal_volume ?? 0,
                    'status' => 'pending'
                ]);
            }

            // Create leadership bonus commission record
            if ($leadershipBonus > 0) {
                ReferralCommission::create([
                    'referrer_id' => $user->id,
                    'referred_id' => $user->id, // Self-referencing for leadership bonus
                    'level' => 0, // Special level for leadership bonuses
                    'amount' => $leadershipBonus,
                    'percentage' => $this->getLeadershipBonusPercentage($user),
                    'commission_type' => 'PERFORMANCE',
                    'package_type' => 'leadership_performance_bonus',
                    'package_amount' => $user->getCurrentTeamVolume()?->team_volume ?? 0,
                    'team_volume' => $user->getCurrentTeamVolume()?->team_volume ?? 0,
                    'personal_volume' => $user->getCurrentTeamVolume()?->personal_volume ?? 0,
                    'status' => 'pending'
                ]);
            }

            // Update user balance
            $user->increment('balance', $totalBonus);
            $user->increment('total_earnings', $totalBonus);

            // Record activity
            $user->recordActivity(
                'performance_bonus_awarded',
                "Performance bonus awarded: K{$totalBonus} (Team Volume: K{$teamVolumeBonus}, Leadership: K{$leadershipBonus}) for {$month->format('M Y')}"
            );

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get team volume percentage for bonus calculation
     */
    private function getTeamVolumePercentage(float $volume): float
    {
        foreach (self::TEAM_VOLUME_BONUS_THRESHOLDS as $threshold => $rate) {
            if ($volume >= $threshold) {
                return $rate;
            }
        }
        return 0;
    }

    /**
     * Get leadership bonus percentage for user
     */
    private function getLeadershipBonusPercentage(User $user): float
    {
        $tier = $user->membershipTier;
        $teamVolume = $user->getCurrentTeamVolume();
        
        if (!$tier || !$teamVolume) {
            return 0;
        }

        $leadershipLevel = $this->assessLeadershipLevel($user, $teamVolume, $tier);
        return self::LEADERSHIP_BONUS_RATES[$leadershipLevel] ?? 0;
    }

    /**
     * Get eligible users for performance bonuses
     */
    private function getEligibleUsersForPerformanceBonuses(Carbon $month): Collection
    {
        return User::with(['membershipTier', 'teamVolumes'])
            ->whereNotNull('current_investment_tier_id')
            ->where('status', 'active')
            ->whereHas('teamVolumes', function ($query) use ($month) {
                $query->where('period_start', '<=', $month->startOfMonth())
                      ->where('period_end', '>=', $month->endOfMonth())
                      ->where('team_volume', '>', 0);
            })
            ->get();
    }

    /**
     * Get performance bonus statistics for a month
     */
    public function getPerformanceBonusStats(Carbon $month): array
    {
        $commissions = ReferralCommission::where('commission_type', 'PERFORMANCE')
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->get();

        $teamVolumeBonuses = $commissions->where('package_type', 'team_volume_performance_bonus');
        $leadershipBonuses = $commissions->where('package_type', 'leadership_performance_bonus');

        return [
            'month' => $month->format('Y-m'),
            'total_bonuses' => $commissions->count(),
            'total_amount' => $commissions->sum('amount'),
            'team_volume_bonuses' => [
                'count' => $teamVolumeBonuses->count(),
                'amount' => $teamVolumeBonuses->sum('amount')
            ],
            'leadership_bonuses' => [
                'count' => $leadershipBonuses->count(),
                'amount' => $leadershipBonuses->sum('amount')
            ],
            'average_bonus' => $commissions->count() > 0 ? $commissions->avg('amount') : 0,
            'top_earners' => $this->getTopPerformanceBonusEarners($month, 10)
        ];
    }

    /**
     * Get top performance bonus earners for a month
     */
    public function getTopPerformanceBonusEarners(Carbon $month, int $limit = 10): array
    {
        return ReferralCommission::select('referrer_id', DB::raw('SUM(amount) as total_bonus'))
            ->with('referrer:id,name')
            ->where('commission_type', 'PERFORMANCE')
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->groupBy('referrer_id')
            ->orderBy('total_bonus', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($commission) {
                return [
                    'user_id' => $commission->referrer_id,
                    'user_name' => $commission->referrer->name ?? 'Unknown',
                    'total_bonus' => $commission->total_bonus
                ];
            })
            ->toArray();
    }

    /**
     * Calculate potential performance bonus for user (preview)
     */
    public function calculatePotentialBonus(User $user): array
    {
        $tier = $user->membershipTier;
        $teamVolume = $user->getCurrentTeamVolume();

        if (!$tier || !$teamVolume) {
            return [
                'eligible' => false,
                'reason' => 'No tier or team volume data'
            ];
        }

        $teamVolumeBonus = $this->calculateTeamVolumeBonus($teamVolume->team_volume);
        $leadershipBonus = $this->calculateLeadershipBonus($user, $teamVolume, $tier);

        return [
            'eligible' => ($teamVolumeBonus + $leadershipBonus) > 0,
            'current_team_volume' => $teamVolume->team_volume,
            'team_volume_bonus' => $teamVolumeBonus,
            'leadership_bonus' => $leadershipBonus,
            'total_potential_bonus' => $teamVolumeBonus + $leadershipBonus,
            'next_threshold' => $this->getNextVolumeThreshold($teamVolume->team_volume),
            'leadership_level' => $this->assessLeadershipLevel($user, $teamVolume, $tier)
        ];
    }

    /**
     * Get next volume threshold for bonus improvement
     */
    private function getNextVolumeThreshold(float $currentVolume): ?array
    {
        foreach (self::TEAM_VOLUME_BONUS_THRESHOLDS as $threshold => $rate) {
            if ($currentVolume < $threshold) {
                return [
                    'threshold' => $threshold,
                    'rate' => $rate,
                    'additional_volume_needed' => $threshold - $currentVolume
                ];
            }
        }

        return null; // Already at highest threshold
    }

    /**
     * Process profit-boost week bonuses (25% increase)
     */
    public function processProfitBoostWeek(Carbon $weekStart, Carbon $weekEnd): array
    {
        $processed = [];
        $failed = [];

        // Get all commissions earned during profit-boost week
        $commissions = ReferralCommission::whereBetween('created_at', [$weekStart, $weekEnd])
            ->where('status', 'pending')
            ->where('commission_type', 'REFERRAL')
            ->get();

        foreach ($commissions as $commission) {
            try {
                $boostAmount = $commission->amount * 0.25; // 25% boost

                // Create boost bonus commission
                ReferralCommission::create([
                    'referrer_id' => $commission->referrer_id,
                    'referred_id' => $commission->referred_id,
                    'level' => $commission->level,
                    'amount' => $boostAmount,
                    'percentage' => 25.0, // 25% boost
                    'commission_type' => 'PERFORMANCE',
                    'package_type' => 'profit_boost_week_bonus',
                    'package_amount' => $commission->package_amount,
                    'team_volume' => $commission->team_volume,
                    'personal_volume' => $commission->personal_volume,
                    'status' => 'pending'
                ]);

                $processed[] = [
                    'user_id' => $commission->referrer_id,
                    'original_commission' => $commission->amount,
                    'boost_amount' => $boostAmount
                ];

            } catch (\Exception $e) {
                $failed[] = [
                    'commission_id' => $commission->id,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),
            'total_processed' => count($processed),
            'total_boost_amount' => collect($processed)->sum('boost_amount')
        ];
    }
}