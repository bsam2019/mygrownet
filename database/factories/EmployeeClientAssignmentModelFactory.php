<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\EmployeeClientAssignmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeClientAssignmentModelFactory extends Factory
{
    protected $model = EmployeeClientAssignmentModel::class;

    public function definition(): array
    {
        $assignedDate = $this->faker->dateTimeBetween('-2 years', 'now');
        
        return [
            'employee_id' => EmployeeModel::factory(),
            'client_user_id' => User::factory(),
            'assignment_type' => $this->faker->randomElement(['primary', 'secondary', 'support']),
            'assigned_date' => $assignedDate,
            'unassigned_date' => $this->faker->optional(0.2)->dateTimeBetween($assignedDate, 'now'),
            'status' => $this->faker->randomElement(['active', 'inactive', 'transferred']),
            'assignment_notes' => $this->faker->optional(0.4)->sentence(),
            'assigned_by' => EmployeeModel::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'unassigned_date' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'unassigned_date' => $this->faker->dateTimeBetween($attributes['assigned_date'] ?? '-1 year', 'now'),
        ]);
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_type' => 'primary',
            'status' => 'active',
        ]);
    }

    public function secondary(): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_type' => 'secondary',
        ]);
    }

    public function support(): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_type' => 'support',
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'status' => 'active',
            'unassigned_date' => null,
        ]);
    }

    public function longTerm(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_date' => $this->faker->dateTimeBetween('-3 years', '-1 year'),
            'status' => 'active',
            'unassigned_date' => null,
        ]);
    }

    public function completed(): static
    {
        $assignedDate = $this->faker->dateTimeBetween('-2 years', '-6 months');
        
        return $this->state(fn (array $attributes) => [
            'assigned_date' => $assignedDate,
            'unassigned_date' => $this->faker->dateTimeBetween($assignedDate, '-1 month'),
            'status' => 'inactive',
        ]);
    }
}