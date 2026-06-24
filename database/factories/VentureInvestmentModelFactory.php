<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentureInvestmentModelFactory extends Factory
{
    protected $model = VentureInvestmentModel::class;

    public function definition(): array
    {
        return [
            'venture_id' => \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel::factory(),
            'user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
            'shares_allocated' => $this->faker->numberBetween(10, 500),
            'status' => 'pending',
            'payment_method' => 'wallet',
            'payment_reference' => 'WALLET_' . strtoupper($this->faker->bothify('??##??##')),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'confirmed',
            'payment_confirmed_at' => now(),
        ]);
    }
}
