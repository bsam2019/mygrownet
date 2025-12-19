<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketplaceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'icon' => '📱', 'sort_order' => 1],
            ['name' => 'Fashion', 'icon' => '👗', 'sort_order' => 2],
            ['name' => 'Home & Garden', 'icon' => '🏠', 'sort_order' => 3],
            ['name' => 'Health & Beauty', 'icon' => '💄', 'sort_order' => 4],
            ['name' => 'Food & Groceries', 'icon' => '🍎', 'sort_order' => 5],
            ['name' => 'Sports & Outdoors', 'icon' => '⚽', 'sort_order' => 6],
            ['name' => 'Books & Stationery', 'icon' => '📚', 'sort_order' => 7],
            ['name' => 'Automotive', 'icon' => '🚗', 'sort_order' => 8],
            ['name' => 'Baby & Kids', 'icon' => '👶', 'sort_order' => 9],
            ['name' => 'Services', 'icon' => '🔧', 'sort_order' => 10],
        ];

        foreach ($categories as $category) {
            DB::table('marketplace_categories')->updateOrInsert(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'slug' => Str::slug($category['name']),
                    'icon' => $category['icon'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
