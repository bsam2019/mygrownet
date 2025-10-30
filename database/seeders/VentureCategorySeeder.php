<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureCategoryModel;
use Illuminate\Database\Seeder;

class VentureCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Agriculture',
                'slug' => 'agriculture',
                'description' => 'Farming, livestock, and agricultural ventures',
                'icon' => 'leaf',
                'sort_order' => 1,
            ],
            [
                'name' => 'Transport',
                'slug' => 'transport',
                'description' => 'Transportation and logistics businesses',
                'icon' => 'truck',
                'sort_order' => 2,
            ],
            [
                'name' => 'Manufacturing',
                'slug' => 'manufacturing',
                'description' => 'Production and manufacturing ventures',
                'icon' => 'factory',
                'sort_order' => 3,
            ],
            [
                'name' => 'Retail',
                'slug' => 'retail',
                'description' => 'Retail stores and shops',
                'icon' => 'shopping-bag',
                'sort_order' => 4,
            ],
            [
                'name' => 'Services',
                'slug' => 'services',
                'description' => 'Service-based businesses',
                'icon' => 'briefcase',
                'sort_order' => 5,
            ],
            [
                'name' => 'Real Estate',
                'slug' => 'real-estate',
                'description' => 'Property development and real estate',
                'icon' => 'building',
                'sort_order' => 6,
            ],
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'Tech startups and digital businesses',
                'icon' => 'cpu',
                'sort_order' => 7,
            ],
            [
                'name' => 'Food & Beverage',
                'slug' => 'food-beverage',
                'description' => 'Restaurants, cafes, and food production',
                'icon' => 'utensils',
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            VentureCategoryModel::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
