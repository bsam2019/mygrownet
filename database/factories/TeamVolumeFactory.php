<?php

namespace Database\Factories;

use App\Models\TeamVolume;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamVolumeFactory extends Factory
{
    protected $model = TeamVolume::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'personal_volume' => $this->faker->randomFloat(2, 100, 5000),
            'team_volume' => $this->faker->randomFloat(2, 100, 10000),
            'team_depth' => $this->faker->numberBetween(0, 5),
            'active_referrals_count' => $this->faker->numberBetween(0, 10),
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
        ];
    }

    public function withTeamVolume(float $personalVolume, float $teamVolume): static
    {
        return $this->state(fn (array $attributes) => [
            'personal_volume' => $personalVolume,
            'team_volume' => max($personalVolume, $teamVolume), // Ensure team >= personal
        ]);
    }

    public function forCurrentPeriod(): static
    {
        return $this->state(fn (array $attributes) => [
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
        ]);
    }

    public function forPreviousPeriod(): static
    {
        return $this->state(fn (array $attributes) => [
            'period_start' => now()->subMonth()->startOfMonth(),
            'period_end' => now()->subMonth()->endOfMonth(),
        ]);
    }

    public function withActiveReferrals(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'active_referrals_count' => $count,
        ]);
    }

    public function withDepth(int $depth): static
    {
        return $this->state(fn (array $attributes) => [
            'team_depth' => $depth,
        ]);
    }
}