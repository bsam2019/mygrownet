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
        $leaderboards = Leaderboard::createDefaultLeaderboards();
        $this->command->info('Created ' . count($leaderboards) . ' default leaderboards');
    }
}