<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\LevelAdvancementNotification;

class LevelAdvancementService
{
    /**
     * Check if user qualifies for level advancement
     */
    public function checkLevelAdvancement(User $user): ?string
    {
        $currentLevel = $user->current_professional_level;
        $nextLevel = $this->getNextLevel($currentLevel);

        if (!$nextLevel) {
            return null; // Already at max level
        }

        $requirements = $this->getLevelRequirements($nextLevel);

        if ($this->meetsAllRequirements($user, $requirements)) {
            return $this->promoteUser($user, $nextLevel);
        }

        return null;
    }

    /**
     * Get next level in progression
     */
    protected function getNextLevel(string $currentLevel): ?string
    {
        $levels = [
            'associate' => 'professional',
            'professional' => 'senior',
            'senior' => 'manager',
            'manager' => 'director',
            'director' => 'executive',
            'executive' => 'ambassador',
            'ambassador' => null,
        ];

        return $levels[$currentLevel] ?? null;
    }

    /**
     * Get requirements for a level
     */
    protected function getLevelRequirements(string $level): array
    {
        return match ($level) {
            'professional' => [
                'lp' => 500,
                'min_days' => 30,
                'direct_referrals' => 3,
                'active_referrals' => 0,
                'courses' => 0,
                'downline_level' => null,
            ],
            'senior' => [
                'lp' => 1500,
                'min_days' => 90,
                'direct_referrals' => 3,
                'active_referrals' => 2,
                'courses' => 1,
                'downline_level' => null,
            ],
            'manager' => [
                'lp' => 4000,
                'min_days' => 180,
                'direct_referrals' => 3,
                'active_referrals' => 2,
                'courses' => 3,
                'downline_level' => 'professional',
            ],
            'director' => [
                'lp' => 10000,
                'min_days' => 365,
                'direct_referrals' => 3,
                'active_referrals' => 2,
                'courses' => 5,
                'downline_level' => 'senior',
            ],
            'executive' => [
                'lp' => 25000,
                'min_days' => 547, // 18 months
                'direct_referrals' => 3,
                'active_referrals' => 2,
                'courses' => 10,
                'downline_level' => 'manager',
            ],
            'ambassador' => [
                'lp' => 50000,
                'min_days' => 730, // 24 months
                'direct_referrals' => 3,
                'active_referrals' => 2,
                'courses' => 15,
                'downline_level' => 'director',
            ],
            default => [],
        };
    }

    /**
     * Check if user meets all requirements
     */
    protected function meetsAllRequirements(User $user, array $requirements): bool
    {
        // Check LP
        if (($user->points?->lifetime_points ?? 0) < $requirements['lp']) {
            return false;
        }

        // Check account age
        if ($user->account_age_days < $requirements['min_days']) {
            return false;
        }

        // Check direct referrals
        if ($user->referral_count < $requirements['direct_referrals']) {
            return false;
        }

        // Check active referrals
        if ($user->active_direct_referrals_count < $requirements['active_referrals']) {
            return false;
        }

        // Check courses completed
        if ($user->courses_completed_count < $requirements['courses']) {
            return false;
        }

        // Check downline level requirement
        if ($requirements['downline_level']) {
            if (!$this->hasDownlineAtLevel($user, $requirements['downline_level'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has at least one downline member at specified level
     */
    protected function hasDownlineAtLevel(User $user, string $requiredLevel): bool
    {
        $levelOrder = [
            'associate' => 0,
            'professional' => 1,
            'senior' => 2,
            'manager' => 3,
            'director' => 4,
            'executive' => 5,
            'ambassador' => 6,
        ];

        $requiredLevelValue = $levelOrder[$requiredLevel] ?? 0;

        return $user->directReferrals()
            ->get()
            ->contains(function ($referral) use ($levelOrder, $requiredLevelValue) {
                $referralLevelValue = $levelOrder[$referral->current_professional_level] ?? 0;
                return $referralLevelValue >= $requiredLevelValue;
            });
    }

    /**
     * Promote user to next level
     */
    protected function promoteUser(User $user, string $newLevel): string
    {
        return DB::transaction(function () use ($user, $newLevel) {
            $oldLevel = $user->current_professional_level;

            // Update user level
            $user->update([
                'current_professional_level' => $newLevel,
                'level_achieved_at' => now(),
            ]);

            // Award milestone bonus
            $bonus = $this->getMilestoneBonus($newLevel);
            $this->awardMilestoneBonus($user, $newLevel, $bonus);

            // Send notification
            $user->notify(new LevelAdvancementNotification($newLevel, $bonus));

            // Award upline mentorship points
            $this->awardUplineForAdvancement($user);

            // Fire event for points system
            event(new \App\Events\UserLevelAdvanced($user, $oldLevel, $newLevel));

            // Log event
            Log::info("User level advanced", [
                'user_id' => $user->id,
                'old_level' => $oldLevel,
                'new_level' => $newLevel,
                'bonus' => $bonus,
            ]);

            return $newLevel;
        });
    }

    /**
     * Get milestone bonus for level
     */
    protected function getMilestoneBonus(string $level): array
    {
        return match ($level) {
            'professional' => ['cash' => 500, 'lp' => 100],
            'senior' => ['cash' => 1500, 'lp' => 200],
            'manager' => ['cash' => 5000, 'lp' => 500],
            'director' => ['cash' => 15000, 'lp' => 1000],
            'executive' => ['cash' => 50000, 'lp' => 2500],
            'ambassador' => ['cash' => 150000, 'lp' => 5000],
            default => ['cash' => 0, 'lp' => 0],
        };
    }

    /**
     * Award milestone bonus to user
     */
    protected function awardMilestoneBonus(User $user, string $level, array $bonus): void
    {
        // Award cash bonus
        if ($bonus['cash'] > 0) {
            $user->increment('balance', $bonus['cash']);

            // Create transaction record
            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'type' => 'milestone_bonus',
                'amount' => $bonus['cash'],
                'status' => 'completed',
                'description' => "Milestone bonus for reaching {$level} level",
            ]);
        }

        // Award LP bonus
        if ($bonus['lp'] > 0) {
            app(PointService::class)->awardPoints(
                $user,
                'level_advancement',
                $bonus['lp'],
                0,
                "Level advancement bonus: {$level}"
            );
        }
    }

    /**
     * Award points to upline for member advancement
     */
    protected function awardUplineForAdvancement(User $user): void
    {
        if ($user->referrer) {
            app(PointService::class)->awardPoints(
                $user->referrer,
                'downline_advancement',
                50,
                50,
                "Downline member {$user->name} advanced to {$user->current_professional_level}",
                $user
            );
        }
    }

    /**
     * Get progress towards next level
     */
    public function getLevelProgress(User $user): array
    {
        $currentLevel = $user->current_professional_level;
        $nextLevel = $this->getNextLevel($currentLevel);

        if (!$nextLevel) {
            return [
                'current_level' => $currentLevel,
                'next_level' => null,
                'progress' => 100,
                'requirements' => [],
                'met' => [],
            ];
        }

        $requirements = $this->getLevelRequirements($nextLevel);
        $met = [];

        // Check each requirement
        $met['lp'] = [
            'required' => $requirements['lp'],
            'current' => $user->points?->lifetime_points ?? 0,
            'met' => ($user->points?->lifetime_points ?? 0) >= $requirements['lp'],
            'progress' => min(100, (($user->points?->lifetime_points ?? 0) / $requirements['lp']) * 100),
        ];

        $met['days'] = [
            'required' => $requirements['min_days'],
            'current' => $user->account_age_days,
            'met' => $user->account_age_days >= $requirements['min_days'],
            'progress' => min(100, ($user->account_age_days / $requirements['min_days']) * 100),
        ];

        $met['referrals'] = [
            'required' => $requirements['direct_referrals'],
            'current' => $user->referral_count,
            'met' => $user->referral_count >= $requirements['direct_referrals'],
            'progress' => min(100, ($user->referral_count / $requirements['direct_referrals']) * 100),
        ];

        $met['active_referrals'] = [
            'required' => $requirements['active_referrals'],
            'current' => $user->active_direct_referrals_count,
            'met' => $user->active_direct_referrals_count >= $requirements['active_referrals'],
            'progress' => $requirements['active_referrals'] > 0 
                ? min(100, ($user->active_direct_referrals_count / $requirements['active_referrals']) * 100)
                : 100,
        ];

        $met['courses'] = [
            'required' => $requirements['courses'],
            'current' => $user->courses_completed_count,
            'met' => $user->courses_completed_count >= $requirements['courses'],
            'progress' => $requirements['courses'] > 0
                ? min(100, ($user->courses_completed_count / $requirements['courses']) * 100)
                : 100,
        ];

        if ($requirements['downline_level']) {
            $met['downline_level'] = [
                'required' => $requirements['downline_level'],
                'met' => $this->hasDownlineAtLevel($user, $requirements['downline_level']),
                'progress' => $this->hasDownlineAtLevel($user, $requirements['downline_level']) ? 100 : 0,
            ];
        }

        // Calculate overall progress
        $totalProgress = collect($met)->avg('progress');

        return [
            'current_level' => $currentLevel,
            'next_level' => $nextLevel,
            'progress' => round($totalProgress, 2),
            'requirements' => $requirements,
            'met' => $met,
            'all_met' => $this->meetsAllRequirements($user, $requirements),
        ];
    }
}
