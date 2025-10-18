<?php

namespace Tests\Unit\Http\Middleware;

use App\Domain\Employee\Constants\EmployeePermissions;
use App\Http\Middleware\CheckDepartmentAccess;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Tests\TestCase;

class CheckDepartmentAccessTest extends TestCase
{
    use RefreshDatabase;

    private CheckDepartmentAccess $middleware;
    private User $hrManager;
    private User $departmentHead;
    private User $employee;
    private DepartmentModel $department;
    private DepartmentModel $otherDepartment;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->middleware = new CheckDepartmentAccess();
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Create permissions
        $permissions = EmployeePermissions::getAllPermissions();
        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission, 
                'guard_name' => 'web',
                'slug' => str_replace('_', '-', $permission)
            ]);
        }

        // Create roles
        $hrRole = Role::create(['name' => 'HR Manager', 'guard_name' => 'web', 'slug' => 'hr-manager']);
        $deptHeadRole = Role::create(['name' => 'Department Head', 'guard_name' => 'web', 'slug' => 'department-head']);
        $empRole = Role::create(['name' => 'Employee', 'guard_name' => 'web', 'slug' => 'employee']);

        // Assign permissions
        $hrRole->givePermissionTo([
            EmployeePermissions::VIEW_EMPLOYEES,
            EmployeePermissions::EDIT_EMPLOYEES,
        ]);

        $deptHeadRole->givePermissionTo([
            EmployeePermissions::VIEW_DEPARTMENT_EMPLOYEES,
            EmployeePermissions::MANAGE_DEPARTMENT_EMPLOYEES,
        ]);

        $empRole->givePermissionTo([
            EmployeePermissions::VIEW_PERFORMANCE,
        ]);

        // Create users
        $this->hrManager = User::factory()->create();
        $this->departmentHead = User::factory()->create();
        $this->employee = User::factory()->create();

        // Assign roles
        $this->hrManager->assignRole($hrRole);
        $this->departmentHead->assignRole($deptHeadRole);
        $this->employee->assignRole($empRole);

        // Create departments
        $this->department = DepartmentModel::factory()->create();
        $this->otherDepartment = DepartmentModel::factory()->create();

        // Create employee records
        EmployeeModel::factory()->create([
            'user_id' => $this->departmentHead->id,
            'department_id' => $this->department->id,
        ]);

        EmployeeModel::factory()->create([
            'user_id' => $this->employee->id,
            'department_id' => $this->department->id,
        ]);
    }

    public function test_unauthenticated_user_is_rejected(): void
    {
        $request = Request::create('/test');
        
        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertStringContainsString('Unauthorized', $response->getContent());
    }

    public function test_hr_manager_has_global_access(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->hrManager);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_department_head_can_access_own_department(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->departmentHead);
        
        // Mock route with department parameter
        $route = new Route(['GET'], '/test/{department}', []);
        $route->bind($request);
        $route->setParameter('department', $this->department->id);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_department_head_cannot_access_other_department(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->departmentHead);
        
        // Mock route with other department parameter
        $route = new Route(['GET'], '/test/{department}', []);
        $route->bind($request);
        $route->setParameter('department', $this->otherDepartment->id);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContainsString('Forbidden', $response->getContent());
    }

    public function test_employee_without_department_permissions_is_rejected(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->employee);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_department_access_with_query_parameter(): void
    {
        $request = Request::create('/test?department_id=' . $this->department->id);
        $request->setUserResolver(fn() => $this->departmentHead);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_department_access_with_request_body(): void
    {
        $request = Request::create('/test', 'POST', ['department_id' => $this->department->id]);
        $request->setUserResolver(fn() => $this->departmentHead);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_with_specific_permission_parameter(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->departmentHead);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'view_employees');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_employee_without_required_permission_is_rejected(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->employee);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'manage_employees');

        $this->assertEquals(403, $response->getStatusCode());
    }
}