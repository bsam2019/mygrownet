<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\Leaderboard;
use App\Models\LeaderboardEntry;
use App\Models\IncentiveProgram;
use App\Models\RecognitionEvent;
use App\Models\Certificate;
use App\Models\User;
use App\Services\RecognitionIncentiveService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    public function __construct(
        private RecognitionIncentiveService $recognitionIncentiveService
    ) {}
    /**
     * Check and award achievements for a user based on their current status
     */
    public function checkAndAwardAchievements(User $user, string $triggerEvent = null): array
    {
        $awardedAchievements = [];
        
        // Get available achievements for the user
        $availableAchievements = Achievement::active()
            ->available()
            ->get()
            ->filter(function ($achievement) use ($user) {
                return $achievement->isAvailableForUser($user);
            });

        foreach ($availableAchievements as $achievement) {
            $requirements = $achievement->checkRequirements($user);
            
            if ($requirements['eligible']) {
                try {
                    $userAchievement = $achievement->awardToUser($user, [
                        'earning_context' => [
                            'trigger_event' => $triggerEvent,
                            'requirements_met' => $requirements['requirements_met']
                        ]
                    ]);
                    
                    $awardedAchievements[] = $userAchievement;
                    
                    // Trigger recognition for significant achievements
                    $this->recognitionIncentiveService->triggerAchievementRecognition($user, $achievement);
                    
                    Log::info('Achievement awarded', [
                        'user_id' => $user->id,
                        'achievement_id' => $achievement->id,
                        'achievement_name' => $achievement->name,
                        'trigger_event' => $triggerEvent
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error('Failed to award achievement', [
                        'user_id' => $user->id,
                        'achievement_id' => $achievement->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $awardedAchievements;
    }

    /**
     * Get user's achievement progress and statistics
     */
    public function getUserAchievementStats(User $user): array
    {
        $userAchievements = $user->userAchievements()->with('achievement')->get();
        $totalAchievements = Achievement::active()->count();
        
        $stats = [
            'total_earned' => $userAchievements->count(),
            'total_available' => $totalAchievements,
            'completion_percentage' => $totalAchievements > 0 ? ($userAchievements->count() / $totalAchievements) * 100 : 0,
            'total_points' => $userAchievements->sum(fn($ua) => $ua->achievement->points),
            'by_category' => $userAchievements->groupBy('achievement.category')->map->count(),
            'by_difficulty' => $userAchievements->groupBy('achievement.difficulty_level')->map->count(),
            'recent_achievements' => $userAchievements->sortByDesc('earned_at')->take(5)->values(),
            'next_achievements' => $this->getNextAvailableAchievements($user, 5)
        ];

        return $stats;
    }

    /**
     * Get next available achievements for a user
     */
    public function getNextAvailableAchievements(User $user, int $limit = 10): Collection
    {
        return Achievement::active()
            ->available()
            ->get()
            ->filter(function ($achievement) use ($user) {
                return $achievement->isAvailableForUser($user);
            })
            ->map(function ($achievement) use ($user) {
                $requirements = $achievement->checkRequirements($user);
                $achievement->progress = $requirements['progress'];
                $achievement->missing_requirements = $requirements['missing_requirements'];
                return $achievement;
            })
            ->sortByDesc('progress')
            ->take($limit);
    }

    /**
     * Update all leaderboards
     */
    public function updateAllLeaderboards(): array
    {
        $results = [];
        $leaderboards = Leaderboard::active()->autoRefresh()->get();

        foreach ($leaderboards as $leaderboard) {
            try {
                $rankings = $leaderboard->calculateAndUpdateRankings();
                $results[] = [
                    'leaderboard_id' => $leaderboard->id,
                    'leaderboard_name' => $leaderboard->name,
                    'status' => 'success',
                    'entries_updated' => count($rankings)
                ];
                
                Log::info('Leaderboard updated', [
                    'leaderboard_id' => $leaderboard->id,
                    'entries_count' => count($rankings)
                ]);
                
            } catch (\Exception $e) {
                $results[] = [
                    'leaderboard_id' => $leaderboard->id,
                    'leaderboard_name' => $leaderboard->name,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
                
                Log::error('Failed to update leaderboard', [
                    'leaderboard_id' => $leaderboard->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Get user's leaderboard positions across all leaderboards
     */
    public function getUserLeaderboardPositions(User $user): array
    {
        $positions = [];
        
        $entries = LeaderboardEntry::forUser($user)
            ->with('leaderboard')
            ->orderBy('position')
            ->get();

        foreach ($entries as $entry) {
            $positions[] = [
                'leaderboard_name' => $entry->leaderboard->name,
                'leaderboard_type' => $entry->leaderboard->type,
                'position' => $entry->position,
                'score' => $entry->score,
                'tier' => $entry->tier_at_entry,
                'trend' => $entry->trend,
                'position_change' => $entry->getPositionChange(),
                'is_top_position' => $entry->isTopPosition()
            ];
        }

        return $positions;
    }

    /**
     * Get leaderboard with top entries
     */
    public function getLeaderboardWithEntries(string $slug, int $limit = 25): array
    {
        $leaderboard = Leaderboard::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$leaderboard) {
            throw new \Exception('Leaderboard not found.');
        }

        $entries = $leaderboard->topEntries($limit)
            ->with('user')
            ->get()
            ->map(function ($entry) {
                return $entry->getEntrySummary();
            });

        return [
            'leaderboard' => $leaderboard->getLeaderboardSummary(),
            'entries' => $entries,
            'total_entries' => $leaderboard->entries()->count(),
            'last_updated' => $leaderboard->last_updated
        ];
    }

    /**
     * Award leaderboard rewards to top performers
     */
    public function awardLeaderboardRewards(Leaderboard $leaderboard): array
    {
        if (empty($leaderboard->rewards)) {
            return [];
        }

        $results = [];
        
        foreach ($leaderboard->rewards as $position => $reward) {
            $entry = $leaderboard->entries()
                ->where('position', $position)
                ->with('user')
                ->first();

            if ($entry) {
                try {
                    $this->awardReward($entry->user, $reward, $leaderboard);
                    $results[] = [
                        'position' => $position,
                        'user_id' => $entry->user_id,
                        'user_name' => $entry->user->name,
                        'reward' => $reward,
                        'status' => 'awarded'
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'position' => $position,
                        'user_id' => $entry->user_id,
                        'error' => $e->getMessage(),
                        'status' => 'failed'
                    ];
                }
            }
        }

        return $results;
    }

    private function awardReward(User $user, array $reward, Leaderboard $leaderboard): void
    {
        if ($reward['type'] === 'monetary') {
            Transaction::create([
                'user_id' => $user->id,
                'amount' => $reward['amount'],
                'transaction_type' => 'leaderboard_reward',
                'status' => 'completed',
                'description' => "Leaderboard reward: {$leaderboard->name} - {$reward['description']}",
                'reference_number' => 'LB-' . $leaderboard->id . '-' . $user->id . '-' . time(),
                'processed_at' => now()
            ]);

            $user->increment('total_earnings', $reward['amount']);
        }
    }

    /**
     * Get gamification dashboard data for a user
     */
    public function getUserGamificationDashboard(User $user): array
    {
        return [
            'achievement_stats' => $this->getUserAchievementStats($user),
            'leaderboard_positions' => $this->getUserLeaderboardPositions($user),
            'recent_activities' => $this->getRecentGamificationActivities($user),
            'tier_progress' => $this->getTierProgressInfo($user),
            'upcoming_rewards' => $this->getUpcomingRewards($user),
            'incentive_summary' => $this->recognitionIncentiveService->getUserIncentiveSummary($user),
            'recognition_stats' => $this->getUserRecognitionStats($user)
        ];
    }

    private function getRecentGamificationActivities(User $user): array
    {
        $activities = [];

        // Recent achievements
        $recentAchievements = $user->userAchievements()
            ->with('achievement')
            ->where('earned_at', '>=', now()->subDays(30))
            ->orderBy('earned_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($recentAchievements as $userAchievement) {
            $activities[] = [
                'type' => 'achievement',
                'title' => "Earned '{$userAchievement->achievement->name}' achievement",
                'description' => $userAchievement->achievement->description,
                'points' => $userAchievement->achievement->points,
                'date' => $userAchievement->earned_at,
                'icon' => $userAchievement->achievement->badge_icon,
                'color' => $userAchievement->achievement->badge_color
            ];
        }

        // Recent leaderboard movements
        $recentEntries = LeaderboardEntry::forUser($user)
            ->with('leaderboard')
            ->where('calculated_at', '>=', now()->subDays(7))
            ->where('trend', '!=', 'same')
            ->orderBy('calculated_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentEntries as $entry) {
            $activities[] = [
                'type' => 'leaderboard',
                'title' => "Moved {$entry->trend} in {$entry->leaderboard->name}",
                'description' => "Position #{$entry->position} (Score: {$entry->score})",
                'date' => $entry->calculated_at,
                'icon' => $entry->getTrendIcon(),
                'color' => $entry->getTrendColor()
            ];
        }

        // Sort by date descending
        usort($activities, fn($a, $b) => $b['date'] <=> $a['date']);

        return array_slice($activities, 0, 15);
    }

    private function getTierProgressInfo(User $user): array
    {
        $currentTier = $user->currentTier;
        if (!$currentTier) {
            return [];
        }

        // Get next tier requirements
        $nextTierRequirements = $user->getNextTierRequirements();
        
        return [
            'current_tier' => $currentTier->name,
            'next_tier' => $nextTierRequirements['next_tier']?->name,
            'progress_percentage' => $this->calculateTierProgress($user, $nextTierRequirements),
            'requirements' => $nextTierRequirements
        ];
    }

    private function calculateTierProgress(User $user, array $requirements): float
    {
        if (!isset($requirements['next_tier'])) {
            return 100; // Already at highest tier
        }

        $nextTier = $requirements['next_tier'];
        $currentReferrals = $user->referral_count ?? 0;
        $currentTeamVolume = $user->teamVolumes()->sum('team_volume') ?? 0;

        $referralProgress = $nextTier->required_active_referrals > 0 
            ? min(($currentReferrals / $nextTier->required_active_referrals) * 100, 100)
            : 100;

        $volumeProgress = $nextTier->required_team_volume > 0
            ? min(($currentTeamVolume / $nextTier->required_team_volume) * 100, 100)
            : 100;

        return ($referralProgress + $volumeProgress) / 2;
    }

    private function getUpcomingRewards(User $user): array
    {
        $rewards = [];

        // Check for achievements close to completion
        $nearCompletionAchievements = $this->getNextAvailableAchievements($user, 10)
            ->filter(function ($achievement) {
                return $achievement->progress >= 50; // 50% or more complete
            });

        foreach ($nearCompletionAchievements as $achievement) {
            $rewards[] = [
                'type' => 'achievement',
                'name' => $achievement->name,
                'description' => $achievement->description,
                'progress' => $achievement->progress,
                'reward_amount' => $achievement->monetary_reward,
                'points' => $achievement->points
            ];
        }

        return $rewards;
    }

    /**
     * Trigger achievement checks based on specific events
     */
    public function triggerAchievementCheck(User $user, string $event, array $eventData = []): array
    {
        return $this->checkAndAwardAchievements($user, $event);
    }

    /**
     * Get global gamification statistics
     */
    public function getGlobalGamificationStats(): array
    {
        return Cache::remember('global_gamification_stats', 3600, function () {
            return [
                'total_achievements_earned' => UserAchievement::count(),
                'total_active_users' => User::whereHas('userAchievements')->count(),
                'most_popular_achievements' => UserAchievement::select('achievement_id', DB::raw('count(*) as count'))
                    ->groupBy('achievement_id')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->with('achievement')
                    ->get(),
                'leaderboard_stats' => Leaderboard::active()->get()->map(function ($leaderboard) {
                    return [
                        'name' => $leaderboard->name,
                        'type' => $leaderboard->type,
                        'total_entries' => $leaderboard->entries()->count(),
                        'last_updated' => $leaderboard->last_updated
                    ];
                }),
                'incentive_stats' => [
                    'active_programs' => IncentiveProgram::active()->count(),
                    'total_participants' => DB::table('incentive_participations')->count(),
                    'total_rewards_awarded' => DB::table('incentive_winners')->where('status', 'awarded')->sum('reward_amount'),
                    'upcoming_events' => RecognitionEvent::upcoming()->count()
                ],
                'recognition_stats' => [
                    'certificates_issued' => Certificate::where('status', 'issued')->count(),
                    'recognition_events_held' => RecognitionEvent::past()->count(),
                    'total_awards_given' => DB::table('recognition_awards')->count()
                ]
            ];
        });
    }

    /**
     * Get user's recognition statistics
     */
    private function getUserRecognitionStats(User $user): array
    {
        return [
            'certificates_earned' => Certificate::forUser($user)->count(),
            'recognition_events_attended' => DB::table('recognition_event_attendees')
                ->where('user_id', $user->id)
                ->where('attendance_status', 'attended')
                ->count(),
            'awards_received' => DB::table('recognition_awards')
                ->where('user_id', $user->id)
                ->count(),
            'total_recognition_value' => DB::table('recognition_awards')
                ->where('user_id', $user->id)
                ->sum('award_value')
        ];
    }
}