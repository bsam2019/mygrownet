<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class WithdrawalRequestFactory extends Factory
{
    protected $model = WithdrawalRequest::class;

    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 100, 5000);
        $penaltyAmount = $this->faker->randomFloat(2, 0, $amount * 0.3);
        $netAmount = $amount - $penaltyAmount;

        return [
            'user_id' => User::factory(),
            'amount' => $amount,
            'type' => $this->faker->randomElement(['full', 'partial', 'emergency']),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'penalty_amount' => $penaltyAmount,
            'net_amount' => $netAmount,
            'reference' => 'WD-' . $this->faker->unique()->numerify('######'),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }
}