<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeModelFactory extends Factory
{
    protected $model = EmployeeModel::class;

    public function definition(): array
    {
        $hireDate = $this->faker->dateTimeBetween('-5 years', 'now');
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        
        return [
            'employee_id' => 'EMP' . str_pad((string)$this->faker->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'user_id' => null, // Can be set to link with User model
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower($firstName . '.' . $lastName . '@vbif.com'),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'date_of_birth' => $this->faker->optional(0.8)->dateTimeBetween('-65 years', '-18 years'),
            'gender' => $this->faker->optional(0.9)->randomElement(['male', 'female', 'other']),
            'national_id' => $this->faker->optional(0.7)->numerify('##########'),
            'department_id' => DepartmentModel::factory(),
            'position_id' => PositionModel::factory(),
            'manager_id' => null, // Will be set for hierarchical relationships
            'employment_status' => $this->faker->randomElement(['active', 'inactive', 'suspended']),
            'hire_date' => $hireDate,
            'termination_date' => null,
            'current_salary' => $this->faker->numberBetween(30000, 100000),
            'emergency_contacts' => $this->faker->optional(0.8)->randomElements([
                [
                    'name' => $this->faker->name(),
                    'relationship' => 'Spouse',
                    'phone' => $this->faker->phoneNumber(),
                ],
                [
                    'name' => $this->faker->name(),
                    'relationship' => 'Parent',
                    'phone' => $this->faker->phoneNumber(),
                ],
            ], $this->faker->numberBetween(1, 2)),
            'qualifications' => $this->faker->optional(0.6)->randomElements([
                'Bachelor\'s Degree in Business',
                'Master\'s in Finance',
                'CPA Certification',
                'Project Management Certification',
                'Sales Training Certificate',
            ], $this->faker->numberBetween(1, 3)),
            'notes' => $this->faker->optional(0.3)->paragraph(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_status' => 'active',
            'termination_date' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_status' => 'inactive',
        ]);
    }

    public function terminated(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_status' => 'terminated',
            'termination_date' => $this->faker->dateTimeBetween($attributes['hire_date'] ?? '-1 year', 'now'),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_status' => 'suspended',
        ]);
    }

    public function withUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
        ]);
    }

    public function withManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'manager_id' => EmployeeModel::factory(),
        ]);
    }

    public function fieldAgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_id' => PositionModel::factory()->fieldAgent(),
            'employment_status' => 'active',
            'current_salary' => $this->faker->numberBetween(35000, 50000),
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_id' => PositionModel::factory()->manager(),
            'current_salary' => $this->faker->numberBetween(60000, 100000),
            'employment_status' => 'active',
        ]);
    }

    public function highPerformer(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_status' => 'active',
            'current_salary' => $this->faker->numberBetween(60000, 120000),
        ]);
    }

    public function lowPerformer(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_salary' => $this->faker->numberBetween(25000, 45000),
        ]);
    }

    public function recentHire(): static
    {
        return $this->state(fn (array $attributes) => [
            'hire_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'employment_status' => 'active',
        ]);
    }

    public function veteran(): static
    {
        return $this->state(fn (array $attributes) => [
            'hire_date' => $this->faker->dateTimeBetween('-10 years', '-2 years'),
            'employment_status' => 'active',
            'current_salary' => $this->faker->numberBetween(50000, 90000),
        ]);
    }
}