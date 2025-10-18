<?php

namespace Tests\Unit\Services;

use App\Domain\Employee\Constants\EmployeePermissions;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\EmployeeRoleIntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeRoleIntegrationServiceTest extends TestCase
{
    use RefreshDatabase;

    private EmployeeRoleIntegrationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new EmployeeRoleIntegrationService();
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Create permissions using the Permission model's findOrCreate method
        $permissions = EmployeePermissions::getAllPermissions();
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Create basic roles using the Role model's findOrCreate method
        Role::findOrCreate('Admin', 'web');
        Role::findOrCreate('HR Manager', 'web');
        Role::findOrCreate('Department Head', 'web');
        Role::findOrCreate('Employee', 'web');
    }

    public function test_extend_existing_roles_adds_permissions(): void
    {
        $this->service->extendExistingRoles();

        // Check HR Manager permissions
        $hrManager = Role::where('name', 'HR Manager')->first();
        $this->assertTrue($hrManager->hasPermissionTo(EmployeePermissions::VIEW_EMPLOYEES));
        $this->assertTrue($hrManager->hasPermissionTo(EmployeePermissions::CREATE_EMPLOYEES));
        $this->assertTrue($hrManager->hasPermissionTo(EmployeePermissions::DELETE_EMPLOYEES));

        // Check Department Head permissions
        $departmentHead = Role::where('name', 'Department Head')->first();
        $this->assertTrue($departmentHead->hasPermissionTo(EmployeePermissions::VIEW_DEPARTMENT_EMPLOYEES));
        $this->assertTrue($departmentHead->hasPermissionTo(EmployeePermissions::MANAGE_DEPARTMENT_EMPLOYEES));
        $this->assertFalse($departmentHead->hasPermissionTo(EmployeePermissions::DELETE_EMPLOYEES));

        // Check Employee permissions
        $employee = Role::where('name', 'Employee')->first();
        $this->assertTrue($employee->hasPermissionTo(EmployeePermissions::VIEW_PERFORMANCE));
        $this->assertTrue($employee->hasPermissionTo(EmployeePermissions::VIEW_COMMISSIONS));
        $this->assertFalse($employee->hasPermissionTo(EmployeePermissions::CREATE_EMPLOYEES));
    }

    public function test_field_agent_role_is_created(): void
    {
        $this->service->extendExistingRoles();

        $fieldAgent = Role::where('name', 'Field Agent')->first();
        $this->assertNotNull($fieldAgent);
        $this->assertTrue($fieldAgent->hasPermissionTo(EmployeePermissions::VIEW_CLIENT_ASSIGNMENTS));
        $this->assertTrue($fieldAgent->hasPermissionTo(EmployeePermissions::VIEW_PERFORMANCE));
        $this->assertFalse($fieldAgent->hasPermissionTo(EmployeePermissions::MANAGE_CLIENT_ASSIGNMENTS));
    }

    public function test_admin_role_gets_all_permissions(): void
    {
        $this->service->extendExistingRoles();

        $admin = Role::where('name', 'Admin')->first();
        $allPermissions = EmployeePermissions::getAllPermissions();

        foreach ($allPermissions as $permission) {
            $this->assertTrue($admin->hasPermissionTo($permission));
        }
    }

    public function test_assign_field_agent_roles_based_on_position(): void
    {
        // Create test data
        $user = User::factory()->create();
        $department = DepartmentModel::factory()->create();
        $position = PositionModel::factory()->create([
            'title' => 'Field Agent',
            'department_id' => $department->id,
        ]);
        
        EmployeeModel::factory()->create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'department_id' => $department->id,
        ]);

        // First create the Field Agent role
        $this->service->extendExistingRoles();
        
        // Then assign roles
        $this->service->assignFieldAgentRoles();

        $user->refresh();
        $this->assertTrue($user->hasRole('Field Agent'));
    }

    public function test_validate_role_hierarchy_detects_issues(): void
    {
        // Create roles with incorrect permissions
        $hrManager = Role::where('name', 'HR Manager')->first();
        $departmentHead = Role::where('name', 'Department Head')->first();
        
        // Give Department Head a permission that HR Manager doesn't have
        $departmentHead->givePermissionTo(EmployeePermissions::VIEW_EMPLOYEES);

        $issues = $this->service->validateRoleHierarchy();
        
        $this->assertNotEmpty($issues);
        $this->assertStringContainsString('HR Manager is missing permissions', $issues[0]);
    }

    public function test_validate_role_hierarchy_passes_with_correct_setup(): void
    {
        $this->service->extendExistingRoles();
        
        $issues = $this->service->validateRoleHierarchy();
        
        $this->assertEmpty($issues);
    }

    public function test_get_role_permission_summary_returns_correct_data(): void
    {
        $this->service->extendExistingRoles();
        
        $summary = $this->service->getRolePermissionSummary();
        
        $this->assertArrayHasKey('HR Manager', $summary);
        $this->assertArrayHasKey('Department Head', $summary);
        $this->assertArrayHasKey('Employee', $summary);
        $this->assertArrayHasKey('Field Agent', $summary);
        
        // HR Manager should have the most employee permissions
        $this->assertGreaterThan(
            $summary['Department Head']['employee_permissions'],
            $summary['HR Manager']['employee_permissions']
        );
        
        // Department Head should have more than Employee
        $this->assertGreaterThan(
            $summary['Employee']['employee_permissions'],
            $summary['Department Head']['employee_permissions']
        );
    }

    public function test_sync_user_roles_with_employee_data_assigns_department_head(): void
    {
        $user = User::factory()->create();
        $department = DepartmentModel::factory()->create();
        $position = PositionModel::factory()->create([
            'title' => 'Manager',
            'department_id' => $department->id,
            'is_department_head' => true,
        ]);
        
        EmployeeModel::factory()->create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'department_id' => $department->id,
        ]);

        // Create the Department Head role first
        $this->service->extendExistingRoles();
        
        $this->service->syncUserRolesWithEmployeeData();

        $user->refresh();
        $this->assertTrue($user->hasRole('Department Head'));
    }

    public function test_sync_user_roles_with_employee_data_assigns_hr_manager(): void
    {
        $user = User::factory()->create();
        $department = DepartmentModel::factory()->create();
        $position = PositionModel::factory()->create([
            'title' => 'HR Manager',
            'department_id' => $department->id,
        ]);
        
        EmployeeModel::factory()->create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'department_id' => $department->id,
        ]);

        // Create the HR Manager role first
        $this->service->extendExistingRoles();
        
        $this->service->syncUserRolesWithEmployeeData();

        $user->refresh();
        $this->assertTrue($user->hasRole('HR Manager'));
    }

    public function test_sync_user_roles_assigns_permissions_for_hr_department(): void
    {
        $user = User::factory()->create();
        $hrDepartment = DepartmentModel::factory()->create(['name' => 'Human Resources']);
        $position = PositionModel::factory()->create([
            'title' => 'HR Specialist',
            'department_id' => $hrDepartment->id,
        ]);
        
        EmployeeModel::factory()->create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'department_id' => $hrDepartment->id,
        ]);

        // Create roles first
        $this->service->extendExistingRoles();
        
        $this->service->syncUserRolesWithEmployeeData();

        $user->refresh();
        $this->assertTrue($user->hasRole('Employee'));
        $this->assertTrue($user->hasPermissionTo(EmployeePermissions::VIEW_EMPLOYEES));
        $this->assertTrue($user->hasPermissionTo(EmployeePermissions::VIEW_DEPARTMENTS));
    }

    public function test_migrate_existing_permissions_maps_old_to_new(): void
    {
        // Create old permissions and assign to a role
        $oldPermission = Permission::findOrCreate('view_users', 'web');
        $testRole = Role::findOrCreate('Test Role', 'web');
        
        $testRole->givePermissionTo($oldPermission);

        $this->service->migrateExistingPermissions();

        $testRole->refresh();
        $this->assertTrue($testRole->hasPermissionTo(EmployeePermissions::VIEW_EMPLOYEES));
    }
}