<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Leaderboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'period',
        'period_start',
        'period_end',
        'tier_restrictions',
        'category_filters',
        'max_positions',
        'rewards',
        'is_active',
        'auto_refresh',
        'last_updated'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'tier_restrictions' => 'array',
        'category_filters' => 'array',
        'rewards' => 'array',
        'is_active' => 'boolean',
        'auto_refresh' => 'boolean',
        'last_updated' => 'datetime'
    ];

    protected $attributes = [
        'tier_restrictions' => '[]',
        'category_filters' => '[]',
        'rewards' => '[]'
    ];

    // Relationships
    public function entries(): HasMany
    {
        return $this->hasMany(LeaderboardEntry::class)->orderBy('position');
    }

    public function topEntries(int $limit = 10): HasMany
    {
        return $this->hasMany(LeaderboardEntry::class)
                    ->orderBy('position')
                    ->limit($limit);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByPeriod(Builder $query, string $period): Builder
    {
        return $query->where('period', $period);
    }

    public function scopeAutoRefresh(Builder $query): Builder
    {
        return $query->where('auto_refresh', true);
    }

    // Business Logic Methods
    public function calculateAndUpdateRankings(): array
    {
        if (!$this->is_active) {
            throw new \Exception('Cannot update inactive leaderboard.');
        }

        $users = $this->getEligibleUsers();
        $rankings = [];

        foreach ($users as $user) {
            $score = $this->calculateUserScore($user);
            if ($score > 0) {
                $rankings[] = [
                    'user' => $user,
                    'score' => $score,
                    'score_breakdown' => $this->getScoreBreakdown($user)
                ];
            }
        }

        // Sort by score descending
        usort($rankings, fn($a, $b) => $b['score'] <=> $a['score']);

        // Limit to max positions
        $rankings = array_slice($rankings, 0, $this->max_positions);

        // Update leaderboard entries
        $this->updateLeaderboardEntries($rankings);

        $this->update(['last_updated' => now()]);

        return $rankings;
    }

    private function getEligibleUsers()
    {
        $query = User::query();

        // Apply tier restrictions
        if (!empty($this->tier_restrictions)) {
            $query->whereHas('currentTier', function ($q) {
                $q->whereIn('name', $this->tier_restrictions);
            });
        }

        // Apply period restrictions
        if ($this->period_start && $this->period_end) {
            $query->where('created_at', '<=', $this->period_end);
        }

        return $query->get();
    }

    private function calculateUserScore(User $user): float
    {
        $periodStart = $this->getPeriodStart();
        $periodEnd = $this->getPeriodEnd();

        return match ($this->type) {
            'achievements' => $this->calculateAchievementScore($user, $periodStart, $periodEnd),
            'referrals' => $this->calculateReferralScore($user, $periodStart, $periodEnd),
            'earnings' => $this->calculateEarningsScore($user, $periodStart, $periodEnd),
            'team_volume' => $this->calculateTeamVolumeScore($user, $periodStart, $periodEnd),
            'course_completions' => $this->calculateCourseCompletionScore($user, $periodStart, $periodEnd),
            'project_contributions' => $this->calculateProjectContributionScore($user, $periodStart, $periodEnd),
            default => 0
        };
    }

    private function calculateAchievementScore(User $user, Carbon $start, Carbon $end): float
    {
        $query = $user->userAchievements()
            ->whereBetween('earned_at', [$start, $end])
            ->with('achievement');

        // Apply category filters if specified
        if (!empty($this->category_filters)) {
            $query->whereHas('achievement', function ($q) {
                $q->whereIn('category', $this->category_filters);
            });
        }

        $achievements = $query->get();
        
        return $achievements->sum(function ($userAchievement) {
            $achievement = $userAchievement->achievement;
            $basePoints = $achievement->points * $achievement->leaderboard_weight;
            
            // Apply difficulty multiplier
            $difficultyMultipliers = Achievement::getDifficultyLevels();
            $multiplier = $difficultyMultipliers[$achievement->difficulty_level]['points_multiplier'] ?? 1.0;
            
            return $basePoints * $multiplier;
        });
    }

    private function calculateReferralScore(User $user, Carbon $start, Carbon $end): float
    {
        return $user->directReferrals()
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    private function calculateEarningsScore(User $user, Carbon $start, Carbon $end): float
    {
        return Transaction::where('user_id', $user->id)
            ->whereIn('transaction_type', ['commission', 'profit_share', 'project_return'])
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
    }

    private function calculateTeamVolumeScore(User $user, Carbon $start, Carbon $end): float
    {
        return $user->teamVolumes()
            ->whereBetween('created_at', [$start, $end])
            ->sum('team_volume');
    }

    private function calculateCourseCompletionScore(User $user, Carbon $start, Carbon $end): float
    {
        return $user->courseCompletions()
            ->whereBetween('completed_at', [$start, $end])
            ->count() * 10; // 10 points per course completion
    }

    private function calculateProjectContributionScore(User $user, Carbon $start, Carbon $end): float
    {
        return $user->projectContributions()
            ->confirmed()
            ->whereBetween('contributed_at', [$start, $end])
            ->sum('amount') / 1000; // 1 point per K1,000 contributed
    }

    private function getScoreBreakdown(User $user): array
    {
        // This would provide detailed breakdown of how the score was calculated
        // Implementation depends on leaderboard type
        return [
            'calculation_method' => $this->type,
            'period' => $this->period,
            'tier' => $user->currentTier?->name ?? 'Bronze'
        ];
    }

    private function updateLeaderboardEntries(array $rankings): void
    {
        DB::transaction(function () use ($rankings) {
            // Get existing entries for comparison
            $existingEntries = $this->entries()->with('user')->get()->keyBy('user_id');

            // Clear existing entries
            $this->entries()->delete();

            // Create new entries
            foreach ($rankings as $index => $ranking) {
                $user = $ranking['user'];
                $position = $index + 1;
                $score = $ranking['score'];
                
                $existingEntry = $existingEntries->get($user->id);
                $previousPosition = $existingEntry?->position;
                $previousScore = $existingEntry?->score ?? 0;
                
                $trend = $this->calculateTrend($position, $previousPosition);

                LeaderboardEntry::create([
                    'leaderboard_id' => $this->id,
                    'user_id' => $user->id,
                    'position' => $position,
                    'score' => $score,
                    'tier_at_entry' => $user->currentTier?->name,
                    'score_breakdown' => $ranking['score_breakdown'],
                    'previous_position' => $previousPosition,
                    'previous_score' => $previousScore,
                    'trend' => $trend,
                    'calculated_at' => now()
                ]);
            }
        });
    }

    private function calculateTrend(int $currentPosition, ?int $previousPosition): string
    {
        if ($previousPosition === null) {
            return 'new';
        }

        if ($currentPosition < $previousPosition) {
            return 'up';
        } elseif ($currentPosition > $previousPosition) {
            return 'down';
        } else {
            return 'same';
        }
    }

    private function getPeriodStart(): Carbon
    {
        if ($this->period_start) {
            return Carbon::parse($this->period_start);
        }

        return match ($this->period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            'quarterly' => now()->startOfQuarter(),
            'yearly' => now()->startOfYear(),
            'all_time' => Carbon::create(2020, 1, 1), // Platform start date
            default => now()->startOfMonth()
        };
    }

    private function getPeriodEnd(): Carbon
    {
        if ($this->period_end) {
            return Carbon::parse($this->period_end);
        }

        return match ($this->period) {
            'daily' => now()->endOfDay(),
            'weekly' => now()->endOfWeek(),
            'monthly' => now()->endOfMonth(),
            'quarterly' => now()->endOfQuarter(),
            'yearly' => now()->endOfYear(),
            'all_time' => now(),
            default => now()->endOfMonth()
        };
    }

    // Static methods for creating default leaderboards
    public static function createDefaultLeaderboards(): array
    {
        $leaderboards = [
            [
                'name' => 'Top Achievers',
                'slug' => 'top-achievers',
                'description' => 'Members with the most achievement points this month',
                'type' => 'achievements',
                'period' => 'monthly',
                'max_positions' => 50,
                'rewards' => [
                    '1' => ['type' => 'monetary', 'amount' => 5000, 'description' => 'K5,000 bonus'],
                    '2' => ['type' => 'monetary', 'amount' => 3000, 'description' => 'K3,000 bonus'],
                    '3' => ['type' => 'monetary', 'amount' => 2000, 'description' => 'K2,000 bonus']
                ]
            ],
            [
                'name' => 'Referral Champions',
                'slug' => 'referral-champions',
                'description' => 'Top referrers of the month',
                'type' => 'referrals',
                'period' => 'monthly',
                'max_positions' => 25,
                'rewards' => [
                    '1' => ['type' => 'monetary', 'amount' => 10000, 'description' => 'K10,000 bonus + Recognition'],
                    '2' => ['type' => 'monetary', 'amount' => 7500, 'description' => 'K7,500 bonus'],
                    '3' => ['type' => 'monetary', 'amount' => 5000, 'description' => 'K5,000 bonus']
                ]
            ],
            [
                'name' => 'Team Volume Leaders',
                'slug' => 'team-volume-leaders',
                'description' => 'Highest team volume generators this quarter',
                'type' => 'team_volume',
                'period' => 'quarterly',
                'max_positions' => 20,
                'tier_restrictions' => ['Gold', 'Diamond', 'Elite']
            ],
            [
                'name' => 'Learning Champions',
                'slug' => 'learning-champions',
                'description' => 'Most course completions this month',
                'type' => 'course_completions',
                'period' => 'monthly',
                'max_positions' => 30
            ],
            [
                'name' => 'Community Contributors',
                'slug' => 'community-contributors',
                'description' => 'Top community project contributors',
                'type' => 'project_contributions',
                'period' => 'quarterly',
                'max_positions' => 15,
                'tier_restrictions' => ['Silver', 'Gold', 'Diamond', 'Elite']
            ],
            [
                'name' => 'All-Time Legends',
                'slug' => 'all-time-legends',
                'description' => 'Highest earning members of all time',
                'type' => 'earnings',
                'period' => 'all_time',
                'max_positions' => 100
            ]
        ];

        $created = [];
        foreach ($leaderboards as $leaderboardData) {
            $created[] = self::create($leaderboardData);
        }

        return $created;
    }

    public function getLeaderboardSummary(): array
    {
        return [
            'leaderboard_id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'period' => $this->period,
            'total_entries' => $this->entries()->count(),
            'last_updated' => $this->last_updated,
            'top_3' => $this->topEntries(3)->with('user')->get()->map(function ($entry) {
                return [
                    'position' => $entry->position,
                    'user_name' => $entry->user->name,
                    'score' => $entry->score,
                    'tier' => $entry->tier_at_entry,
                    'trend' => $entry->trend
                ];
            }),
            'rewards' => $this->rewards
        ];
    }
}