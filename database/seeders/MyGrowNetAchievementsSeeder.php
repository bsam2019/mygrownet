<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Leaderboard;
use Illuminate\Database\Seeder;

class MyGrowNetAchievementsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAchievements();
        $this->seedLeaderboards();
    }

    private function seedAchievements(): void
    {
        $achievements = Achievement::createMyGrowNetAchievements();
        $created = 0;
        $skipped = 0;
        
        foreach ($achievements as $achievementData) {
            try {
                Achievement::updateOrCreate(
                    ['slug' => $achievementData['slug']],
                    $achievementData
                );
                $created++;
            } catch (\Exception $e) {
                $this->command->warn('Skipped achievement: ' . $achievementData['slug'] . ' - ' . $e->getMessage());
                $skipped++;
            }
        }

        $this->command->info("Created {$created} MyGrowNet achievements" . ($skipped > 0 ? " (skipped {$skipped})" : ''));
    }

    private function seedLeaderboards(): void
    {
        $leaderboardsData = [
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

        $created = 0;
        foreach ($leaderboardsData as $leaderboardData) {
            Leaderboard::updateOrCreate(
                ['slug' => $leaderboardData['slug']],
                $leaderboardData
            );
            $created++;
        }

        $this->command->info("Created/updated {$created} default leaderboards");
    }
}