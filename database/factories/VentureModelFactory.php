<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureCategoryModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentureModelFactory extends Factory
{
    protected $model = VentureModel::class;

    public function definition(): array
    {
        return [
            'category_id' => VentureCategoryModelFactory::new(),
            'title' => $this->faker->company(),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(),
            'business_model' => $this->faker->paragraph(),
            'funding_target' => 100000,
            'minimum_investment' => 1000,
            'maximum_investment' => 50000,
            'share_price' => 100,
            'total_raised' => 0,
            'investor_count' => 0,
            'status' => 'draft',
            'expected_roi_months' => $this->faker->numberBetween(6, 36),
            'is_featured' => false,
            'views_count' => 0,
            'created_by' => User::factory(),
        ];
    }

    public function funding(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'funding',
            'funding_start_date' => now()->subDays(5),
            'funding_end_date' => now()->addDays(25),
        ]);
    }

    public function funded(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'funded',
            'total_raised' => $attrs['funding_target'] ?? 100000,
            'funding_start_date' => now()->subDays(30),
            'funding_end_date' => now()->subDays(1),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'active',
            'total_raised' => $attrs['funding_target'] ?? 100000,
        ]);
    }
}
