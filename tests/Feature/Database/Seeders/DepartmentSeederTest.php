<?php

namespace Tests\Feature\Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_seeder_creates_required_departments(): void
    {
        $this->artisan('db:seed', ['--class' => DepartmentSeeder::class]);

        // Verify all required departments exist
        $expectedDepartments = [
            'Human Resources',
            'Investment Management',
            'Field Operations',
            'Finance & Accounting',
            'Information Technology',
            'Compliance & Risk',
            'Customer Service'
        ];

        foreach ($expectedDepartments as $departmentName) {
            $this->assertTrue(
                DepartmentModel::where('name', $departmentName)->exists(),
                "Department '{$departmentName}' was not created"
            );
        }
    }

    public function test_department_hierarchy_is_correct(): void
    {
        $this->artisan('db:seed', ['--class' => DepartmentSeeder::class]);

        // Field Operations should be under Investment Management
        $investmentDept = DepartmentModel::where('name', 'Investment Management')->first();
        $fieldDept = DepartmentModel::where('name', 'Field Operations')->first();

        $this->assertNotNull($investmentDept);
        $this->assertNotNull($fieldDept);
        $this->assertEquals($investmentDept->id, $fieldDept->parent_department_id);
    }

    public function test_departments_have_valid_descriptions(): void
    {
        $this->artisan('db:seed', ['--class' => DepartmentSeeder::class]);

        $departments = DepartmentModel::all();

        foreach ($departments as $department) {
            $this->assertNotEmpty($department->description);
            $this->assertIsString($department->description);
            $this->assertGreaterThan(10, strlen($department->description));
        }
    }

    public function test_departments_are_active_by_default(): void
    {
        $this->artisan('db:seed', ['--class' => DepartmentSeeder::class]);

        $departments = DepartmentModel::all();

        foreach ($departments as $department) {
            $this->assertTrue($department->is_active);
        }
    }
}