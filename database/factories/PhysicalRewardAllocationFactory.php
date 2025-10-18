<?php

namespace Database\Factories;

use App\Models\PhysicalRewardAllocation;
use App\Models\User;
use App\Models\PhysicalReward;
use App\Models\InvestmentTier;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhysicalRewardAllocationFactory extends Factory
{
    protected $model = PhysicalRewardAllocation::class;

    public function definition(): array
    {
        $statuses = ['allocated', 'delivered', 'ownership_transferred', 'forfeited', 'recovered'];

        return [
            'user_id' => User::factory(),
            'physical_reward_id' => PhysicalReward::factory(),
            'tier_id' => InvestmentTier::factory(),
            'team_volume_at_allocation' => $this->faker->numberBetween(10000, 500000),
            'active_referrals_at_allocation' => $this->faker->numberBetween(1, 50),
            'team_depth_at_allocation' => $this->faker->numberBetween(1, 5),
            'status' => $this->faker->randomElement($statuses),
            'allocated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'delivered_at' => null,
            'ownership_transferred_at' => null,
            'forfeited_at' => null,
            'maintenance_compliant' => true,
            'maintenance_months_completed' => $this->faker->numberBetween(0, 12),
            'last_maintenance_check' => null,
            'maintenance_notes' => null,
            'total_income_generated' => 0,
            'monthly_income_average' => 0,
            'income_tracking_started' => null,
            'asset_management_details' => null,
            'asset_manager' => null,
            'special_conditions' => null
        ];
    }

    public function allocated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'allocated',
            'delivered_at' => null,
            'ownership_transferred_at' => null,
            'forfeited_at' => null
        ]);
    }

    public function delivered(): static
    {
        return $this->state(function (array $attributes) {
            $deliveredAt = $this->faker->dateTimeBetween('-6 months', 'now');
            return [
                'status' => 'delivered',
                'delivered_at' => $deliveredAt,
                'income_tracking_started' => $deliveredAt,
                'ownership_transferred_at' => null,
                'forfeited_at' => null
            ];
        });
    }

    public function ownershipTransferred(): static
    {
        return $this->state(function (array $attributes) {
            $deliveredAt = $this->faker->dateTimeBetween('-1 year', '-6 months');
            $transferredAt = $this->faker->dateTimeBetween($deliveredAt, 'now');
            
            return [
                'status' => 'ownership_transferred',
                'delivered_at' => $deliveredAt,
                'ownership_transferred_at' => $transferredAt,
                'income_tracking_started' => $deliveredAt,
                'maintenance_compliant' => true,
                'maintenance_months_completed' => 12,
                'forfeited_at' => null
            ];
        });
    }

    public function forfeited(): static
    {
        return $this->state(function (array $attributes) {
            $forfeitedAt = $this->faker->dateTimeBetween('-3 months', 'now');
            return [
                'status' => 'forfeited',
                'forfeited_at' => $forfeitedAt,
                'maintenance_compliant' => false,
                'maintenance_notes' => 'Performance requirements not met',
                'ownership_transferred_at' => null
            ];
        });
    }

    public function withIncome(): static
    {
        return $this->state(function (array $attributes) {
            $totalIncome = $this->faker->numberBetween(1000, 10000);
            $monthsActive = $this->faker->numberBetween(1, 12);
            
            return [
                'total_income_generated' => $totalIncome,
                'monthly_income_average' => $totalIncome / $monthsActive,
                'income_tracking_started' => $this->faker->dateTimeBetween('-1 year', '-1 month')
            ];
        });
    }

    public function maintenanceCompliant(): static
    {
        return $this->state(fn (array $attributes) => [
            'maintenance_compliant' => true,
            'maintenance_months_completed' => $this->faker->numberBetween(6, 12),
            'last_maintenance_check' => $this->faker->dateTimeBetween('-1 month', 'now')
        ]);
    }

    public function maintenanceViolation(): static
    {
        return $this->state(fn (array $attributes) => [
            'maintenance_compliant' => false,
            'maintenance_months_completed' => 0,
            'last_maintenance_check' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'maintenance_notes' => 'Performance requirements not met'
        ]);
    }

    public function withAssetManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'asset_manager' => $this->faker->company(),
            'asset_management_details' => [
                'manager_contact' => $this->faker->phoneNumber(),
                'management_fee' => $this->faker->numberBetween(50, 200),
                'service_level' => $this->faker->randomElement(['basic', 'premium', 'full'])
            ]
        ]);
    }

    public function highPerformance(): static
    {
        return $this->state(fn (array $attributes) => [
            'team_volume_at_allocation' => $this->faker->numberBetween(100000, 500000),
            'active_referrals_at_allocation' => $this->faker->numberBetween(20, 50),
            'team_depth_at_allocation' => $this->faker->numberBetween(4, 5)
        ]);
    }

    public function lowPerformance(): static
    {
        return $this->state(fn (array $attributes) => [
            'team_volume_at_allocation' => $this->faker->numberBetween(1000, 10000),
            'active_referrals_at_allocation' => $this->faker->numberBetween(1, 5),
            'team_depth_at_allocation' => $this->faker->numberBetween(1, 2)
        ]);
    }
}