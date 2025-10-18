<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentModelFactory extends Factory
{
    protected $model = DepartmentModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company() . ' Department',
            'description' => $this->faker->paragraph(2),
            'head_employee_id' => null, // Will be set after employees are created
            'parent_department_id' => null,
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
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

    public function withHead(): static
    {
        return $this->state(fn (array $attributes) => [
            'head_employee_id' => EmployeeModel::factory(),
        ]);
    }

    public function withParent(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_department_id' => DepartmentModel::factory(),
        ]);
    }

    public function hrDepartment(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Human Resources',
            'description' => 'Responsible for employee management, recruitment, and organizational development.',
            'is_active' => true,
        ]);
    }

    public function financeDepartment(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Finance',
            'description' => 'Manages financial operations, budgeting, and investment fund administration.',
            'is_active' => true,
        ]);
    }

    public function fieldOperations(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Field Operations',
            'description' => 'Manages field agents and client relationship activities.',
            'is_active' => true,
        ]);
    }
}