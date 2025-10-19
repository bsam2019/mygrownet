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
            Achievement::updateOrCreate(
                ['slug' => $achievementData['slug']],
                $achievementData
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