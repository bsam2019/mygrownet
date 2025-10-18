<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Agriculture',
                'description' => 'Agricultural investments and farming projects',
                'min_investment' => 1000.00,
                'max_investment' => 50000.00,
                'interest_rate' => 12.50,
                'expected_roi' => 15.00,
                'lock_in_period' => 6
            ],
            [
                'name' => 'Real Estate',
                'description' => 'Property development and real estate investments',
                'min_investment' => 5000.00,
                'max_investment' => 100000.00,
                'interest_rate' => 10.00,
                'expected_roi' => 12.00,
                'lock_in_period' => 12
            ],
            [
                'name' => 'Technology',
                'description' => 'Tech startups and innovation projects',
                'min_investment' => 2000.00,
                'max_investment' => 75000.00,
                'interest_rate' => 15.00,
                'expected_roi' => 18.00,
                'lock_in_period' => 3
            ],
            [
                'name' => 'Green Energy',
                'description' => 'Renewable energy and sustainability projects',
                'min_investment' => 3000.00,
                'max_investment' => 80000.00,
                'interest_rate' => 11.00,
                'expected_roi' => 13.50,
                'lock_in_period' => 9
            ],
            [
                'name' => 'Small Business',
                'description' => 'Local business and entrepreneurship funding',
                'min_investment' => 1500.00,
                'max_investment' => 25000.00,
                'interest_rate' => 13.00,
                'expected_roi' => 16.00,
                'lock_in_period' => 6
            ]
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'slug' => Str::slug($category['name']),
                    'description' => $category['description'],
                    'min_investment' => $category['min_investment'],
                    'max_investment' => $category['max_investment'],
                    'interest_rate' => $category['interest_rate'],
                    'expected_roi' => $category['expected_roi'],
                    'lock_in_period' => $category['lock_in_period'],
                    'is_active' => true
                ]
            );
        }
    }
}
