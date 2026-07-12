<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureResolutionModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentureResolutionModelFactory extends Factory
{
    protected $model = VentureResolutionModel::class;

    public function definition(): array
    {
        return [
            'venture_id' => \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['ordinary', 'special', 'board']),
            'status' => 'draft',
            'pass_threshold_percentage' => 50.0,
            'created_by' => User::factory(),
        ];
    }

    public function voting(): static
    {
        return $this->state(fn() => [
            'status' => 'voting',
            'voting_starts_at' => now()->subDays(1),
            'voting_ends_at' => now()->addDays(13),
        ]);
    }

    public function passed(): static
    {
        return $this->state(fn() => [
            'status' => 'passed',
            'votes_for' => 60.0,
            'votes_against' => 20.0,
            'votes_abstain' => 10.0,
            'total_voted_equity' => 90.0,
        ]);
    }
}
