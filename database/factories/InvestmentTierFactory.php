<?php

namespace Database\Factories;

use App\Models\InvestmentTier;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvestmentTierFactory extends Factory
{
    protected $model = InvestmentTier::class;

    public function definition(): array
    {
        $baseName = $this->faker->randomElement(['Basic', 'Starter', 'Builder', 'Leader', 'Elite']);
        $uniqueName = $baseName . ' ' . $this->faker->unique()->numberBetween(1, 9999);
        
        return [
            'name' => $uniqueName,
            'minimum_investment' => $this->faker->numberBetween(500, 10000),
            'fixed_profit_rate' => $this->faker->randomFloat(2, 3, 15),
            'direct_referral_rate' => $this->faker->randomFloat(2, 5, 15),
            'level2_referral_rate' => $this->faker->randomFloat(2, 0, 7),
            'level3_referral_rate' => $this->faker->randomFloat(2, 0, 3),
            'reinvestment_bonus_rate' => $this->faker->randomFloat(2, 0, 2),
            'benefits' => [],
            'is_active' => true,
            'is_archived' => false,
            'description' => $this->faker->sentence(),
            'order' => $this->faker->numberBetween(1, 5),
        ];
    }

    public function basic(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Basic',
            'minimum_investment' => 500,
            'fixed_profit_rate' => 3.0,
            'direct_referral_rate' => 5.0,
            'order' => 1,
        ]);
    }

    public function starter(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Starter',
            'minimum_investment' => 1000,
            'fixed_profit_rate' => 5.0,
            'direct_referral_rate' => 7.0,
            'level2_referral_rate' => 2.0,
            'order' => 2,
        ]);
    }

    public function builder(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Builder',
            'minimum_investment' => 2500,
            'fixed_profit_rate' => 7.0,
            'direct_referral_rate' => 10.0,
            'level2_referral_rate' => 3.0,
            'level3_referral_rate' => 1.0,
            'order' => 3,
        ]);
    }

    public function leader(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Leader',
            'minimum_investment' => 5000,
            'fixed_profit_rate' => 10.0,
            'direct_referral_rate' => 12.0,
            'level2_referral_rate' => 5.0,
            'level3_referral_rate' => 2.0,
            'order' => 4,
        ]);
    }

    public function elite(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Elite',
            'minimum_investment' => 10000,
            'fixed_profit_rate' => 15.0,
            'direct_referral_rate' => 15.0,
            'level2_referral_rate' => 7.0,
            'level3_referral_rate' => 3.0,
            'reinvestment_bonus_rate' => 2.0,
            'order' => 5,
        ]);
    }
}