<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureVoteModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentureVoteModelFactory extends Factory
{
    protected $model = VentureVoteModel::class;

    public function definition(): array
    {
        return [
            'resolution_id' => \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureResolutionModel::factory(),
            'shareholder_id' => \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel::factory(),
            'user_id' => User::factory(),
            'vote' => $this->faker->randomElement(['for', 'against', 'abstain']),
            'equity_at_vote' => $this->faker->randomFloat(4, 1, 50),
            'voted_at' => now(),
        ];
    }
}
