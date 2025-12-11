<?php

namespace Database\Seeders\GrowStart;

use Illuminate\Database\Seeder;
use App\Models\GrowStart\Industry;

class GrowStartIndustriesSeeder extends Seeder
{
    public function run(): void
    {
        $industries = [
            [
                'name' => 'Agriculture',
                'slug' => 'agriculture',
                'description' => 'Farming, livestock, agro-processing, and agricultural services',
                'icon' => '🌾',
                'estimated_startup_cost_min' => 5000,
                'estimated_startup_cost_max' => 50000,
                'sort_order' => 1,
            ],
            [
                'name' => 'Retail',
                'slug' => 'retail',
                'description' => 'Shops, stores, and retail trading businesses',
                'icon' => '🏪',
                'estimated_startup_cost_min' => 10000,
                'estimated_startup_cost_max' => 100000,
                'sort_order' => 2,
            ],
            [
                'name' => 'Writing & Academic Services',
                'slug' => 'writing',
                'description' => 'Content writing, academic support, editing, and publishing',
                'icon' => '✍️',
                'estimated_startup_cost_min' => 1000,
                'estimated_startup_cost_max' => 10000,
                'sort_order' => 3,
            ],
            [
                'name' => 'Transport',
                'slug' => 'transport',
                'description' => 'Taxi services, logistics, delivery, and transportation',
                'icon' => '🚗',
                'estimated_startup_cost_min' => 20000,
                'estimated_startup_cost_max' => 200000,
                'sort_order' => 4,
            ],
            [
                'name' => 'Beauty & Fashion',
                'slug' => 'beauty',
                'description' => 'Salons, boutiques, fashion design, and beauty services',
                'icon' => '💄',
                'estimated_startup_cost_min' => 5000,
                'estimated_startup_cost_max' => 50000,
                'sort_order' => 5,
            ],
            [
                'name' => 'Construction',
                'slug' => 'construction',
                'description' => 'Building, renovation, and construction services',
                'icon' => '🏗️',
                'estimated_startup_cost_min' => 50000,
                'estimated_startup_cost_max' => 500000,
                'sort_order' => 6,
            ],
            [
                'name' => 'Mobile Money & Fintech',
                'slug' => 'fintech',
                'description' => 'Mobile money agents, digital payments, and financial services',
                'icon' => '💳',
                'estimated_startup_cost_min' => 5000,
                'estimated_startup_cost_max' => 100000,
                'sort_order' => 7,
            ],
            [
                'name' => 'Online Businesses',
                'slug' => 'online',
                'description' => 'E-commerce, digital services, and online platforms',
                'icon' => '💻',
                'estimated_startup_cost_min' => 2000,
                'estimated_startup_cost_max' => 30000,
                'sort_order' => 8,
            ],
            [
                'name' => 'Food & Hospitality',
                'slug' => 'food',
                'description' => 'Restaurants, catering, food processing, and hospitality',
                'icon' => '🍽️',
                'estimated_startup_cost_min' => 10000,
                'estimated_startup_cost_max' => 150000,
                'sort_order' => 9,
            ],
            [
                'name' => 'Professional Services',
                'slug' => 'professional',
                'description' => 'Consulting, legal, accounting, and professional services',
                'icon' => '💼',
                'estimated_startup_cost_min' => 5000,
                'estimated_startup_cost_max' => 50000,
                'sort_order' => 10,
            ],
        ];

        foreach ($industries as $industry) {
            Industry::updateOrCreate(
                ['slug' => $industry['slug']],
                $industry
            );
        }
    }
}
