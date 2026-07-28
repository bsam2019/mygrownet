<?php

namespace Tests\Feature\GrowNet;

use App\Models\User;
use App\Domain\GrowNet\Services\GamificationService;
use App\Infrastructure\Persistence\Eloquent\GrowNet\Achievement;
use App\Infrastructure\Persistence\Eloquent\GrowNet\UserAchievement;
use App\Infrastructure\Persistence\Eloquent\GrowNet\Leaderboard;
use App\Infrastructure\Persistence\Eloquent\GrowNet\LeaderboardEntry;
use Illuminate\Support\Facades\Cache;

class GamificationServiceTest extends GrowNetTestCase
{
    private GamificationService $gamification;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gamification = app(GamificationService::class);
    }

    public function test_get_user_achievement_stats_empty(): void
    {
        $stats = $this->gamification->getUserAchievementStats($this->user);

        $this->assertEquals(0, $stats['total_earned']);
        $this->assertEquals(0, $stats['total_available']);
        $this->assertEquals(0, $stats['completion_percentage']);
        $this->assertEquals(0, $stats['total_points']);
        $this->assertCount(0, $stats['by_category']);
        $this->assertCount(0, $stats['by_difficulty']);
        $this->assertCount(0, $stats['recent_achievements']);
    }

    public function test_get_user_achievement_stats_with_earned(): void
    {
        $achievement = Achievement::create([
            'name' => 'First Referral',
            'slug' => 'first-referral',
            'description' => 'Made your first referral',
            'category' => 'referral',
            'badge_icon' => 'star',
            'badge_color' => 'gold',
            'points' => 100,
            'difficulty_level' => 'easy',
            'is_active' => true,
        ]);

        UserAchievement::create([
            'user_id' => $this->user->id,
            'achievement_id' => $achievement->id,
            'earned_at' => now(),
            'progress' => 100,
        ]);

        $stats = $this->gamification->getUserAchievementStats($this->user);

        $this->assertEquals(1, $stats['total_earned']);
        $this->assertEquals(1, $stats['total_available']);
        $this->assertEquals(100.0, $stats['completion_percentage']);
        $this->assertEquals(100, $stats['total_points']);
        $this->assertEquals(['referral' => 1], $stats['by_category']->toArray());
        $this->assertEquals(['easy' => 1], $stats['by_difficulty']->toArray());
        $this->assertCount(1, $stats['recent_achievements']);
    }

    public function test_get_user_achievement_stats_partial_completion(): void
    {
        Achievement::create([
            'name' => 'First Referral',
            'slug' => 'first-referral',
            'description' => 'Made your first referral',
            'category' => 'referral',
            'badge_icon' => 'star',
            'badge_color' => 'gold',
            'points' => 100,
            'difficulty_level' => 'easy',
            'is_active' => true,
        ]);

        Achievement::create([
            'name' => 'Top Referrer',
            'slug' => 'top-referrer',
            'description' => 'Refer 10 members',
            'category' => 'referral',
            'badge_icon' => 'trophy',
            'badge_color' => 'silver',
            'points' => 200,
            'difficulty_level' => 'medium',
            'is_active' => true,
        ]);

        UserAchievement::create([
            'user_id' => $this->user->id,
            'achievement_id' => 1,
            'earned_at' => now(),
            'progress' => 100,
        ]);

        $stats = $this->gamification->getUserAchievementStats($this->user);

        $this->assertEquals(1, $stats['total_earned']);
        $this->assertEquals(2, $stats['total_available']);
        $this->assertEquals(50.0, $stats['completion_percentage']);
        $this->assertEquals(100, $stats['total_points']);
    }

    public function test_recent_achievements_sorted_by_earned_at(): void
    {
        $a1 = Achievement::create([
            'name' => 'First Referral', 'slug' => 'first-referral', 'description' => 'A',
            'category' => 'referral', 'badge_icon' => 'star', 'badge_color' => 'gold',
            'points' => 10, 'difficulty_level' => 'easy', 'is_active' => true,
        ]);
        $a2 = Achievement::create([
            'name' => 'Team Builder', 'slug' => 'team-builder', 'description' => 'B',
            'category' => 'team_building', 'badge_icon' => 'trophy', 'badge_color' => 'silver',
            'points' => 20, 'difficulty_level' => 'medium', 'is_active' => true,
        ]);

        UserAchievement::create([
            'user_id' => $this->user->id, 'achievement_id' => $a1->id,
            'earned_at' => now()->subDays(2), 'progress' => 100,
        ]);
        $ua2 = UserAchievement::create([
            'user_id' => $this->user->id, 'achievement_id' => $a2->id,
            'earned_at' => now()->subDay(), 'progress' => 100,
        ]);

        $stats = $this->gamification->getUserAchievementStats($this->user);
        $recent = $stats['recent_achievements'];

        $this->assertCount(2, $recent);
        $this->assertEquals($ua2->id, $recent[0]->id);
    }

    public function test_is_available_for_user_returns_true_for_active_achievement(): void
    {
        $achievement = Achievement::create([
            'name' => 'Active Achievement', 'slug' => 'active-achievement', 'description' => 'Available',
            'category' => 'referral', 'badge_icon' => 'star', 'badge_color' => 'gold',
            'points' => 100, 'is_active' => true,
        ]);

        $achievement->refresh();
        $this->assertTrue($achievement->isAvailableForUser($this->user));
    }



    public function test_get_user_leaderboard_positions_empty(): void
    {
        $positions = $this->gamification->getUserLeaderboardPositions($this->user);
        $this->assertCount(0, $positions);
    }

    public function test_get_user_leaderboard_positions_with_entry(): void
    {
        $leaderboard = Leaderboard::create([
            'name' => 'Top Earners',
            'slug' => 'top-earners',
            'description' => 'Top earning members',
            'type' => 'earnings',
            'period' => 'monthly',
            'is_active' => true,
            'auto_refresh' => false,
        ]);

        LeaderboardEntry::create([
            'user_id' => $this->user->id,
            'leaderboard_id' => $leaderboard->id,
            'position' => 1,
            'score' => 5000,
            'tier_at_entry' => 'elite',
            'trend' => 'up',
            'calculated_at' => now(),
        ]);

        $positions = $this->gamification->getUserLeaderboardPositions($this->user);

        $this->assertCount(1, $positions);
        $this->assertEquals('Top Earners', $positions[0]['leaderboard_name']);
        $this->assertEquals('earnings', $positions[0]['leaderboard_type']);
        $this->assertEquals(1, $positions[0]['position']);
        $this->assertEquals(5000, $positions[0]['score']);
        $this->assertEquals('up', $positions[0]['trend']);
    }

    public function test_get_user_gamification_dashboard_structure(): void
    {
        $dashboard = $this->gamification->getUserGamificationDashboard($this->user);

        $this->assertArrayHasKey('achievement_stats', $dashboard);
        $this->assertArrayHasKey('leaderboard_positions', $dashboard);
        $this->assertArrayHasKey('recent_activities', $dashboard);
        $this->assertArrayHasKey('tier_progress', $dashboard);
        $this->assertArrayHasKey('upcoming_rewards', $dashboard);
        $this->assertCount(0, $dashboard['leaderboard_positions']);
    }

    public function test_global_gamification_stats_structure(): void
    {
        Cache::flush();

        $stats = $this->gamification->getGlobalGamificationStats();

        $this->assertArrayHasKey('total_achievements_earned', $stats);
        $this->assertArrayHasKey('total_active_users', $stats);
        $this->assertArrayHasKey('most_popular_achievements', $stats);
        $this->assertArrayHasKey('leaderboard_stats', $stats);
        $this->assertArrayHasKey('incentive_stats', $stats);
        $this->assertArrayHasKey('recognition_stats', $stats);
    }

    public function test_leaderboard_not_found_throws_exception(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Leaderboard not found.');
        $this->gamification->getLeaderboardWithEntries('non-existent-slug');
    }

    public function test_leaderboard_with_entries(): void
    {
        $leaderboard = Leaderboard::create([
            'name' => 'Top Earners',
            'slug' => 'top-earners',
            'description' => 'Top earning members',
            'type' => 'earnings',
            'period' => 'monthly',
            'is_active' => true,
            'auto_refresh' => false,
        ]);

        LeaderboardEntry::create([
            'user_id' => $this->user->id,
            'leaderboard_id' => $leaderboard->id,
            'position' => 1,
            'score' => 5000,
            'tier_at_entry' => 'elite',
            'trend' => 'up',
            'calculated_at' => now(),
        ]);

        $result = $this->gamification->getLeaderboardWithEntries('top-earners');

        $this->assertEquals('Top Earners', $result['leaderboard']['name']);
        $this->assertCount(1, $result['entries']);
        $this->assertEquals(1, $result['total_entries']);
    }
}
