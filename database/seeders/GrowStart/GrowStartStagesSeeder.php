<?php

namespace Database\Seeders\GrowStart;

use Illuminate\Database\Seeder;
use App\Models\GrowStart\Stage;

class GrowStartStagesSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            [
                'name' => 'Idea',
                'slug' => 'idea',
                'description' => 'Validate your business idea and identify your target market',
                'order' => 1,
                'icon' => '💡',
                'color' => '#fbbf24',
                'estimated_days' => 7,
            ],
            [
                'name' => 'Validation',
                'slug' => 'validation',
                'description' => 'Test your concept with potential customers and refine your value proposition',
                'order' => 2,
                'icon' => '✓',
                'color' => '#34d399',
                'estimated_days' => 14,
            ],
            [
                'name' => 'Planning',
                'slug' => 'planning',
                'description' => 'Create your business plan, financial projections, and operational strategy',
                'order' => 3,
                'icon' => '📋',
                'color' => '#60a5fa',
                'estimated_days' => 14,
            ],
            [
                'name' => 'Registration',
                'slug' => 'registration',
                'description' => 'Register your business with PACRA, ZRA, NAPSA and obtain necessary licenses',
                'order' => 4,
                'icon' => '📝',
                'color' => '#a78bfa',
                'estimated_days' => 21,
            ],
            [
                'name' => 'Launch',
                'slug' => 'launch',
                'description' => 'Set up operations, acquire initial inventory, and launch your business',
                'order' => 5,
                'icon' => '🚀',
                'color' => '#f472b6',
                'estimated_days' => 14,
            ],
            [
                'name' => 'Accounting',
                'slug' => 'accounting',
                'description' => 'Set up bookkeeping, invoicing, and financial management systems',
                'order' => 6,
                'icon' => '💰',
                'color' => '#22c55e',
                'estimated_days' => 7,
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'description' => 'Build your brand, create marketing materials, and acquire customers',
                'order' => 7,
                'icon' => '📣',
                'color' => '#f97316',
                'estimated_days' => 14,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'description' => 'Scale your operations, expand your team, and grow your customer base',
                'order' => 8,
                'icon' => '📈',
                'color' => '#06b6d4',
                'estimated_days' => 30,
            ],
        ];

        foreach ($stages as $stage) {
            Stage::updateOrCreate(
                ['slug' => $stage['slug']],
                $stage
            );
        }
    }
}
