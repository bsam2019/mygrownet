<?php

namespace Tests\Integration\Employee;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Services\EmployeeRoleIntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserPermissionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function employee_user_account_linking_works()
    {
        // Create user account
        $user = User::factory()->create([
            'email' => 'employee@test.com',
            'name' => 'John Employee',
        ]);

        // Create employee record
        $department = DepartmentModel::factory()->create(['name' => 'HR']);
        $position = PositionModel::factory()->create([
            'title' => 'HR Manager',
            'department_id' => $department->id,
        ]);

        $employee = EmployeeModel::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Employee',
            'email' => 'employee@test.com',
            'user_id' => $user->id,
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        // Assert linking works
        $this->assertEquals($user->id, $employee->user_id);
        $this->assertEquals($user->email, $employee->email);
    }

    /** @test */
    public function role_based_access_control_works()
    {
        // Create permissions
        $viewEmployees = Permission::create([
            'name' => 'view employees',
            'guard_name' => 'web',
        ]);

        $manageEmployees = Permission::create([
            'name' => 'manage employees',
            'guard_name' => 'web',
        ]);

        // Create roles
        $hrManager = Role::create([
            'name' => 'HR Manager',
            'guard_name' => 'web',
        ]);

        $employee = Role::create([
            'name' => 'Employee',
            'guard_name' => 'web',
        ]);

        // Assign permissions to roles
        $hrManager->givePermissionTo($viewEmployees, $manageEmployees);
        $employee->givePermissionTo($viewEmployees);

        // Create users and assign roles
        $hrUser = User::factory()->create(['email' => 'hr@test.com']);
        $regularUser = User::factory()->create(['email' => 'employee@test.com']);

        $hrUser->assignRole($hrManager);
        $regularUser->assignRole($employee);

        // Assert permissions work
        $this->assertTrue($hrUser->can('view employees'));
        $this->assertTrue($hrUser->can('manage employees'));
        $this->assertTrue($regularUser->can('view employees'));
        $this->assertFalse($regularUser->can('manage employees'));
    }

    /** @test */
    public function employee_role_integration_service_works()
    {
        // Create test data
        $user = User::factory()->create(['email' => 'manager@test.com']);
        
        $department = DepartmentModel::factory()->create(['name' => 'Sales']);
        $position = PositionModel::factory()->create([
            'title' => 'Sales Manager',
            'department_id' => $department->id,
        ]);

        $employee = EmployeeModel::factory()->create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employment_status' => 'active',
        ]);

        // Create role integration service
        $service = app(EmployeeRoleIntegrationService::class);
        
        // Test role assignment based on position
        $result = $service->assignRoleBasedOnPosition($employee);
        
        // Assert service works
        $this->assertTrue($result);
        $this->assertNotEmpty($user->fresh()->roles);
    }
}