<?php

namespace Database\Factories;

use App\Models\InvestmentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvestmentCategoryFactory extends Factory
{
    protected $model = InvestmentCategory::class;

    public function definition(): array
    {
        $baseName = $this->faker->randomElement([
            'Real Estate',
            'Technology Stocks',
            'Government Bonds',
            'Mutual Funds',
            'Cryptocurrency',
            'Commodities'
        ]);
        
        // Add unique suffix to avoid duplicates
        $name = $baseName . ' ' . $this->faker->unique()->numberBetween(1, 9999);

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => $this->faker->sentence(10),
            'min_investment' => $this->faker->randomFloat(2, 500, 1000),
            'max_investment' => $this->faker->randomFloat(2, 50000, 100000),
            'interest_rate' => $this->faker->randomFloat(2, 5, 15),
            'expected_roi' => $this->faker->randomFloat(2, 8, 20),
            'lock_in_period' => $this->faker->numberBetween(6, 24), // months
            'is_active' => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false
        ]);
    }
}