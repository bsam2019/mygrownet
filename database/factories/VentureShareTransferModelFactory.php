<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareTransferModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentureShareTransferModelFactory extends Factory
{
    protected $model = VentureShareTransferModel::class;

    public function definition(): array
    {
        return [
            'venture_id' => \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel::factory(),
            'from_user_id' => User::factory(),
            'to_user_id' => User::factory(),
            'shares' => $this->faker->numberBetween(10, 500),
            'price_per_share' => $this->faker->randomFloat(2, 50, 200),
            'total_value' => fn(array $attrs) => $attrs['shares'] * ($attrs['price_per_share'] ?? 100),
            'status' => 'pending',
            'reason' => $this->faker->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn() => ['status' => 'pending']);
    }

    public function completed(): static
    {
        return $this->state(fn() => ['status' => 'completed', 'completed_at' => now()]);
    }
}
