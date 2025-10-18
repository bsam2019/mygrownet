<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPoints;
use App\Models\PointTransaction;
use App\Models\MonthlyActivityStatus;
use App\Events\PointsAwarded;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointService
{
    /**
     * Award points to a user
     */
    public function awardPoints(
        User $user,
        string $source,
        int $lpAmount,
        int $mapAmount,
        string $description,
        $reference = null
    ): PointTransaction {
        return DB::transaction(function () use ($user, $source, $lpAmount, $mapAmount, $description, $reference) {
            // Ensure user has points record
            $userPoints = $this->ensureUserPoints($user);

            // Apply multiplier
            $multiplier = $userPoints->active_multiplier;
            $finalLP = round($lpAmount * $multiplier);
            $finalMAP = round($mapAmount * $multiplier);

            // Create transaction
            $transaction = PointTransaction::create([
                'user_id' => $user->id,
                'point_type' => 'both',
                'lp_amount' => $finalLP,
                'map_amount' => $finalMAP,
                'source' => $source,
                'description' => $description,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'multiplier_applied' => $multiplier,
            ]);

            // Update user points
            $userPoints->increment('lifetime_points', $finalLP);
            $userPoints->increment('monthly_points', $finalMAP);
            $userPoints->touch('last_activity_date');

            // Update user activity
            $user->update(['is_currently_active' => true]);

            // Check for level advancement
            app(LevelAdvancementService::class)->checkLevelAdvancement($user);

            // Check for badges
            $this->checkBadgeEligibility($user, $source);

            // Fire event
            event(new PointsAwarded($user, $transaction));

            Log::info("Points awarded", [
                'user_id' => $user->id,
                'source' => $source,
                'lp' => $finalLP,
                'map' => $finalMAP,
                'multiplier' => $multiplier,
            ]);

            return $transaction;
        });
    }

    /**
     * Ensure user has points record
     */
    protected function ensureUserPoints(User $user): UserPoints
    {
        return $user->points()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'lifetime_points' => 0,
                'monthly_points' => 0,
                'last_month_points' => 0,
                'three_month_average' => 0,
                'current_streak_months' => 0,
                'longest_streak_months' => 0,
                'active_multiplier' => 1.00,
                'last_activity_date' => now(),
            ]
        );
    }

    /**
     * Check if user meets monthly qualification
     */
    public function checkMonthlyQualification(User $user): bool
    {
        $userPoints = $user->points;
        if (!$userPoints) {
            return false;
        }

        $requiredMAP = $userPoints->getRequiredMapForLevel();
        $currentMAP = $userPoints->monthly_points;

        return $currentMAP >= $requiredMAP;
    }

    /**
     * Get required MAP for user's level
     */
    public function getRequiredMAP(User $user): int
    {
        return match ($user->current_professional_level) {
            'associate' => 100,
            'professional' => 200,
            'senior' => 300,
            'manager' => 400,
            'director' => 500,
            'executive' => 600,
            'ambassador' => 800,
            default => 100,
        };
    }

    /**
     * Get performance tier for user
     */
    public function getPerformanceTier(User $user): string
    {
        $map = $user->points?->monthly_points ?? 0;

        return match (true) {
            $map >= 1000 => 'platinum',
            $map >= 600 => 'gold',
            $map >= 300 => 'silver',
            default => 'bronze',
        };
    }

    /**
     * Get commission bonus percentage
     */
    public function getCommissionBonus(User $user): float
    {
        return match ($this->getPerformanceTier($user)) {
            'platinum' => 30.0,
            'gold' => 20.0,
            'silver' => 10.0,
            default => 0.0,
        };
    }

    /**
     * Reset monthly points for all users
     */
    public function resetMonthlyPoints(): void
    {
        DB::transaction(function () {
            Log::info("Starting monthly points reset");

            // Archive last month's data
            $this->archiveMonthlyActivity();

            // Reset monthly points
            UserPoints::query()->update([
                'last_month_points' => DB::raw('monthly_points'),
                'monthly_points' => 0,
            ]);

            // Update streaks
            $this->updateActivityStreaks();

            // Update multipliers
            $this->updateMultipliers();

            Log::info("Monthly points reset completed");
        });
    }

    /**
     * Archive monthly activity for all users
     */
    protected function archiveMonthlyActivity(): void
    {
        $lastMonth = now()->subMonth();

        User::with('points')->chunk(100, function ($users) use ($lastMonth) {
            foreach ($users as $user) {
                if (!$user->points) {
                    continue;
                }

                $qualified = $this->checkMonthlyQualification($user);

                MonthlyActivityStatus::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'month' => $lastMonth->month,
                        'year' => $lastMonth->year,
                    ],
                    [
                        'map_earned' => $user->points->monthly_points,
                        'map_required' => $this->getRequiredMAP($user),
                        'qualified' => $qualified,
                        'performance_tier' => $this->getPerformanceTier($user),
                        'commission_bonus_percent' => $this->getCommissionBonus($user),
                        'team_synergy_bonus' => $this->checkTeamSynergyBonus($user),
                    ]
                );
            }
        });
    }

    /**
     * Update activity streaks for all users
     */
    protected function updateActivityStreaks(): void
    {
        UserPoints::with('user')->chunk(100, function ($userPoints) {
            foreach ($userPoints as $points) {
                $qualified = $points->monthly_points >= $points->getRequiredMapForLevel();

                if ($qualified) {
                    // Increment streak
                    $newStreak = $points->current_streak_months + 1;
                    $points->update([
                        'current_streak_months' => $newStreak,
                        'longest_streak_months' => max($newStreak, $points->longest_streak_months),
                    ]);
                } else {
                    // Reset streak
                    $points->update(['current_streak_months' => 0]);
                }
            }
        });
    }

    /**
     * Update multipliers based on streaks
     */
    protected function updateMultipliers(): void
    {
        UserPoints::chunk(100, function ($userPoints) {
            foreach ($userPoints as $points) {
                $points->updateMultiplier();
            }
        });
    }

    /**
     * Check team synergy bonus
     */
    protected function checkTeamSynergyBonus(User $user): bool
    {
        $directReferrals = $user->directReferrals()->limit(3)->get();

        if ($directReferrals->count() < 3) {
            return false;
        }

        foreach ($directReferrals as $referral) {
            if (!$this->checkMonthlyQualification($referral)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check and award badges
     */
    protected function checkBadgeEligibility(User $user, string $source): void
    {
        // This will be expanded with specific badge logic
        // For now, just check a few simple ones

        // First Sale badge
        if ($source === 'product_sale' && !$user->badges()->where('badge_code', 'first_sale')->exists()) {
            $this->awardBadge($user, 'first_sale');
        }

        // Network Builder badge (100 referrals)
        if ($user->referral_count >= 100 && !$user->badges()->where('badge_code', 'network_builder')->exists()) {
            $this->awardBadge($user, 'network_builder');
        }

        // Scholar badge (20 courses)
        if ($user->courses_completed_count >= 20 && !$user->badges()->where('badge_code', 'scholar')->exists()) {
            $this->awardBadge($user, 'scholar');
        }

        // Consistent Champion badge (12-month streak)
        if ($user->points && $user->points->current_streak_months >= 12 && !$user->badges()->where('badge_code', 'consistent_champion')->exists()) {
            $this->awardBadge($user, 'consistent_champion');
        }
    }

    /**
     * Award a badge to user
     */
    protected function awardBadge(User $user, string $badgeCode): void
    {
        $badges = \App\Models\UserBadge::availableBadges();
        $badgeConfig = $badges[$badgeCode] ?? null;

        if (!$badgeConfig) {
            return;
        }

        $badge = $user->badges()->create([
            'badge_code' => $badgeCode,
            'badge_name' => $badgeConfig['name'],
            'lp_reward' => $badgeConfig['lp_reward'],
            'earned_at' => now(),
        ]);

        // Award LP for badge
        if ($badgeConfig['lp_reward'] > 0) {
            $this->awardPoints(
                $user,
                'badge_earned',
                $badgeConfig['lp_reward'],
                0,
                "Badge earned: {$badgeConfig['name']}",
                $badge
            );
        }

        Log::info("Badge awarded", [
            'user_id' => $user->id,
            'badge_code' => $badgeCode,
            'lp_reward' => $badgeConfig['lp_reward'],
        ]);
    }

    /**
     * Award daily login points
     */
    public function awardDailyLogin(User $user): ?PointTransaction
    {
        // Check if already logged in today
        $today = now()->startOfDay();
        $alreadyAwarded = PointTransaction::where('user_id', $user->id)
            ->where('source', 'daily_login')
            ->where('created_at', '>=', $today)
            ->exists();

        if ($alreadyAwarded) {
            return null;
        }

        return $this->awardPoints(
            $user,
            'daily_login',
            0,
            5,
            'Daily login bonus'
        );
    }

    /**
     * Check and award streak bonuses
     */
    public function checkStreakBonuses(User $user): void
    {
        $consecutiveDays = $this->getConsecutiveLoginDays($user);

        // 7-day streak bonus
        if ($consecutiveDays == 7) {
            $this->awardPoints(
                $user,
                'login_streak_7',
                0,
                50,
                '7-day login streak bonus'
            );
        }

        // 30-day streak bonus
        if ($consecutiveDays == 30) {
            $this->awardPoints(
                $user,
                'login_streak_30',
                0,
                200,
                '30-day login streak bonus'
            );
        }
    }

    /**
     * Get consecutive login days
     */
    protected function getConsecutiveLoginDays(User $user): int
    {
        $days = 0;
        $date = now()->startOfDay();

        for ($i = 0; $i < 30; $i++) {
            $hasLogin = PointTransaction::where('user_id', $user->id)
                ->where('source', 'daily_login')
                ->whereDate('created_at', $date)
                ->exists();

            if (!$hasLogin) {
                break;
            }

            $days++;
            $date->subDay();
        }

        return $days;
    }
}
