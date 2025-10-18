<?php

namespace Tests\Feature\Routes;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EmployeeRouteRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $adminUser;

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
            Permission::create([
                'name' => $permission, 
                'slug' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Create roles
        $hrRole = Role::create(['name' => 'HR Manager', 'slug' => 'hr-manager', 'guard_name' => 'web']);
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'guard_name' => 'web']);

        // Assign all permissions to admin
        $adminRole->givePermissionTo($permissions);
        
        // Assign specific permissions to HR
        $hrRole->givePermissionTo([
            'view-employees', 'create-employees', 'edit-employees',
            'view-departments', 'view-positions', 'view-performance'
        ]);

        // Create users
        $this->user = User::factory()->create();
        $this->user->assignRole($hrRole);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);
    }

    public function test_employee_routes_are_registered(): void
    {
        $expectedRoutes = [
            'employees.index',
            'employees.create',
            'employees.store',
            'employees.show',
            'employees.edit',
            'employees.update',
            'employees.destroy',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route {$routeName} is not registered"
            );
        }
    }

    public function test_department_routes_are_registered(): void
    {
        $expectedRoutes = [
            'departments.index',
            'departments.create',
            'departments.store',
            'departments.show',
            'departments.edit',
            'departments.update',
            'departments.destroy',
            'departments.hierarchy',
            'departments.assign-head',
            'departments.remove-head',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route {$routeName} is not registered"
            );
        }
    }

    public function test_position_routes_are_registered(): void
    {
        $expectedRoutes = [
            'positions.index',
            'positions.create',
            'positions.store',
            'positions.show',
            'positions.edit',
            'positions.update',
            'positions.destroy',
            'positions.by-department',
            'positions.salary-ranges',
            'positions.commission-eligible',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route {$routeName} is not registered"
            );
        }
    }

    public function test_performance_routes_are_registered(): void
    {
        $expectedRoutes = [
            'performance.index',
            'performance.create',
            'performance.store',
            'performance.show',
            'performance.edit',
            'performance.update',
            'performance.destroy',
            'performance.analytics',
            'performance.goals.set',
            'performance.goals.track',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route {$routeName} is not registered"
            );
        }
    }

    public function test_commission_routes_are_registered(): void
    {
        $expectedRoutes = [
            'commissions.index',
            'commissions.calculate',
            'commissions.store',
            'commissions.approve',
            'commissions.mark-paid',
            'commissions.reports.monthly',
            'commissions.reports.quarterly',
            'commissions.analytics',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route {$routeName} is not registered"
            );
        }
    }

    public function test_api_routes_are_registered(): void
    {
        $expectedRoutes = [
            'api.employee.profile',
            'api.employee.performance-summary',
            'api.employee.client-portfolio',
            'api.admin.employee-management-summary',
            'api.admin.department-overview',
            'api.admin.performance-stats',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route {$routeName} is not registered"
            );
        }
    }

    public function test_employee_routes_have_correct_middleware(): void
    {
        $route = Route::getRoutes()->getByName('employees.index');
        $middleware = $route->gatherMiddleware();

        $this->assertContains('auth', $middleware);
        $this->assertContains('verified', $middleware);
        $this->assertContains('can:view-employees', $middleware);
    }

    public function test_department_management_routes_have_correct_middleware(): void
    {
        $route = Route::getRoutes()->getByName('departments.create');
        $middleware = $route->gatherMiddleware();

        $this->assertContains('auth', $middleware);
        $this->assertContains('verified', $middleware);
        $this->assertContains('can:view-departments', $middleware);
        $this->assertContains('can:create-departments', $middleware);
    }

    public function test_commission_routes_have_correct_middleware(): void
    {
        $route = Route::getRoutes()->getByName('commissions.approve');
        $middleware = $route->gatherMiddleware();

        $this->assertContains('auth', $middleware);
        $this->assertContains('verified', $middleware);
        $this->assertContains('can:view-commissions', $middleware);
        $this->assertContains('can:approve-commissions', $middleware);
    }

    public function test_admin_api_routes_have_admin_middleware(): void
    {
        $route = Route::getRoutes()->getByName('api.admin.employee-management-summary');
        $middleware = $route->gatherMiddleware();

        $this->assertContains('auth', $middleware);
        $this->assertContains('verified', $middleware);
        $this->assertContains('admin', $middleware);
    }

    public function test_route_parameters_are_correctly_bound(): void
    {
        // Test employee parameter binding
        $employee = EmployeeModel::factory()->create();
        $route = route('employees.show', $employee);
        $this->assertStringContains((string)$employee->id, $route);

        // Test department parameter binding
        $department = DepartmentModel::factory()->create();
        $route = route('departments.show', $department);
        $this->assertStringContains((string)$department->id, $route);

        // Test position parameter binding
        $position = PositionModel::factory()->create();
        $route = route('positions.show', $position);
        $this->assertStringContains((string)$position->id, $route);
    }

    public function test_route_uri_patterns_are_correct(): void
    {
        $routePatterns = [
            'employees.index' => 'employees',
            'employees.create' => 'employees/create',
            'employees.store' => 'employees',
            'departments.hierarchy' => 'departments/hierarchy/tree',
            'positions.by-department' => 'positions/department/{department}',
            'performance.analytics' => 'performance/analytics/dashboard',
            'commissions.reports.monthly' => 'commissions/reports/monthly',
        ];

        foreach ($routePatterns as $routeName => $expectedUri) {
            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route, "Route {$routeName} not found");
            $this->assertEquals($expectedUri, $route->uri(), "Route {$routeName} has incorrect URI pattern");
        }
    }

    public function test_route_methods_are_correct(): void
    {
        $routeMethods = [
            'employees.index' => ['GET', 'HEAD'],
            'employees.store' => ['POST'],
            'employees.update' => ['PUT', 'PATCH'],
            'employees.destroy' => ['DELETE'],
            'departments.assign-head' => ['POST'],
            'departments.remove-head' => ['DELETE'],
            'commissions.calculate' => ['POST'],
            'commissions.approve' => ['PATCH'],
        ];

        foreach ($routeMethods as $routeName => $expectedMethods) {
            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route, "Route {$routeName} not found");
            
            foreach ($expectedMethods as $method) {
                $this->assertContains($method, $route->methods(), 
                    "Route {$routeName} does not support {$method} method");
            }
        }
    }
}