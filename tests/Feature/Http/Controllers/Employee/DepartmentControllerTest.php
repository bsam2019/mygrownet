<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Employee;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DepartmentControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_departments(): void
    {
        // Create test departments
        $parentDepartment = DepartmentModel::factory()->create([
            'name' => 'Engineering',
            'description' => 'Software Engineering Department'
        ]);
        
        $childDepartment = DepartmentModel::factory()->create([
            'name' => 'Frontend Team',
            'description' => 'Frontend Development Team',
            'parent_department_id' => $parentDepartment->id
        ]);

        $response = $this->get(route('departments.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Employee/Departments/Index')
                ->has('departments.data', 2)
                ->where('departments.data.0.name', 'Engineering')
                ->where('departments.data.1.name', 'Frontend Team')
        );
    }

    public function test_can_filter_departments_by_search(): void
    {
        DepartmentModel::factory()->create(['name' => 'Engineering']);
        DepartmentModel::factory()->create(['name' => 'Marketing']);
        DepartmentModel::factory()->create(['name' => 'Sales']);

        $response = $this->get(route('departments.index', ['search' => 'Eng']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('departments.data', 1)
                ->where('departments.data.0.name', 'Engineering')
        );
    }

    public function test_can_get_hierarchical_department_structure(): void
    {
        $parent = DepartmentModel::factory()->create(['name' => 'Engineering']);
        $child1 = DepartmentModel::factory()->create([
            'name' => 'Frontend',
            'parent_department_id' => $parent->id
        ]);
        $child2 = DepartmentModel::factory()->create([
            'name' => 'Backend',
            'parent_department_id' => $parent->id
        ]);

        $response = $this->get(route('departments.index', ['hierarchical' => true]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('departments', 1)
                ->where('departments.0.name', 'Engineering')
                ->has('departments.0.children', 2)
        );
    }

    public function test_can_show_department_details(): void
    {
        $department = DepartmentModel::factory()->create([
            'name' => 'Engineering',
            'description' => 'Software Engineering Department'
        ]);

        // Create some employees and positions for statistics
        $position = PositionModel::factory()->create(['department_id' => $department->id]);
        $employee = EmployeeModel::factory()->create([
            'department_id' => $department->id,
            'position_id' => $position->id
        ]);

        $response = $this->get(route('departments.show', $department));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Employee/Departments/Show')
                ->where('department.name', 'Engineering')
                ->has('statistics')
                ->where('statistics.total_employees', 1)
                ->where('statistics.total_positions', 1)
        );
    }

    public function test_can_create_department(): void
    {
        $response = $this->get(route('departments.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Employee/Departments/Create')
                ->has('parent_departments')
        );
    }

    public function test_can_store_new_department(): void
    {
        $departmentData = [
            'name' => 'New Department',
            'description' => 'A new department for testing',
            'is_active' => true
        ];

        $response = $this->post(route('departments.store'), $departmentData);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Department created successfully'
        ]);

        $this->assertDatabaseHas('departments', [
            'name' => 'New Department',
            'description' => 'A new department for testing',
            'is_active' => true
        ]);
    }

    public function test_can_store_department_with_parent(): void
    {
        $parentDepartment = DepartmentModel::factory()->create();

        $departmentData = [
            'name' => 'Child Department',
            'description' => 'A child department',
            'parent_department_id' => $parentDepartment->id,
            'is_active' => true
        ];

        $response = $this->post(route('departments.store'), $departmentData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('departments', [
            'name' => 'Child Department',
            'parent_department_id' => $parentDepartment->id
        ]);
    }

    public function test_validates_required_fields_when_storing(): void
    {
        $response = $this->post(route('departments.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'description']);
    }

    public function test_validates_unique_department_name(): void
    {
        DepartmentModel::factory()->create(['name' => 'Existing Department']);

        $response = $this->post(route('departments.store'), [
            'name' => 'Existing Department',
            'description' => 'Test description'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_can_edit_department(): void
    {
        $department = DepartmentModel::factory()->create();
        $parentDepartment = DepartmentModel::factory()->create();

        $response = $this->get(route('departments.edit', $department));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Employee/Departments/Edit')
                ->where('department.id', $department->id)
                ->has('parent_departments')
        );
    }

    public function test_can_update_department(): void
    {
        $department = DepartmentModel::factory()->create([
            'name' => 'Old Name',
            'description' => 'Old Description'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'is_active' => true
        ];

        $response = $this->put(route('departments.update', $department), $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Department updated successfully'
        ]);

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description'
        ]);
    }

    public function test_prevents_circular_reference_when_updating_parent(): void
    {
        $parent = DepartmentModel::factory()->create();
        $child = DepartmentModel::factory()->create(['parent_department_id' => $parent->id]);

        // Try to make parent a child of its own child
        $response = $this->put(route('departments.update', $parent), [
            'name' => $parent->name,
            'description' => $parent->description,
            'parent_department_id' => $child->id
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['parent_department_id']);
    }

    public function test_can_delete_empty_department(): void
    {
        $department = DepartmentModel::factory()->create();

        $response = $this->delete(route('departments.destroy', $department));

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Department deleted successfully'
        ]);

        $this->assertSoftDeleted('departments', ['id' => $department->id]);
    }

    public function test_cannot_delete_department_with_employees(): void
    {
        $department = DepartmentModel::factory()->create();
        $position = PositionModel::factory()->create(['department_id' => $department->id]);
        EmployeeModel::factory()->create([
            'department_id' => $department->id,
            'position_id' => $position->id
        ]);

        $response = $this->delete(route('departments.destroy', $department));

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Cannot delete department with active employees. Please reassign employees first.'
        ]);
    }

    public function test_cannot_delete_department_with_child_departments(): void
    {
        $parent = DepartmentModel::factory()->create();
        DepartmentModel::factory()->create(['parent_department_id' => $parent->id]);

        $response = $this->delete(route('departments.destroy', $parent));

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Cannot delete department with child departments. Please reassign or delete child departments first.'
        ]);
    }

    public function test_can_get_department_hierarchy_api(): void
    {
        $parent = DepartmentModel::factory()->create(['name' => 'Engineering']);
        $child = DepartmentModel::factory()->create([
            'name' => 'Frontend',
            'parent_department_id' => $parent->id
        ]);

        $response = $this->get(route('departments.hierarchy'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'hierarchy' => [
                '*' => [
                    'id',
                    'name',
                    'children'
                ]
            ]
        ]);
    }

    public function test_can_assign_department_head(): void
    {
        $department = DepartmentModel::factory()->create();
        $position = PositionModel::factory()->create(['department_id' => $department->id]);
        $employee = EmployeeModel::factory()->create([
            'department_id' => $department->id,
            'position_id' => $position->id
        ]);

        $response = $this->post(route('departments.assign-head', $department), [
            'employee_id' => $employee->id
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Department head assigned successfully'
        ]);

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'head_employee_id' => $employee->id
        ]);
    }

    public function test_can_remove_department_head(): void
    {
        $department = DepartmentModel::factory()->create();
        $position = PositionModel::factory()->create(['department_id' => $department->id]);
        $employee = EmployeeModel::factory()->create([
            'department_id' => $department->id,
            'position_id' => $position->id
        ]);
        
        $department->update(['head_employee_id' => $employee->id]);

        $response = $this->delete(route('departments.remove-head', $department));

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Department head removed successfully'
        ]);

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'head_employee_id' => null
        ]);
    }

    public function test_can_get_departments_as_json(): void
    {
        DepartmentModel::factory()->count(3)->create();

        $response = $this->getJson(route('departments.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'departments' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'is_active',
                        'employees_count',
                        'positions_count'
                    ]
                ]
            ],
            'meta' => [
                'total_count',
                'active_count'
            ]
        ]);
    }
}