<?php

namespace Database\Seeders\GrowStart;

use Illuminate\Database\Seeder;
use App\Models\GrowStart\Badge;

class GrowStartBadgesSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Idea Champion',
                'slug' => 'idea-champion',
                'description' => 'Completed the Idea validation stage',
                'icon' => '🎯',
                'criteria_type' => 'stage_complete',
                'criteria_value' => 'idea',
                'points' => 10,
            ],
            [
                'name' => 'Plan Master',
                'slug' => 'plan-master',
                'description' => 'Completed the Planning stage with a solid business plan',
                'icon' => '📋',
                'criteria_type' => 'stage_complete',
                'criteria_value' => 'planning',
                'points' => 20,
            ],
            [
                'name' => 'Officially Registered',
                'slug' => 'officially-registered',
                'description' => 'Completed all business registration tasks',
                'icon' => '✅',
                'criteria_type' => 'stage_complete',
                'criteria_value' => 'registration',
                'points' => 30,
            ],
            [
                'name' => 'Launched',
                'slug' => 'launched',
                'description' => 'Successfully launched your business',
                'icon' => '🚀',
                'criteria_type' => 'stage_complete',
                'criteria_value' => 'launch',
                'points' => 40,
            ],
            [
                'name' => 'Growth Seeker',
                'slug' => 'growth-seeker',
                'description' => 'Completed the Growth stage tasks',
                'icon' => '📈',
                'criteria_type' => 'stage_complete',
                'criteria_value' => 'growth',
                'points' => 50,
            ],
            [
                'name' => 'Streak Star',
                'slug' => 'streak-star',
                'description' => 'Maintained 4 consecutive weeks of progress',
                'icon' => '🔥',
                'criteria_type' => 'streak_days',
                'criteria_value' => '28',
                'points' => 25,
            ],
            [
                'name' => 'Task Master',
                'slug' => 'task-master',
                'description' => 'Completed 25 tasks',
                'icon' => '⭐',
                'criteria_type' => 'tasks_complete',
                'criteria_value' => '25',
                'points' => 15,
            ],
            [
                'name' => 'Journey Complete',
                'slug' => 'journey-complete',
                'description' => 'Completed the entire startup journey',
                'icon' => '🏆',
                'criteria_type' => 'journey_complete',
                'criteria_value' => null,
                'points' => 100,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['slug' => $badge['slug']],
                $badge
            );
        }
    }
}
