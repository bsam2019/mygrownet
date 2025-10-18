<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeePerformanceModelFactory extends Factory
{
    protected $model = EmployeePerformanceModel::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-2 years', '-3 months');
        $endDate = (clone $startDate)->modify('+3 months');
        
        $investmentsFacilitatedCount = $this->faker->numberBetween(0, 50);
        $investmentsFacilitatedAmount = $investmentsFacilitatedCount * $this->faker->numberBetween(5000, 50000);
        $commissionGenerated = $investmentsFacilitatedAmount * 0.1; // 10% commission rate
        
        return [
            'employee_id' => EmployeeModel::factory(),
            'reviewer_id' => EmployeeModel::factory(),
            'evaluation_period' => $this->faker->randomElement(['monthly', 'quarterly', 'annual']),
            'period_start' => $startDate,
            'period_end' => $endDate,
            'metrics' => [
                'investments_facilitated_count' => $investmentsFacilitatedCount,
                'investments_facilitated_amount' => $investmentsFacilitatedAmount,
                'client_retention_rate' => $this->faker->randomFloat(2, 60, 100),
                'commission_generated' => $commissionGenerated,
                'new_client_acquisitions' => $this->faker->numberBetween(0, 20),
                'goal_achievement_rate' => $this->faker->randomFloat(2, 50, 120),
            ],
            'overall_score' => $this->faker->randomFloat(2, 1, 100),
            'rating' => $this->faker->randomElement(['excellent', 'good', 'satisfactory', 'needs_improvement', 'unsatisfactory']),
            'strengths' => $this->faker->optional(0.8)->paragraph(2),
            'areas_for_improvement' => $this->faker->optional(0.8)->paragraph(2),
            'goals_next_period' => $this->faker->optional(0.8)->paragraph(3),
            'reviewer_comments' => $this->faker->optional(0.8)->paragraph(3),
            'employee_comments' => $this->faker->optional(0.3)->paragraph(2),
            'status' => $this->faker->randomElement(['draft', 'submitted', 'approved', 'rejected']),
            'submitted_at' => $this->faker->optional(0.7)->dateTimeBetween($endDate, 'now'),
            'approved_at' => $this->faker->optional(0.5)->dateTimeBetween($endDate, 'now'),
        ];
    }

    public function excellent(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_score' => $this->faker->randomFloat(2, 90, 100),
            'rating' => 'excellent',
            'metrics' => array_merge($attributes['metrics'] ?? [], [
                'goal_achievement_rate' => $this->faker->randomFloat(2, 100, 120),
                'client_retention_rate' => $this->faker->randomFloat(2, 90, 100),
                'investments_facilitated_count' => $this->faker->numberBetween(30, 50),
                'new_client_acquisitions' => $this->faker->numberBetween(15, 25),
            ]),
        ]);
    }

    public function good(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_score' => $this->faker->randomFloat(2, 70, 89),
            'rating' => 'good',
            'metrics' => array_merge($attributes['metrics'] ?? [], [
                'goal_achievement_rate' => $this->faker->randomFloat(2, 80, 99),
                'client_retention_rate' => $this->faker->randomFloat(2, 80, 89),
                'investments_facilitated_count' => $this->faker->numberBetween(20, 35),
                'new_client_acquisitions' => $this->faker->numberBetween(10, 18),
            ]),
        ]);
    }

    public function needsImprovement(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_score' => $this->faker->randomFloat(2, 40, 60),
            'rating' => 'needs_improvement',
            'metrics' => array_merge($attributes['metrics'] ?? [], [
                'goal_achievement_rate' => $this->faker->randomFloat(2, 50, 79),
                'client_retention_rate' => $this->faker->randomFloat(2, 60, 79),
                'investments_facilitated_count' => $this->faker->numberBetween(5, 15),
                'new_client_acquisitions' => $this->faker->numberBetween(2, 8),
            ]),
        ]);
    }

    public function quarterly(): static
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', '-3 months');
        $endDate = (clone $startDate)->modify('+3 months');
        
        return $this->state(fn (array $attributes) => [
            'evaluation_period' => 'quarterly',
            'period_start' => $startDate,
            'period_end' => $endDate,
        ]);
    }

    public function annual(): static
    {
        $startDate = $this->faker->dateTimeBetween('-2 years', '-1 year');
        $endDate = (clone $startDate)->modify('+1 year');
        
        return $this->state(fn (array $attributes) => [
            'evaluation_period' => 'annual',
            'period_start' => $startDate,
            'period_end' => $endDate,
            'metrics' => array_merge($attributes['metrics'] ?? [], [
                'investments_facilitated_count' => $this->faker->numberBetween(50, 200),
                'new_client_acquisitions' => $this->faker->numberBetween(20, 80),
            ]),
        ]);
    }

    public function recent(): static
    {
        $startDate = $this->faker->dateTimeBetween('-6 months', '-3 months');
        $endDate = (clone $startDate)->modify('+3 months');
        
        return $this->state(fn (array $attributes) => [
            'period_start' => $startDate,
            'period_end' => $endDate,
        ]);
    }

    public function fieldAgentPerformance(): static
    {
        return $this->state(fn (array $attributes) => [
            'metrics' => array_merge($attributes['metrics'] ?? [], [
                'investments_facilitated_count' => $this->faker->numberBetween(15, 40),
                'client_retention_rate' => $this->faker->randomFloat(2, 75, 95),
                'new_client_acquisitions' => $this->faker->numberBetween(8, 20),
                'goal_achievement_rate' => $this->faker->randomFloat(2, 70, 110),
            ]),
            'overall_score' => $this->faker->randomFloat(2, 60, 90),
        ]);
    }
}