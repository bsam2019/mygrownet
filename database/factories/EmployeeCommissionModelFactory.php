<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeCommissionModelFactory extends Factory
{
    protected $model = EmployeeCommissionModel::class;

    public function definition(): array
    {
        $baseAmount = $this->faker->numberBetween(10000, 100000);
        $rate = $this->faker->randomFloat(4, 0.05, 0.15); // 5-15%
        $amount = $baseAmount * $rate;
        
        return [
            'employee_id' => EmployeeModel::factory(),
            'commission_type' => $this->faker->randomElement(['base', 'performance', 'bonus', 'referral']),
            'source_reference' => $this->faker->optional(0.7)->uuid(),
            'amount' => $amount,
            'rate_applied' => $rate,
            'earned_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'payment_date' => $this->faker->optional(0.6)->dateTimeBetween('-3 months', 'now'),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'cancelled']),
            'description' => $this->faker->optional(0.7)->sentence(),
            'calculation_details' => $this->faker->optional(0.5)->randomElements([
                'base_amount' => $baseAmount,
                'commission_rate' => $rate,
                'calculation_method' => 'percentage',
                'bonus_multiplier' => $this->faker->randomFloat(2, 1.0, 2.0),
            ]),
            'approved_by' => $this->faker->optional(0.8) ? EmployeeModel::factory() : null,
            'approved_at' => $this->faker->optional(0.7)->dateTimeBetween('-3 months', 'now'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'pending',
            'payment_date' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
            'payment_date' => $this->faker->dateTimeBetween($attributes['earned_date'] ?? '-1 month', 'now'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'cancelled',
            'payment_date' => null,
        ]);
    }

    public function baseCommission(): static
    {
        return $this->state(fn (array $attributes) => [
            'commission_type' => 'base',
            'rate_applied' => $this->faker->randomFloat(4, 0.08, 0.12),
            'amount' => $this->faker->numberBetween(20000, 100000),
        ]);
    }

    public function referralCommission(): static
    {
        return $this->state(fn (array $attributes) => [
            'commission_type' => 'referral',
            'rate_applied' => $this->faker->randomFloat(4, 0.05, 0.08),
            'amount' => $this->faker->numberBetween(10000, 50000),
        ]);
    }

    public function performanceCommission(): static
    {
        return $this->state(fn (array $attributes) => [
            'commission_type' => 'performance',
            'rate_applied' => $this->faker->randomFloat(4, 0.10, 0.15),
            'amount' => $this->faker->numberBetween(15000, 75000),
        ]);
    }

    public function bonus(): static
    {
        return $this->state(fn (array $attributes) => [
            'commission_type' => 'bonus',
            'rate_applied' => 1.0, // Flat bonus amount
            'amount' => $this->faker->numberBetween(5000, 25000),
        ]);
    }

    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->numberBetween(75000, 200000),
            'rate_applied' => $this->faker->randomFloat(4, 0.10, 0.15),
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'earned_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'payment_status' => $this->faker->randomElement(['pending']),
            'payment_date' => null,
        ]);
    }
}