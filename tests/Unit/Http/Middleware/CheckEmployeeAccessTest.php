<?php

namespace Tests\Unit\Http\Middleware;

use App\Domain\Employee\Constants\EmployeePermissions;
use App\Http\Middleware\CheckEmployeeAccess;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

class CheckEmployeeAccessTest extends TestCase
{
    use RefreshDatabase;

    private CheckEmployeeAccess $middleware;
    private User $hrManager;
    private User $departmentHead;
    private User $employee;
    private EmployeeModel $testEmployee;
    private EmployeeModel $departmentHeadEmployee;
    private DepartmentModel $department;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->middleware = new CheckEmployeeAccess();
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
            EmployeePermissions::VIEW_ALL_PERFORMANCE,
            EmployeePermissions::VIEW_ALL_PAYROLL,
        ]);

        $deptHeadRole->givePermissionTo([
            EmployeePermissions::VIEW_DEPARTMENT_EMPLOYEES,
            EmployeePermissions::MANAGE_DEPARTMENT_EMPLOYEES,
            EmployeePermissions::VIEW_PERFORMANCE,
            EmployeePermissions::CREATE_PERFORMANCE_REVIEWS,
        ]);

        $empRole->givePermissionTo([
            EmployeePermissions::VIEW_PERFORMANCE,
            EmployeePermissions::VIEW_COMMISSIONS,
            EmployeePermissions::VIEW_PAYROLL,
        ]);

        // Create users
        $this->hrManager = User::factory()->create();
        $this->departmentHead = User::factory()->create();
        $this->employee = User::factory()->create();

        // Assign roles
        $this->hrManager->assignRole($hrRole);
        $this->departmentHead->assignRole($deptHeadRole);
        $this->employee->assignRole($empRole);

        // Create department and position
        $this->department = DepartmentModel::factory()->create();
        $managerPosition = PositionModel::factory()->create([
            'department_id' => $this->department->id,
            'is_department_head' => true,
        ]);

        // Create employee records
        $this->departmentHeadEmployee = EmployeeModel::factory()->create([
            'user_id' => $this->departmentHead->id,
            'department_id' => $this->department->id,
            'position_id' => $managerPosition->id,
        ]);

        $this->testEmployee = EmployeeModel::factory()->create([
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

    public function test_employee_not_found_returns_404(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->employee);
        
        // Mock route with non-existent employee
        $route = new Route(['GET'], '/test/{employee}', []);
        $route->bind($request);
        $route->setParameter('employee', 99999);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Employee not found', $response->getContent());
    }

    public function test_employee_can_view_own_record(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->employee);
        
        // Mock route with employee's own ID
        $route = new Route(['GET'], '/test/{employee}', []);
        $route->bind($request);
        $route->setParameter('employee', $this->testEmployee->id);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_hr_manager_can_view_any_employee(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->hrManager);
        
        // Mock route with any employee ID
        $route = new Route(['GET'], '/test/{employee}', []);
        $route->bind($request);
        $route->setParameter('employee', $this->testEmployee->id);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_department_head_can_view_department_employee(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->departmentHead);
        
        // Mock route with department employee ID
        $route = new Route(['GET'], '/test/{employee}', []);
        $route->bind($request);
        $route->setParameter('employee', $this->testEmployee->id);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_department_head_cannot_view_other_department_employee(): void
    {
        // Create employee in different department
        $otherDepartment = DepartmentModel::factory()->create();
        $otherEmployee = EmployeeModel::factory()->create([
            'department_id' => $otherDepartment->id,
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->departmentHead);
        
        // Mock route with other department employee ID
        $route = new Route(['GET'], '/test/{employee}', []);
        $route->bind($request);
        $route->setParameter('employee', $otherEmployee->id);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContainsString('Forbidden', $response->getContent());
    }

    public function test_employee_can_edit_own_record(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->employee);
        
        // Mock route with employee's own ID
        $route = new Route(['GET'], '/test/{employee}', []);
        $route->bind($request);
        $route->setParameter('employee', $this->testEmployee->id);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'edit');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_department_head_can_edit_department_employee(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->departmentHead);
        
        // Mock route with department employee ID
        $route = new Route(['GET'], '/test/{employee}', []);
        $route->bind($request);
        $route->setParameter('employee', $this->testEmployee->id);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'edit');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_employee_can_view_own_performance(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->employee);
        
        // Mock route with employee's own ID
        $route = new Route(['GET'], '/test/{employee}', []);
        $route->bind($request);
        $route->setParameter('employee', $this->testEmployee->id);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'view_performance');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_hr_manager_can_view_all_performance(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->hrManager);
        
        // Mock route with any employee ID
        $route = new Route(['GET'], '/test/{employee}', []);
        $route->bind($request);
        $route->setParameter('employee', $this->testEmployee->id);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'view_performance');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_department_head_can_manage_department_employee_performance(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->departmentHead);
        
        // Mock route with department employee ID
        $route = new Route(['GET'], '/test/{employee}', []);
        $route->bind($request);
        $route->setParameter('employee', $this->testEmployee->id);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'manage_performance');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_employee_cannot_manage_performance(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->employee);
        
        // Mock route with employee's own ID
        $route = new Route(['GET'], '/test/{employee}', []);
        $route->bind($request);
        $route->setParameter('employee', $this->testEmployee->id);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'manage_performance');

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_employee_can_view_own_payroll(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->employee);
        
        // Mock route with employee's own ID
        $route = new Route(['GET'], '/test/{employee}', []);
        $route->bind($request);
        $route->setParameter('employee', $this->testEmployee->id);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'view_payroll');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_adds_employee_to_request(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->employee);
        
        // Mock route with employee's own ID
        $route = new Route(['GET'], '/test/{employee}', []);
        $route->bind($request);
        $route->setParameter('employee', $this->testEmployee->id);
        $request->setRouteResolver(fn() => $route);

        $this->middleware->handle($request, function ($req) {
            $this->assertArrayHasKey('accessed_employee', $req->all());
            $this->assertEquals($this->testEmployee->id, $req->get('accessed_employee')->id);
            return response('OK');
        });
    }

    public function test_invalid_action_is_rejected(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $this->employee);
        
        // Mock route with employee's own ID
        $route = new Route(['GET'], '/test/{employee}', []);
        $route->bind($request);
        $route->setParameter('employee', $this->testEmployee->id);
        $request->setRouteResolver(fn() => $route);

        $response = $this->middleware->handle($request, function () {
            return response('OK');
        }, 'invalid_action');

        $this->assertEquals(403, $response->getStatusCode());
    }
}