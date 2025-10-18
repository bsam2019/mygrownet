<?php

namespace Tests\Feature\Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\EmployeeSeeder;
use Database\Seeders\PositionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed dependencies first
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
        $this->artisan('db:seed', ['--class' => DepartmentSeeder::class]);
        $this->artisan('db:seed', ['--class' => PositionSeeder::class]);
    }

    public function test_employee_seeder_creates_employees(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeSeeder::class]);

        $this->assertGreaterThan(0, EmployeeModel::count());
        
        // Should have at least one employee per department
        $departmentCount = DepartmentModel::count();
        $this->assertGreaterThanOrEqual($departmentCount, EmployeeModel::count());
    }

    public function test_employees_have_valid_employee_numbers(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeSeeder::class]);

        $employees = EmployeeModel::all();

        foreach ($employees as $employee) {
            $this->assertNotEmpty($employee->employee_id);
            $this->assertMatchesRegularExpression('/^EMP\d{3,}$/', $employee->employee_id);
        }

        // Employee IDs should be unique
        $employeeIds = $employees->pluck('employee_id')->toArray();
        $uniqueIds = array_unique($employeeIds);
        $this->assertEquals(count($employeeIds), count($uniqueIds));
    }

    public function test_employees_have_valid_relationships(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeSeeder::class]);

        $employees = EmployeeModel::with(['department', 'position', 'user'])->get();

        foreach ($employees as $employee) {
            // Must have department and position
            $this->assertNotNull($employee->department);
            $this->assertNotNull($employee->position);
            
            // Position must belong to same department
            $this->assertEquals($employee->department_id, $employee->position->department_id);
            
            // Must have associated user
            $this->assertNotNull($employee->user);
            $this->assertInstanceOf(User::class, $employee->user);
        }
    }

    public function test_employees_have_valid_salaries(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeSeeder::class]);

        $employees = EmployeeModel::with('position')->get();

        foreach ($employees as $employee) {
            $position = $employee->position;
            
            // Salary should be within position range
            $this->assertGreaterThanOrEqual($position->min_salary, $employee->current_salary);
            $this->assertLessThanOrEqual($position->max_salary, $employee->current_salary);
        }
    }

    public function test_employees_have_valid_hire_dates(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeSeeder::class]);

        $employees = EmployeeModel::all();

        foreach ($employees as $employee) {
            $this->assertNotNull($employee->hire_date);
            $this->assertLessThanOrEqual(now(), $employee->hire_date);
            $this->assertGreaterThan(now()->subYears(10), $employee->hire_date);
        }
    }

    public function test_manager_relationships_are_valid(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeSeeder::class]);

        $employeesWithManagers = EmployeeModel::whereNotNull('manager_id')
            ->with(['manager', 'department'])
            ->get();

        foreach ($employeesWithManagers as $employee) {
            $manager = $employee->manager;
            $this->assertNotNull($manager);
            
            // Manager should not be the same as employee
            $this->assertNotEquals($employee->id, $manager->id);
            
            // Manager should be in same or parent department, or be a senior position
            $this->assertTrue(
                $manager->department_id === $employee->department_id ||
                $manager->department_id === $employee->department->parent_department_id ||
                $manager->position->title === 'Investment Director' // Investment Director can manage across departments
            );
        }
    }

    public function test_department_heads_are_assigned(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeSeeder::class]);

        $departmentsWithHeads = DepartmentModel::whereNotNull('head_employee_id')->get();
        
        $this->assertGreaterThan(0, $departmentsWithHeads->count());

        foreach ($departmentsWithHeads as $department) {
            $head = EmployeeModel::find($department->head_employee_id);
            $this->assertNotNull($head);
            $this->assertEquals($department->id, $head->department_id);
        }
    }

    public function test_employees_have_valid_employment_status(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeSeeder::class]);

        $employees = EmployeeModel::all();
        $validStatuses = ['active', 'inactive', 'terminated', 'on_leave'];

        foreach ($employees as $employee) {
            $this->assertContains($employee->employment_status, $validStatuses);
        }
    }

    public function test_employees_have_contact_information(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeSeeder::class]);

        $employees = EmployeeModel::all();

        foreach ($employees as $employee) {
            $this->assertNotEmpty($employee->email);
            $this->assertNotEmpty($employee->phone);
            
            // Validate email format
            $this->assertMatchesRegularExpression('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $employee->email);
            
            // Validate phone format (African format with optional dashes)
            $this->assertMatchesRegularExpression('/^(\+26[0-9]|0)[-0-9]{8,12}$/', $employee->phone);
        }
    }
}