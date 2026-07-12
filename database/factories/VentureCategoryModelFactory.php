<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureCategoryModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentureCategoryModelFactory extends Factory
{
    protected $model = VentureCategoryModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'slug' => $this->faker->unique()->slug(1),
            'description' => $this->faker->sentence(),
            'icon' => $this->faker->word(),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
