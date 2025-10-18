<?php

namespace Database\Factories;

use App\Models\MatrixPosition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MatrixPositionFactory extends Factory
{
    protected $model = MatrixPosition::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'sponsor_id' => User::factory(),
            'level' => $this->faker->numberBetween(1, 3),
            'position' => $this->faker->numberBetween(1, 27),
            'left_child_id' => null,
            'middle_child_id' => null,
            'right_child_id' => null,
            'is_active' => true,
            'placed_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function level1(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 1,
            'position' => $this->faker->numberBetween(1, 3),
        ]);
    }

    public function level2(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 2,
            'position' => $this->faker->numberBetween(1, 9),
        ]);
    }

    public function level3(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 3,
            'position' => $this->faker->numberBetween(1, 27),
        ]);
    }

    public function withChildren(): static
    {
        return $this->state(fn (array $attributes) => [
            'left_child_id' => User::factory(),
            'middle_child_id' => User::factory(),
            'right_child_id' => User::factory(),
        ]);
    }

    public function partiallyFilled(): static
    {
        return $this->state(fn (array $attributes) => [
            'left_child_id' => User::factory(),
            'middle_child_id' => null,
            'right_child_id' => null,
        ]);
    }
}