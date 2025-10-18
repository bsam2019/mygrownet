<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionModelFactory extends Factory
{
    protected $model = PositionModel::class;

    public function definition(): array
    {
        $minSalary = $this->faker->numberBetween(30000, 80000);
        $maxSalary = $minSalary + $this->faker->numberBetween(10000, 40000);
        
        return [
            'title' => $this->faker->randomElement([
                'HR Manager',
                'HR Assistant',
                'Finance Manager',
                'Accountant',
                'Investment Analyst',
                'Portfolio Manager',
                'Field Agent',
                'Senior Field Agent',
                'Client Relations Manager',
                'Customer Service Representative',
                'Compliance Officer',
                'Risk Analyst',
                'Marketing Coordinator',
                'Business Development Manager',
                'IT Support Specialist',
                'System Administrator'
            ]),
            'description' => $this->faker->paragraph(3),
            'department_id' => DepartmentModel::factory(),
            'min_salary' => $minSalary,
            'max_salary' => $maxSalary,
            'base_commission_rate' => $this->faker->randomFloat(4, 0, 0.15), // 0-15% commission rate
            'performance_commission_rate' => $this->faker->randomFloat(4, 0, 0.10), // 0-10% performance commission
            'permissions' => $this->faker->randomElements([
                'view_employees',
                'create_employees',
                'edit_employees',
                'delete_employees',
                'view_investments',
                'create_investments',
                'edit_investments',
                'view_reports',
                'create_reports',
                'manage_commissions',
                'approve_withdrawals',
                'manage_users'
            ], $this->faker->numberBetween(2, 5)),
            'level' => $this->faker->numberBetween(1, 5),
            'is_active' => $this->faker->boolean(95), // 95% chance of being active
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function commissionEligible(): static
    {
        return $this->state(fn (array $attributes) => [
            'base_commission_rate' => $this->faker->randomFloat(4, 0.05, 0.15),
            'performance_commission_rate' => $this->faker->randomFloat(4, 0.02, 0.10),
        ]);
    }

    public function noCommission(): static
    {
        return $this->state(fn (array $attributes) => [
            'base_commission_rate' => 0,
            'performance_commission_rate' => 0,
        ]);
    }

    public function fieldAgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Field Agent',
            'description' => 'Responsible for client acquisition, relationship management, and investment facilitation.',
            'min_salary' => 35000,
            'max_salary' => 50000,
            'base_commission_rate' => 0.10,
            'performance_commission_rate' => 0.05,
            'permissions' => [
                'view_investments',
                'create_investments',
                'view_clients',
                'manage_client_portfolio'
            ],
            'level' => 2,
            'is_active' => true,
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement(['HR Manager', 'Finance Manager', 'Operations Manager']),
            'min_salary' => 60000,
            'max_salary' => 90000,
            'base_commission_rate' => 0,
            'performance_commission_rate' => 0,
            'permissions' => [
                'view_employees',
                'edit_employees',
                'view_reports',
                'create_reports',
                'manage_team'
            ],
            'level' => 4,
            'is_active' => true,
        ]);
    }
}