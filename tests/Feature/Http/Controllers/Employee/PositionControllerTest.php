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

class PositionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private DepartmentModel $department;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        $this->department = DepartmentModel::factory()->create([
            'name' => 'Engineering',
            'description' => 'Software Engineering Department'
        ]);
    }

    public function test_can_list_positions(): void
    {
        PositionModel::factory()->count(3)->create([
            'department_id' => $this->department->id
        ]);

        $response = $this->get(route('positions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Employee/Positions/Index')
                ->has('positions.data', 3)
                ->has('departments')
                ->has('meta')
        );
    }

    public function test_can_filter_positions_by_search(): void
    {
        PositionModel::factory()->create([
            'title' => 'Senior Developer',
            'department_id' => $this->department->id
        ]);
        PositionModel::factory()->create([
            'title' => 'Marketing Manager',
            'department_id' => $this->department->id
        ]);

        $response = $this->get(route('positions.index', ['search' => 'Developer']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('positions.data', 1)
                ->where('positions.data.0.title', 'Senior Developer')
        );
    }

    public function test_can_filter_positions_by_department(): void
    {
        $otherDepartment = DepartmentModel::factory()->create();
        
        PositionModel::factory()->create([
            'title' => 'Developer',
            'department_id' => $this->department->id
        ]);
        PositionModel::factory()->create([
            'title' => 'Manager',
            'department_id' => $otherDepartment->id
        ]);

        $response = $this->get(route('positions.index', [
            'department_id' => $this->department->id
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('positions.data', 1)
                ->where('positions.data.0.title', 'Developer')
        );
    }

    public function test_can_filter_commission_eligible_positions(): void
    {
        PositionModel::factory()->create([
            'title' => 'Sales Rep',
            'department_id' => $this->department->id,
            'base_commission_rate' => 5.0
        ]);
        PositionModel::factory()->create([
            'title' => 'Developer',
            'department_id' => $this->department->id,
            'base_commission_rate' => 0.0
        ]);

        $response = $this->get(route('positions.index', [
            'commission_eligible' => true
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('positions.data', 1)
                ->where('positions.data.0.title', 'Sales Rep')
        );
    }

    public function test_can_filter_positions_by_salary_range(): void
    {
        PositionModel::factory()->create([
            'title' => 'Junior Developer',
            'department_id' => $this->department->id,
            'min_salary' => 30000,
            'max_salary' => 40000
        ]);
        PositionModel::factory()->create([
            'title' => 'Senior Developer',
            'department_id' => $this->department->id,
            'min_salary' => 60000,
            'max_salary' => 80000
        ]);

        $response = $this->get(route('positions.index', [
            'min_salary' => 50000
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('positions.data', 1)
                ->where('positions.data.0.title', 'Senior Developer')
        );
    }

    public function test_can_show_position_details(): void
    {
        $position = PositionModel::factory()->create([
            'title' => 'Senior Developer',
            'department_id' => $this->department->id
        ]);

        // Create an employee for statistics
        EmployeeModel::factory()->create([
            'department_id' => $this->department->id,
            'position_id' => $position->id
        ]);

        $response = $this->get(route('positions.show', $position));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Employee/Positions/Show')
                ->where('position.title', 'Senior Developer')
                ->has('statistics')
                ->where('statistics.total_employees', 1)
        );
    }

    public function test_can_create_position(): void
    {
        $response = $this->get(route('positions.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Employee/Positions/Create')
                ->has('departments')
        );
    }

    public function test_can_store_new_position(): void
    {
        $positionData = [
            'title' => 'New Position',
            'description' => 'A new position for testing',
            'department_id' => $this->department->id,
            'min_salary' => 40000,
            'max_salary' => 60000,
            'base_commission_rate' => 2.5,
            'performance_commission_rate' => 1.0,
            'permissions' => ['read', 'write'],
            'level' => 3,
            'is_active' => true
        ];

        $response = $this->post(route('positions.store'), $positionData);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Position created successfully'
        ]);

        $this->assertDatabaseHas('positions', [
            'title' => 'New Position',
            'department_id' => $this->department->id,
            'min_salary' => 40000,
            'max_salary' => 60000
        ]);
    }

    public function test_validates_required_fields_when_storing_position(): void
    {
        $response = $this->post(route('positions.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'title', 'description', 'department_id', 'min_salary', 'max_salary'
        ]);
    }

    public function test_validates_salary_range_when_storing(): void
    {
        $response = $this->post(route('positions.store'), [
            'title' => 'Test Position',
            'description' => 'Test Description',
            'department_id' => $this->department->id,
            'min_salary' => 60000,
            'max_salary' => 40000, // Invalid: max < min
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['max_salary']);
    }

    public function test_validates_commission_rates(): void
    {
        $response = $this->post(route('positions.store'), [
            'title' => 'Test Position',
            'description' => 'Test Description',
            'department_id' => $this->department->id,
            'min_salary' => 40000,
            'max_salary' => 60000,
            'base_commission_rate' => 150, // Invalid: > 100%
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['base_commission_rate']);
    }

    public function test_can_edit_position(): void
    {
        $position = PositionModel::factory()->create([
            'department_id' => $this->department->id
        ]);

        $response = $this->get(route('positions.edit', $position));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Employee/Positions/Edit')
                ->where('position.id', $position->id)
                ->has('departments')
        );
    }

    public function test_can_update_position(): void
    {
        $position = PositionModel::factory()->create([
            'title' => 'Old Title',
            'department_id' => $this->department->id
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'department_id' => $this->department->id,
            'min_salary' => 45000,
            'max_salary' => 65000,
            'is_active' => true
        ];

        $response = $this->put(route('positions.update', $position), $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Position updated successfully'
        ]);

        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'title' => 'Updated Title',
            'min_salary' => 45000,
            'max_salary' => 65000
        ]);
    }

    public function test_can_delete_empty_position(): void
    {
        $position = PositionModel::factory()->create([
            'department_id' => $this->department->id
        ]);

        $response = $this->delete(route('positions.destroy', $position));

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Position deleted successfully'
        ]);

        $this->assertSoftDeleted('positions', ['id' => $position->id]);
    }

    public function test_cannot_delete_position_with_employees(): void
    {
        $position = PositionModel::factory()->create([
            'department_id' => $this->department->id
        ]);
        
        EmployeeModel::factory()->create([
            'department_id' => $this->department->id,
            'position_id' => $position->id
        ]);

        $response = $this->delete(route('positions.destroy', $position));

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Cannot delete position with active employees. Please reassign employees first.'
        ]);
    }

    public function test_can_get_positions_by_department(): void
    {
        $position1 = PositionModel::factory()->create([
            'title' => 'Developer',
            'department_id' => $this->department->id
        ]);
        
        $otherDepartment = DepartmentModel::factory()->create();
        PositionModel::factory()->create([
            'title' => 'Manager',
            'department_id' => $otherDepartment->id
        ]);

        $response = $this->get(route('positions.by-department', $this->department));

        $response->assertStatus(200);
        $response->assertJson([
            'department' => [
                'id' => $this->department->id,
                'name' => $this->department->name
            ]
        ]);
        $response->assertJsonCount(1, 'positions');
        $response->assertJsonPath('positions.0.title', 'Developer');
    }

    public function test_can_get_salary_ranges_analysis(): void
    {
        PositionModel::factory()->create([
            'department_id' => $this->department->id,
            'min_salary' => 40000,
            'max_salary' => 60000
        ]);
        PositionModel::factory()->create([
            'department_id' => $this->department->id,
            'min_salary' => 50000,
            'max_salary' => 70000
        ]);

        $response = $this->get(route('positions.salary-ranges'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'overall_ranges' => [
                'overall_min',
                'overall_max',
                'avg_min',
                'avg_max'
            ],
            'department_ranges' => [
                '*' => [
                    'department_name',
                    'department_id',
                    'min_salary',
                    'max_salary',
                    'avg_min_salary',
                    'avg_max_salary',
                    'position_count'
                ]
            ]
        ]);
    }

    public function test_can_get_commission_eligible_positions(): void
    {
        PositionModel::factory()->create([
            'title' => 'Sales Rep',
            'department_id' => $this->department->id,
            'base_commission_rate' => 5.0
        ]);
        PositionModel::factory()->create([
            'title' => 'Developer',
            'department_id' => $this->department->id,
            'base_commission_rate' => 0.0
        ]);

        $response = $this->get(route('positions.commission-eligible'));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'positions');
        $response->assertJsonPath('positions.0.title', 'Sales Rep');
    }

    public function test_can_get_positions_as_json(): void
    {
        PositionModel::factory()->count(2)->create([
            'department_id' => $this->department->id
        ]);

        $response = $this->getJson(route('positions.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'positions' => [
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'min_salary',
                        'max_salary',
                        'is_active',
                        'employees_count',
                        'department'
                    ]
                ]
            ],
            'departments',
            'meta' => [
                'total_count',
                'active_count',
                'commission_eligible_count'
            ]
        ]);
    }
}