<?php

namespace Tests\Feature\Http\Controllers\Employee;

use Tests\TestCase;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EmployeeControllerBasicTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private DepartmentModel $department;
    private PositionModel $position;

    protected function setUp(): void
    {
        parent::setUp();

        // Create basic permission
        Permission::create([
            'name' => 'view_employees', 
            'slug' => 'view-employees',
            'guard_name' => 'web'
        ]);
        
        // Create admin role with permission
        $adminRole = Role::create([
            'name' => 'admin', 
            'slug' => 'admin',
            'guard_name' => 'web'
        ]);
        $adminRole->givePermissionTo('view_employees');

        // Create test user
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);

        // Create test data
        $this->department = DepartmentModel::factory()->create([
            'name' => 'Human Resources',
            'is_active' => true
        ]);

        $this->position = PositionModel::factory()->create([
            'title' => 'HR Specialist',
            'department_id' => $this->department->id,
            'min_salary' => 5000,
            'max_salary' => 8000,
            'is_active' => true
        ]);
    }

    public function test_employee_index_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('employees.index'));

        $response->assertOk();
    }

    public function test_unauthorized_user_cannot_access_employees()
    {
        $regularUser = User::factory()->create();
        
        $response = $this->actingAs($regularUser)
            ->get(route('employees.index'));

        $response->assertForbidden();
    }
}