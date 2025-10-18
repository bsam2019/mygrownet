<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_has_fillable_attributes(): void
    {
        $fillable = [
            'name',
            'description',
            'head_employee_id',
            'parent_department_id',
            'is_active',
        ];

        $department = new DepartmentModel();
        
        $this->assertEquals($fillable, $department->getFillable());
    }

    public function test_department_casts_attributes_correctly(): void
    {
        $department = DepartmentModel::factory()->create([
            'is_active' => '1',
        ]);

        $this->assertIsBool($department->is_active);
        $this->assertTrue($department->is_active);
    }

    public function test_department_belongs_to_head_employee(): void
    {
        $headEmployee = EmployeeModel::factory()->create();
        $department = DepartmentModel::factory()->create([
            'head_employee_id' => $headEmployee->id,
        ]);

        $this->assertInstanceOf(EmployeeModel::class, $department->headEmployee);
        $this->assertEquals($headEmployee->id, $department->headEmployee->id);
    }

    public function test_department_belongs_to_parent_department(): void
    {
        $parentDepartment = DepartmentModel::factory()->create();
        $childDepartment = DepartmentModel::factory()->create([
            'parent_department_id' => $parentDepartment->id,
        ]);

        $this->assertInstanceOf(DepartmentModel::class, $childDepartment->parentDepartment);
        $this->assertEquals($parentDepartment->id, $childDepartment->parentDepartment->id);
    }

    public function test_department_has_many_child_departments(): void
    {
        $parentDepartment = DepartmentModel::factory()->create();
        $childDepartments = DepartmentModel::factory()->count(3)->create([
            'parent_department_id' => $parentDepartment->id,
        ]);

        $this->assertCount(3, $parentDepartment->childDepartments);
        
        foreach ($childDepartments as $child) {
            $this->assertTrue($parentDepartment->childDepartments->contains($child));
        }
    }

    public function test_department_has_many_employees(): void
    {
        $department = DepartmentModel::factory()->create();
        $employees = EmployeeModel::factory()->count(5)->create([
            'department_id' => $department->id,
        ]);

        $this->assertCount(5, $department->employees);
        
        foreach ($employees as $employee) {
            $this->assertTrue($department->employees->contains($employee));
        }
    }

    public function test_department_has_many_positions(): void
    {
        $department = DepartmentModel::factory()->create();
        $positions = PositionModel::factory()->count(3)->create([
            'department_id' => $department->id,
        ]);

        $this->assertCount(3, $department->positions);
        
        foreach ($positions as $position) {
            $this->assertTrue($department->positions->contains($position));
        }
    }

    public function test_active_scope_filters_active_departments(): void
    {
        DepartmentModel::factory()->count(3)->create(['is_active' => true]);
        DepartmentModel::factory()->count(2)->create(['is_active' => false]);

        $activeDepartments = DepartmentModel::active()->get();

        $this->assertCount(3, $activeDepartments);
        
        foreach ($activeDepartments as $department) {
            $this->assertTrue($department->is_active);
        }
    }

    public function test_with_employee_count_scope_includes_employee_count(): void
    {
        $department = DepartmentModel::factory()->create();
        EmployeeModel::factory()->count(4)->create(['department_id' => $department->id]);

        $departmentWithCount = DepartmentModel::withEmployeeCount()->find($department->id);

        $this->assertEquals(4, $departmentWithCount->employees_count);
    }

    public function test_with_active_employees_scope_loads_only_active_employees(): void
    {
        $department = DepartmentModel::factory()->create();
        EmployeeModel::factory()->count(3)->create([
            'department_id' => $department->id,
            'employment_status' => 'active',
        ]);
        EmployeeModel::factory()->count(2)->create([
            'department_id' => $department->id,
            'employment_status' => 'terminated',
        ]);

        $departmentWithActiveEmployees = DepartmentModel::withActiveEmployees()->find($department->id);

        $this->assertCount(3, $departmentWithActiveEmployees->employees);
        
        foreach ($departmentWithActiveEmployees->employees as $employee) {
            $this->assertEquals('active', $employee->employment_status);
        }
    }

    public function test_department_can_be_created_with_factory(): void
    {
        $department = DepartmentModel::factory()->create();

        $this->assertInstanceOf(DepartmentModel::class, $department);
        $this->assertNotNull($department->name);
        $this->assertNotNull($department->description);
        $this->assertIsBool($department->is_active);
    }

    public function test_department_factory_states_work_correctly(): void
    {
        $activeDepartment = DepartmentModel::factory()->active()->create();
        $inactiveDepartment = DepartmentModel::factory()->inactive()->create();

        $this->assertTrue($activeDepartment->is_active);
        $this->assertFalse($inactiveDepartment->is_active);
    }

    public function test_department_soft_deletes(): void
    {
        $department = DepartmentModel::factory()->create();
        $departmentId = $department->id;

        $department->delete();

        $this->assertSoftDeleted('departments', ['id' => $departmentId]);
        $this->assertNotNull($department->fresh()->deleted_at);
    }
}