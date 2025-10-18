<?php

namespace Database\Factories;

use App\Models\PhysicalReward;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhysicalRewardFactory extends Factory
{
    protected $model = PhysicalReward::class;

    public function definition(): array
    {
        $categories = ['vehicle', 'electronics', 'business_kit', 'property'];
        $tiers = ['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite'];
        $ownershipTypes = ['conditional', 'immediate', 'gradual'];

        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement($categories),
            'estimated_value' => $this->faker->numberBetween(1000, 100000),
            'required_membership_tiers' => [$this->faker->randomElement($tiers)],
            'required_referrals' => $this->faker->numberBetween(1, 50),
            'required_subscription_amount' => $this->faker->numberBetween(150, 1500),
            'required_sustained_months' => $this->faker->numberBetween(1, 12),
            'required_team_volume' => $this->faker->numberBetween(0, 500000),
            'required_team_depth' => $this->faker->numberBetween(1, 5),
            'maintenance_period_months' => $this->faker->numberBetween(6, 24),
            'requires_performance_maintenance' => $this->faker->boolean(70),
            'income_generating' => $this->faker->boolean(60),
            'estimated_monthly_income' => $this->faker->numberBetween(0, 5000),
            'asset_management_options' => $this->faker->boolean() ? [
                'option1' => $this->faker->sentence(),
                'option2' => $this->faker->sentence()
            ] : null,
            'ownership_type' => $this->faker->randomElement($ownershipTypes),
            'ownership_conditions' => $this->faker->sentence(),
            'available_quantity' => $this->faker->numberBetween(5, 100),
            'allocated_quantity' => 0,
            'image_url' => $this->faker->imageUrl(),
            'specifications' => [
                'spec1' => $this->faker->word(),
                'spec2' => $this->faker->word()
            ],
            'terms_and_conditions' => $this->faker->paragraph(),
            'is_active' => true
        ];
    }

    public function bronze(): static
    {
        return $this->state(fn (array $attributes) => [
            'required_membership_tiers' => ['Bronze'],
            'required_referrals' => 1,
            'required_team_volume' => 0,
            'required_team_depth' => 1,
            'required_subscription_amount' => 150,
            'estimated_value' => 500,
            'maintenance_period_months' => 0,
            'requires_performance_maintenance' => false,
            'income_generating' => false
        ]);
    }

    public function silver(): static
    {
        return $this->state(fn (array $attributes) => [
            'required_membership_tiers' => ['Silver'],
            'required_referrals' => 3,
            'required_team_volume' => 15000,
            'required_team_depth' => 2,
            'required_subscription_amount' => 300,
            'estimated_value' => 3000,
            'maintenance_period_months' => 12,
            'requires_performance_maintenance' => true,
            'income_generating' => false
        ]);
    }

    public function gold(): static
    {
        return $this->state(fn (array $attributes) => [
            'required_membership_tiers' => ['Gold'],
            'required_referrals' => 10,
            'required_team_volume' => 50000,
            'required_team_depth' => 3,
            'required_subscription_amount' => 500,
            'estimated_value' => 12000,
            'maintenance_period_months' => 12,
            'requires_performance_maintenance' => true,
            'income_generating' => true,
            'estimated_monthly_income' => 800
        ]);
    }

    public function diamond(): static
    {
        return $this->state(fn (array $attributes) => [
            'required_membership_tiers' => ['Diamond'],
            'required_referrals' => 25,
            'required_team_volume' => 150000,
            'required_team_depth' => 4,
            'required_subscription_amount' => 1000,
            'estimated_value' => 35000,
            'maintenance_period_months' => 12,
            'requires_performance_maintenance' => true,
            'income_generating' => true,
            'estimated_monthly_income' => 2000
        ]);
    }

    public function elite(): static
    {
        return $this->state(fn (array $attributes) => [
            'required_membership_tiers' => ['Elite'],
            'required_referrals' => 50,
            'required_team_volume' => 500000,
            'required_team_depth' => 5,
            'required_subscription_amount' => 1500,
            'estimated_value' => 75000,
            'maintenance_period_months' => 12,
            'requires_performance_maintenance' => true,
            'income_generating' => true,
            'estimated_monthly_income' => 3500
        ]);
    }

    public function vehicle(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'vehicle',
            'income_generating' => true,
            'asset_management_options' => [
                'ride_sharing' => 'Uber/Bolt partnership program',
                'rental_services' => 'Vehicle rental to other members'
            ]
        ]);
    }

    public function electronics(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'electronics',
            'income_generating' => false,
            'requires_performance_maintenance' => true
        ]);
    }

    public function businessKit(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'business_kit',
            'income_generating' => true,
            'asset_management_options' => [
                'co_working_space' => 'Rent out desk space',
                'business_services' => 'Offer printing and admin services'
            ]
        ]);
    }

    public function property(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'property',
            'income_generating' => true,
            'estimated_value' => $this->faker->numberBetween(25000, 100000),
            'asset_management_options' => [
                'rental_management' => 'Property management services',
                'tenant_screening' => 'Tenant vetting and placement'
            ]
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false
        ]);
    }

    public function fullyAllocated(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $attributes['available_quantity'] ?? 10;
            return ['allocated_quantity' => $quantity];
        });
    }
}