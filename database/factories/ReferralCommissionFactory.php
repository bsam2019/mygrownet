<?php

namespace Database\Factories;

use App\Models\ReferralCommission;
use App\Models\User;
use App\Models\Investment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferralCommissionFactory extends Factory
{
    protected $model = ReferralCommission::class;

    public function definition(): array
    {
        return [
            'referrer_id' => User::factory(),
            'referred_id' => User::factory(),
            'investment_id' => Investment::factory(),
            'level' => $this->faker->numberBetween(1, 5),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'percentage' => $this->faker->randomFloat(2, 1, 15),
            'status' => $this->faker->randomElement(['pending', 'paid', 'rejected']),
            'team_volume' => $this->faker->randomFloat(2, 0, 50000),
            'personal_volume' => $this->faker->randomFloat(2, 0, 10000),
            'commission_type' => $this->faker->randomElement(['REFERRAL', 'TEAM_VOLUME', 'PERFORMANCE']),
            'package_type' => $this->faker->randomElement(['subscription', 'upgrade', 'bonus']),
            'package_amount' => $this->faker->randomFloat(2, 100, 5000),
            'paid_at' => $this->faker->optional(0.6)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'paid_at' => null,
        ]);
    }

    public function referralType(): static
    {
        return $this->state(fn (array $attributes) => [
            'commission_type' => 'REFERRAL',
            'level' => $this->faker->numberBetween(1, 5),
        ]);
    }

    public function teamVolumeType(): static
    {
        return $this->state(fn (array $attributes) => [
            'commission_type' => 'TEAM_VOLUME',
            'level' => 0, // Team volume bonuses don't have levels
        ]);
    }

    public function performanceType(): static
    {
        return $this->state(fn (array $attributes) => [
            'commission_type' => 'PERFORMANCE',
            'level' => 0, // Performance bonuses don't have levels
        ]);
    }

    public function level(int $level): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $level,
            'percentage' => ReferralCommission::getCommissionRate($level),
        ]);
    }

    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }

    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween(now()->startOfMonth(), now()),
        ]);
    }

    public function lastMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween(
                now()->subMonth()->startOfMonth(), 
                now()->subMonth()->endOfMonth()
            ),
        ]);
    }
}