<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    private static $index = 0;

    private $categories = [
        'Fixed Income Bonds',
        'Real Estate Fund',
        'Technology Growth Fund',
        'Green Energy Fund',
        'Small Cap Equity',
        'Emerging Markets Fund',
        'Infrastructure Fund',
        'Healthcare Innovation',
        'Agriculture Fund',
        'Fintech Ventures'
    ];

    public function definition(): array
    {
        // Get next category in rotation
        $name = $this->categories[self::$index % count($this->categories)];
        self::$index++;

        // Add a unique identifier if we've gone through all categories once
        $uniqueSuffix = floor((self::$index - 1) / count($this->categories)) > 0
            ? ' ' . floor((self::$index - 1) / count($this->categories))
            : '';

        $finalName = $name . $uniqueSuffix;

        return [
            'name' => $finalName,
            'slug' => Str::slug($finalName),
            'description' => $this->faker->paragraph(),
            'interest_rate' => $this->faker->randomFloat(2, 5, 15),
            'min_investment' => $this->faker->randomFloat(2, 500, 1000),
            'max_investment' => $this->faker->randomFloat(2, 50000, 100000),
            'expected_roi' => $this->faker->randomFloat(2, 8, 20),
            'lock_in_period' => $this->faker->randomElement([3, 6, 12]),
            'is_active' => true,
        ];
    }
}
