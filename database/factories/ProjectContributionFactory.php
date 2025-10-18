<?php

namespace Database\Factories;

use App\Models\ProjectContribution;
use App\Models\User;
use App\Models\CommunityProject;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectContributionFactory extends Factory
{
    protected $model = ProjectContribution::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'community_project_id' => CommunityProject::factory(),
            'amount' => $this->faker->numberBetween(1000, 50000),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
            'tier_at_contribution' => $this->faker->randomElement(['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite']),
            'contributed_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'confirmed_at' => $this->faker->optional(0.7)->dateTimeBetween('-5 months', 'now'),
            'transaction_reference' => 'PROJ-CONTRIB-' . $this->faker->unique()->numerify('########'),
            'payment_method' => $this->faker->randomElement(['bank_transfer', 'mobile_money', 'internal_balance']),
            'payment_details' => [
                'reference' => $this->faker->uuid,
                'method_details' => $this->faker->sentence
            ],
            'total_returns_received' => $this->faker->numberBetween(0, 5000),
            'expected_annual_return' => $this->faker->randomFloat(2, 8, 20),
            'auto_reinvest' => $this->faker->boolean(30),
            'notes' => $this->faker->optional()->sentence
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'confirmed_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'confirmed_by' => User::factory()
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'confirmed_at' => null,
            'confirmed_by' => null
        ]);
    }

    public function withReturns(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'total_returns_received' => $this->faker->numberBetween(500, 10000)
        ]);
    }
}