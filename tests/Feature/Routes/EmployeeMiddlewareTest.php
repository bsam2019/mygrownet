<?php

namespace Tests\Feature\Routes;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EmployeeMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $adminUser;
    protected User $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        $permissions = [
            'view-employees', 'create-employees', 'edit-employees', 'delete-employees',
            'view-departments', 'create-departments', 'edit-departments', 'delete-departments',
            'manage-department-heads',
            'view-positions', 'create-positions', 'edit-positions', 'delete-positions',
            'view-performance', 'create-performance-reviews', 'edit-performance-reviews', 
            'delete-performance-reviews', 'manage-performance-goals',
            'view-commissions', 'calculate-commissions', 'create-commissions', 
            'approve-commissions', 'process-commission-payments',
            'view-commission-reports', 'view-commission-analytics'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'slug' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $hrRole = Role::create(['name' => 'HR Manager', 'slug' => 'hr-manager', 'guard_name' => 'web']);
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'guard_name' => 'web']);
        $employeeRole = Role::create(['name' => 'Employee', 'slug' => 'employee', 'guard_name' => 'web']);

        // Assign permissions
        $adminRole->givePermissionTo($permissions);
        $hrRole->givePermissionTo([
            'view-employees', 'create-employees', 'edit-employees',
            'view-departments', 'view-positions', 'view-performance'
        ]);

        // Create users
        $this->user = User::factory()->create();
        $this->user->assignRole($hrRole);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);

        $this->unauthorizedUser = User::factory()->create();
        $this->unauthorizedUser->assignRole($employeeRole);
    }

    public function test_unauthenticated_users_cannot_access_employee_routes(): void
    {
        $response = $this->get(route('employees.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('departments.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('positions.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_users_without_permissions_cannot_access_protected_routes(): void
    {
        $this->actingAs($this->unauthorizedUser);

        // Test employee routes
        $response = $this->get(route('employees.index'));
        $response->assertStatus(403);

        $response = $this->get(route('employees.create'));
        $response->assertStatus(403);

        // Test department routes
        $response = $this->get(route('departments.index'));
        $response->assertStatus(403);

        // Test commission routes
        $response = $this->get(route('commissions.index'));
        $response->assertStatus(403);
    }

    public function test_hr_manager_can_access_permitted_routes(): void
    {
        $this->actingAs($this->user);

        // Can view employees
        $response = $this->get(route('employees.index'));
        $response->assertStatus(200);

        // Can create employees
        $response = $this->get(route('employees.create'));
        $response->assertStatus(200);

        // Can view departments
        $response = $this->get(route('departments.index'));
        $response->assertStatus(200);
    }

    public function test_hr_manager_cannot_access_restricted_routes(): void
    {
        $this->actingAs($this->user);

        // Cannot delete employees (no delete permission)
        $employee = EmployeeModel::factory()->create();
        $response = $this->delete(route('employees.destroy', $employee));
        $response->assertStatus(403);

        // Cannot manage department heads
        $department = DepartmentModel::factory()->create();
        $response = $this->post(route('departments.assign-head', $department), [
            'employee_id' => $employee->id
        ]);
        $response->assertStatus(403);
    }

    public function test_admin_can_access_all_employee_routes(): void
    {
        $this->actingAs($this->adminUser);

        // Can access all employee management routes
        $response = $this->get(route('employees.index'));
        $response->assertStatus(200);

        $response = $this->get(route('employees.create'));
        $response->assertStatus(200);

        // Can access department management
        $response = $this->get(route('departments.index'));
        $response->assertStatus(200);

        // Can access commission management
        $response = $this->get(route('commissions.index'));
        $response->assertStatus(200);

        // Can access analytics
        $response = $this->get(route('performance.analytics'));
        $response->assertStatus(200);
    }

    public function test_admin_api_routes_require_admin_middleware(): void
    {
        // Test without admin role
        $this->actingAs($this->user);
        $response = $this->get(route('api.admin.employee-management-summary'));
        $response->assertStatus(403);

        // Test with admin role
        $this->actingAs($this->adminUser);
        $response = $this->get(route('api.admin.employee-management-summary'));
        $response->assertStatus(200);
    }

    public function test_commission_approval_requires_specific_permission(): void
    {
        $commission = EmployeeCommissionModel::factory()->create();

        // User without approve permission
        $this->actingAs($this->user);
        $response = $this->patch(route('commissions.approve', $commission));
        $response->assertStatus(403);

        // Admin with approve permission
        $this->actingAs($this->adminUser);
        $response = $this->patch(route('commissions.approve', $commission));
        $response->assertStatus(200);
    }

    public function test_performance_goal_management_requires_specific_permission(): void
    {
        $employee = EmployeeModel::factory()->create();

        // User without manage goals permission
        $this->actingAs($this->user);
        $response = $this->post(route('performance.goals.set'), [
            'employee_id' => $employee->id,
            'goals' => ['Increase sales by 10%']
        ]);
        $response->assertStatus(403);

        // Admin with manage goals permission
        $this->actingAs($this->adminUser);
        $response = $this->post(route('performance.goals.set'), [
            'employee_id' => $employee->id,
            'goals' => ['Increase sales by 10%']
        ]);
        $response->assertStatus(200);
    }

    public function test_department_head_management_requires_specific_permission(): void
    {
        $department = DepartmentModel::factory()->create();
        $employee = EmployeeModel::factory()->create();

        // User without manage department heads permission
        $this->actingAs($this->user);
        $response = $this->post(route('departments.assign-head', $department), [
            'employee_id' => $employee->id
        ]);
        $response->assertStatus(403);

        // Admin with manage department heads permission
        $this->actingAs($this->adminUser);
        $response = $this->post(route('departments.assign-head', $department), [
            'employee_id' => $employee->id
        ]);
        $response->assertStatus(200);
    }

    public function test_commission_payment_processing_requires_specific_permission(): void
    {
        $commission = EmployeeCommissionModel::factory()->create([
            'status' => 'approved'
        ]);

        // User without payment processing permission
        $this->actingAs($this->user);
        $response = $this->patch(route('commissions.mark-paid', $commission));
        $response->assertStatus(403);

        // Admin with payment processing permission
        $this->actingAs($this->adminUser);
        $response = $this->patch(route('commissions.mark-paid', $commission));
        $response->assertStatus(200);
    }

    public function test_commission_reports_require_specific_permission(): void
    {
        // User without report viewing permission
        $this->actingAs($this->user);
        $response = $this->get(route('commissions.reports.monthly'));
        $response->assertStatus(403);

        // Admin with report viewing permission
        $this->actingAs($this->adminUser);
        $response = $this->get(route('commissions.reports.monthly'));
        $response->assertStatus(200);
    }

    public function test_middleware_is_applied_to_nested_route_groups(): void
    {
        // Test that middleware is properly applied to nested groups
        $this->actingAs($this->unauthorizedUser);

        // All employee routes should be protected
        $employeeRoutes = [
            'employees.index',
            'employees.create',
        ];

        foreach ($employeeRoutes as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertStatus(403);
        }

        // All department routes should be protected
        $departmentRoutes = [
            'departments.index',
            'departments.create',
            'departments.hierarchy'
        ];

        foreach ($departmentRoutes as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertStatus(403);
        }
    }

    public function test_route_specific_middleware_combinations(): void
    {
        $this->actingAs($this->user); // HR Manager with limited permissions

        // Test routes that require multiple permissions
        $employee = EmployeeModel::factory()->create();
        
        // Can view but cannot delete
        $response = $this->get(route('employees.show', $employee));
        $response->assertStatus(200);
        
        $response = $this->delete(route('employees.destroy', $employee));
        $response->assertStatus(403);

        // Can view departments but cannot create positions
        $response = $this->get(route('departments.index'));
        $response->assertStatus(200);
        
        $response = $this->get(route('positions.create'));
        $response->assertStatus(403);
    }

    public function test_api_routes_have_proper_middleware_protection(): void
    {
        $employee = EmployeeModel::factory()->create();

        // Test employee API routes
        $this->actingAs($this->user);
        $response = $this->get(route('api.employee.profile', $employee));
        $response->assertStatus(200);

        // Test admin API routes require admin middleware
        $response = $this->get(route('api.admin.employee-management-summary'));
        $response->assertStatus(403);

        // Test with admin user
        $this->actingAs($this->adminUser);
        $response = $this->get(route('api.admin.employee-management-summary'));
        $response->assertStatus(200);
    }
}