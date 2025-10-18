<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'badge_icon',
        'badge_color',
        'points',
        'requirements',
        'is_repeatable',
        'max_times',
        'is_active',
        'tier_requirement',
        'monetary_reward',
        'tier_specific_rewards',
        'triggers_celebration',
        'celebration_message',
        'counts_for_leaderboard',
        'leaderboard_weight',
        'difficulty_level',
        'has_progress_tracking',
        'progress_milestones',
        'progress_unit',
        'prerequisite_achievements',
        'unlocks_achievements',
        'available_from',
        'available_until',
        'is_seasonal',
        'is_shareable',
        'share_message'
    ];

    protected $casts = [
        'requirements' => 'array',
        'tier_specific_rewards' => 'array',
        'progress_milestones' => 'array',
        'prerequisite_achievements' => 'array',
        'unlocks_achievements' => 'array',
        'is_repeatable' => 'boolean',
        'is_active' => 'boolean',
        'triggers_celebration' => 'boolean',
        'counts_for_leaderboard' => 'boolean',
        'has_progress_tracking' => 'boolean',
        'is_seasonal' => 'boolean',
        'is_shareable' => 'boolean',
        'monetary_reward' => 'decimal:2',
        'available_from' => 'date',
        'available_until' => 'date'
    ];

    protected $attributes = [
        'requirements' => '[]',
        'tier_specific_rewards' => '[]',
        'progress_milestones' => '[]',
        'prerequisite_achievements' => '[]',
        'unlocks_achievements' => '[]'
    ];

    // Relationships
    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
                    ->withPivot(['earned_at', 'progress', 'times_earned', 'tier_at_earning'])
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeForTier(Builder $query, string $tier): Builder
    {
        return $query->where(function ($q) use ($tier) {
            $q->whereNull('tier_requirement')
              ->orWhere('tier_requirement', $tier)
              ->orWhere('tier_requirement', '<=', $this->getTierLevel($tier));
        });
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('available_from')
              ->orWhere('available_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('available_until')
              ->orWhere('available_until', '>=', now());
        });
    }

    public function scopeForLeaderboard(Builder $query): Builder
    {
        return $query->where('counts_for_leaderboard', true);
    }

    public function scopeByDifficulty(Builder $query, string $difficulty): Builder
    {
        return $query->where('difficulty_level', $difficulty);
    }

    // Business Logic Methods
    public function isAvailableForUser(User $user): bool
    {
        // Check if achievement is active
        if (!$this->is_active) {
            return false;
        }

        // Check date availability
        if ($this->available_from && now()->lt($this->available_from)) {
            return false;
        }

        if ($this->available_until && now()->gt($this->available_until)) {
            return false;
        }

        // Check tier requirement
        if ($this->tier_requirement) {
            $userTier = $user->currentTier?->name;
            if (!$userTier || !$this->meetsMinimumTier($userTier)) {
                return false;
            }
        }

        // Check prerequisites
        if (!empty($this->prerequisite_achievements)) {
            foreach ($this->prerequisite_achievements as $prerequisiteId) {
                if (!$user->hasAchievement($prerequisiteId)) {
                    return false;
                }
            }
        }

        // Check if repeatable or not already earned
        if (!$this->is_repeatable) {
            return !$user->hasAchievement($this->id);
        }

        // Check max times for repeatable achievements
        if ($this->max_times) {
            $timesEarned = $user->getAchievementCount($this->id);
            return $timesEarned < $this->max_times;
        }

        return true;
    }

    public function checkRequirements(User $user): array
    {
        $results = [
            'eligible' => false,
            'progress' => 0,
            'requirements_met' => [],
            'missing_requirements' => []
        ];

        if (empty($this->requirements)) {
            $results['eligible'] = true;
            return $results;
        }

        $totalRequirements = count($this->requirements);
        $metRequirements = 0;

        foreach ($this->requirements as $requirement) {
            $met = $this->checkSingleRequirement($user, $requirement);
            
            if ($met['status']) {
                $metRequirements++;
                $results['requirements_met'][] = $requirement;
            } else {
                $results['missing_requirements'][] = array_merge($requirement, [
                    'current_value' => $met['current_value'],
                    'required_value' => $met['required_value']
                ]);
            }
        }

        $results['progress'] = $totalRequirements > 0 ? ($metRequirements / $totalRequirements) * 100 : 0;
        $results['eligible'] = $metRequirements === $totalRequirements;

        return $results;
    }

    private function checkSingleRequirement(User $user, array $requirement): array
    {
        $type = $requirement['type'];
        $value = $requirement['value'];
        $operator = $requirement['operator'] ?? '>=';

        $currentValue = match ($type) {
            'referral_count' => $user->referral_count ?? 0,
            'team_volume' => $user->teamVolumes()->sum('team_volume') ?? 0,
            'tier_level' => $this->getTierLevel($user->currentTier?->name ?? 'Bronze'),
            'course_completions' => $user->courseCompletions()->count(),
            'project_contributions' => $user->projectContributions()->confirmed()->count(),
            'total_earnings' => $user->total_earnings ?? 0,
            'consecutive_months' => $this->getConsecutiveActiveMonths($user),
            'community_votes' => $user->projectVotes()->count(),
            'mentorship_sessions' => $this->getMentorshipSessions($user),
            'asset_rewards' => $user->physicalRewardAllocations()->where('status', 'completed')->count(),
            default => 0
        };

        $met = match ($operator) {
            '>=' => $currentValue >= $value,
            '>' => $currentValue > $value,
            '=' => $currentValue == $value,
            '<=' => $currentValue <= $value,
            '<' => $currentValue < $value,
            default => false
        };

        return [
            'status' => $met,
            'current_value' => $currentValue,
            'required_value' => $value
        ];
    }

    public function awardToUser(User $user, array $additionalData = []): UserAchievement
    {
        if (!$this->isAvailableForUser($user)) {
            throw new \Exception('Achievement is not available for this user.');
        }

        $requirements = $this->checkRequirements($user);
        if (!$requirements['eligible']) {
            throw new \Exception('User does not meet achievement requirements.');
        }

        $userAchievement = UserAchievement::create(array_merge([
            'user_id' => $user->id,
            'achievement_id' => $this->id,
            'earned_at' => now(),
            'tier_at_earning' => $user->currentTier?->name,
            'progress' => 100,
            'times_earned' => $user->getAchievementCount($this->id) + 1
        ], $additionalData));

        // Award monetary reward if applicable
        if ($this->monetary_reward > 0) {
            $this->awardMonetaryReward($user);
        }

        // Award tier-specific rewards
        if (!empty($this->tier_specific_rewards)) {
            $this->awardTierSpecificRewards($user);
        }

        // Unlock dependent achievements
        if (!empty($this->unlocks_achievements)) {
            $this->unlockDependentAchievements($user);
        }

        // Trigger celebration if configured
        if ($this->triggers_celebration) {
            $this->triggerCelebration($user);
        }

        return $userAchievement;
    }

    private function awardMonetaryReward(User $user): void
    {
        Transaction::create([
            'user_id' => $user->id,
            'amount' => $this->monetary_reward,
            'transaction_type' => 'achievement_reward',
            'status' => 'completed',
            'description' => "Achievement reward: {$this->name}",
            'reference_number' => 'ACH-' . $this->id . '-' . $user->id . '-' . time(),
            'processed_at' => now()
        ]);

        $user->increment('total_earnings', $this->monetary_reward);
    }

    private function awardTierSpecificRewards(User $user): void
    {
        $tierName = $user->currentTier?->name ?? 'Bronze';
        $tierRewards = $this->tier_specific_rewards[$tierName] ?? [];

        foreach ($tierRewards as $reward) {
            // Process different types of tier-specific rewards
            match ($reward['type']) {
                'bonus_points' => $user->increment('achievement_points', $reward['value']),
                'course_unlock' => $this->unlockCourse($user, $reward['course_id']),
                'asset_bonus' => $this->applyAssetBonus($user, $reward['percentage']),
                default => null
            };
        }
    }

    private function unlockDependentAchievements(User $user): void
    {
        foreach ($this->unlocks_achievements as $achievementId) {
            $achievement = Achievement::find($achievementId);
            if ($achievement && $achievement->isAvailableForUser($user)) {
                $requirements = $achievement->checkRequirements($user);
                if ($requirements['eligible']) {
                    $achievement->awardToUser($user);
                }
            }
        }
    }

    private function triggerCelebration(User $user): void
    {
        // This would typically trigger a notification or event
        // For now, we'll just log it
        \Log::info('Achievement celebration triggered', [
            'user_id' => $user->id,
            'achievement_id' => $this->id,
            'achievement_name' => $this->name,
            'celebration_message' => $this->celebration_message
        ]);
    }

    // Helper methods
    private function getTierLevel(string $tierName): int
    {
        return match ($tierName) {
            'Bronze' => 1,
            'Silver' => 2,
            'Gold' => 3,
            'Diamond' => 4,
            'Elite' => 5,
            default => 0
        };
    }

    private function meetsMinimumTier(string $userTier): bool
    {
        return $this->getTierLevel($userTier) >= $this->getTierLevel($this->tier_requirement);
    }

    private function getConsecutiveActiveMonths(User $user): int
    {
        // This would calculate consecutive months of activity
        // Simplified implementation
        return $user->created_at->diffInMonths(now());
    }

    private function getMentorshipSessions(User $user): int
    {
        // This would count mentorship sessions
        // Placeholder implementation
        return 0;
    }

    // Static methods for achievement categories
    public static function getCategories(): array
    {
        return [
            'referral' => 'Referral Achievements',
            'subscription' => 'Subscription Milestones',
            'education' => 'Educational Progress',
            'community' => 'Community Participation',
            'mentorship' => 'Mentorship Activities',
            'tier_advancement' => 'Tier Advancement',
            'team_building' => 'Team Building',
            'project_contribution' => 'Project Contributions',
            'leadership' => 'Leadership Excellence',
            'financial_milestone' => 'Financial Milestones',
            'course_completion' => 'Course Completions',
            'voting_participation' => 'Voting Participation',
            'asset_rewards' => 'Asset Rewards',
            'profit_sharing' => 'Profit Sharing'
        ];
    }

    public static function getDifficultyLevels(): array
    {
        return [
            'easy' => ['name' => 'Easy', 'points_multiplier' => 1.0, 'color' => '#10b981'],
            'medium' => ['name' => 'Medium', 'points_multiplier' => 1.5, 'color' => '#f59e0b'],
            'hard' => ['name' => 'Hard', 'points_multiplier' => 2.0, 'color' => '#ef4444'],
            'legendary' => ['name' => 'Legendary', 'points_multiplier' => 3.0, 'color' => '#8b5cf6']
        ];
    }

    public static function createMyGrowNetAchievements(): array
    {
        $achievements = [
            // Referral Achievements
            [
                'name' => 'First Referral',
                'slug' => 'first-referral',
                'description' => 'Successfully refer your first member to MyGrowNet',
                'category' => 'referral',
                'badge_icon' => 'user-plus',
                'badge_color' => '#10b981',
                'points' => 100,
                'requirements' => [['type' => 'referral_count', 'operator' => '>=', 'value' => 1]],
                'monetary_reward' => 500,
                'triggers_celebration' => true,
                'celebration_message' => 'Congratulations on your first referral! Welcome to the MyGrowNet family!',
                'difficulty_level' => 'easy'
            ],
            [
                'name' => 'Team Builder',
                'slug' => 'team-builder',
                'description' => 'Build a team of 10 active referrals',
                'category' => 'team_building',
                'badge_icon' => 'users',
                'badge_color' => '#3b82f6',
                'points' => 500,
                'requirements' => [['type' => 'referral_count', 'operator' => '>=', 'value' => 10]],
                'monetary_reward' => 2000,
                'difficulty_level' => 'medium'
            ],
            // Tier Advancement Achievements
            [
                'name' => 'Silver Achiever',
                'slug' => 'silver-achiever',
                'description' => 'Advance to Silver tier membership',
                'category' => 'tier_advancement',
                'badge_icon' => 'star',
                'badge_color' => '#6b7280',
                'points' => 300,
                'requirements' => [['type' => 'tier_level', 'operator' => '>=', 'value' => 2]],
                'monetary_reward' => 1000,
                'tier_requirement' => 'Silver',
                'difficulty_level' => 'medium'
            ],
            [
                'name' => 'Elite Status',
                'slug' => 'elite-status',
                'description' => 'Reach the prestigious Elite tier',
                'category' => 'tier_advancement',
                'badge_icon' => 'crown',
                'badge_color' => '#8b5cf6',
                'points' => 2000,
                'requirements' => [['type' => 'tier_level', 'operator' => '>=', 'value' => 5]],
                'monetary_reward' => 10000,
                'tier_requirement' => 'Elite',
                'triggers_celebration' => true,
                'celebration_message' => 'Welcome to Elite status! You are now part of the top tier of MyGrowNet leaders!',
                'difficulty_level' => 'legendary'
            ],
            // Educational Achievements
            [
                'name' => 'Knowledge Seeker',
                'slug' => 'knowledge-seeker',
                'description' => 'Complete your first educational course',
                'category' => 'course_completion',
                'badge_icon' => 'academic-cap',
                'badge_color' => '#059669',
                'points' => 150,
                'requirements' => [['type' => 'course_completions', 'operator' => '>=', 'value' => 1]],
                'monetary_reward' => 300,
                'difficulty_level' => 'easy'
            ],
            [
                'name' => 'Scholar',
                'slug' => 'scholar',
                'description' => 'Complete 10 educational courses',
                'category' => 'course_completion',
                'badge_icon' => 'book-open',
                'badge_color' => '#0ea5e9',
                'points' => 800,
                'requirements' => [['type' => 'course_completions', 'operator' => '>=', 'value' => 10]],
                'monetary_reward' => 2500,
                'difficulty_level' => 'hard'
            ],
            // Community Participation
            [
                'name' => 'Community Contributor',
                'slug' => 'community-contributor',
                'description' => 'Make your first community project contribution',
                'category' => 'project_contribution',
                'badge_icon' => 'heart',
                'badge_color' => '#f59e0b',
                'points' => 200,
                'requirements' => [['type' => 'project_contributions', 'operator' => '>=', 'value' => 1]],
                'monetary_reward' => 750,
                'difficulty_level' => 'easy'
            ],
            [
                'name' => 'Voice of the Community',
                'slug' => 'voice-of-community',
                'description' => 'Participate in 5 community project votes',
                'category' => 'voting_participation',
                'badge_icon' => 'hand-raised',
                'badge_color' => '#dc2626',
                'points' => 300,
                'requirements' => [['type' => 'community_votes', 'operator' => '>=', 'value' => 5]],
                'monetary_reward' => 1000,
                'difficulty_level' => 'medium'
            ],
            // Financial Milestones
            [
                'name' => 'First Earnings',
                'slug' => 'first-earnings',
                'description' => 'Earn your first K1,000 in the MyGrowNet system',
                'category' => 'financial_milestone',
                'badge_icon' => 'currency-dollar',
                'badge_color' => '#10b981',
                'points' => 250,
                'requirements' => [['type' => 'total_earnings', 'operator' => '>=', 'value' => 1000]],
                'monetary_reward' => 500,
                'difficulty_level' => 'easy'
            ],
            [
                'name' => 'High Earner',
                'slug' => 'high-earner',
                'description' => 'Accumulate K50,000 in total earnings',
                'category' => 'financial_milestone',
                'badge_icon' => 'banknotes',
                'badge_color' => '#f59e0b',
                'points' => 1500,
                'requirements' => [['type' => 'total_earnings', 'operator' => '>=', 'value' => 50000]],
                'monetary_reward' => 5000,
                'difficulty_level' => 'hard'
            ]
        ];

        return $achievements;
    }
}