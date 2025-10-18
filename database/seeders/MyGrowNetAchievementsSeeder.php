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
        
        foreach ($achievements as $achievementData) {
            Achievement::updateOrInsert(
                ['slug' => $achievementData['slug']],
                array_merge($achievementData, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        $this->command->info('Created ' . count($achievements) . ' MyGrowNet achievements');
    }

    private function seedLeaderboards(): void
    {
        $leaderboards = Leaderboard::createDefaultLeaderboards();
        
        $this->command->info('Created ' . count($leaderboards) . ' default leaderboards');
    }
}