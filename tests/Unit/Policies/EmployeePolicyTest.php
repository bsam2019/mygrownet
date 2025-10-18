<?php

namespace Tests\Unit\Policies;

use App\Domain\Employee\Constants\EmployeePermissions;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\EmployeePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeePolicyTest extends TestCase
{
    use RefreshDatabase;

    private EmployeePolicy $policy;
    private User $hrManager;
    private User $departmentHead;
    private User $employee;
    private User $fieldAgent;
    private EmployeeModel $testEmployee;
    private DepartmentModel $department;
    private PositionModel $managerPosition;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new EmployeePolicy();
        
        // Create test data
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
        $agentRole = Role::create(['name' => 'Field Agent', 'guard_name' => 'web', 'slug' => 'field-agent']);

        // Assign permissions to roles
        $hrRole->givePermissionTo([
            EmployeePermissions::VIEW_EMPLOYEES,
            EmployeePermissions::CREATE_EMPLOYEES,
            EmployeePermissions::EDIT_EMPLOYEES,
            EmployeePermissions::DELETE_EMPLOYEES,
            EmployeePermissions::VIEW_ALL_PERFORMANCE,
            EmployeePermissions::CREATE_PERFORMANCE_REVIEWS,
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

        $agentRole->givePermissionTo([
            EmployeePermissions::VIEW_PERFORMANCE,
            EmployeePermissions::VIEW_COMMISSIONS,
            EmployeePermissions::VIEW_CLIENT_ASSIGNMENTS,
        ]);

        // Create users
        $this->hrManager = User::factory()->create();
        $this->departmentHead = User::factory()->create();
        $this->employee = User::factory()->create();
        $this->fieldAgent = User::factory()->create();

        // Assign roles
        $this->hrManager->assignRole($hrRole);
        $this->departmentHead->assignRole($deptHeadRole);
        $this->employee->assignRole($empRole);
        $this->fieldAgent->assignRole($agentRole);

        // Create department and position
        $this->department = DepartmentModel::factory()->create();
        $this->managerPosition = PositionModel::factory()->create([
            'department_id' => $this->department->id,
            'is_department_head' => true,
        ]);

        // Create employee records
        EmployeeModel::factory()->create([
            'user_id' => $this->departmentHead->id,
            'department_id' => $this->department->id,
            'position_id' => $this->managerPosition->id,
        ]);

        $this->testEmployee = EmployeeModel::factory()->create([
            'user_id' => $this->employee->id,
            'department_id' => $this->department->id,
        ]);
    }

    public function test_hr_manager_can_view_any_employees(): void
    {
        $this->assertTrue($this->policy->viewAny($this->hrManager));
    }

    public function test_department_head_cannot_view_any_employees_globally(): void
    {
        $this->assertFalse($this->policy->viewAny($this->departmentHead));
    }

    public function test_employee_cannot_view_any_employees(): void
    {
        $this->assertFalse($this->policy->viewAny($this->employee));
    }

    public function test_hr_manager_can_view_any_employee(): void
    {
        $this->assertTrue($this->policy->view($this->hrManager, $this->testEmployee));
    }

    public function test_employee_can_view_own_record(): void
    {
        $this->assertTrue($this->policy->view($this->employee, $this->testEmployee));
    }

    public function test_department_head_can_view_department_employee(): void
    {
        $this->assertTrue($this->policy->view($this->departmentHead, $this->testEmployee));
    }

    public function test_department_head_cannot_view_other_department_employee(): void
    {
        $otherDepartment = DepartmentModel::factory()->create();
        $otherEmployee = EmployeeModel::factory()->create([
            'department_id' => $otherDepartment->id,
        ]);

        $this->assertFalse($this->policy->view($this->departmentHead, $otherEmployee));
    }

    public function test_hr_manager_can_create_employees(): void
    {
        $this->assertTrue($this->policy->create($this->hrManager));
    }

    public function test_department_head_cannot_create_employees(): void
    {
        $this->assertFalse($this->policy->create($this->departmentHead));
    }

    public function test_hr_manager_can_update_any_employee(): void
    {
        $this->assertTrue($this->policy->update($this->hrManager, $this->testEmployee));
    }

    public function test_employee_can_update_own_record(): void
    {
        $this->assertTrue($this->policy->update($this->employee, $this->testEmployee));
    }

    public function test_department_head_can_update_department_employee(): void
    {
        $this->assertTrue($this->policy->update($this->departmentHead, $this->testEmployee));
    }

    public function test_only_hr_manager_can_delete_employees(): void
    {
        $this->assertTrue($this->policy->delete($this->hrManager, $this->testEmployee));
        $this->assertFalse($this->policy->delete($this->departmentHead, $this->testEmployee));
        $this->assertFalse($this->policy->delete($this->employee, $this->testEmployee));
    }

    public function test_employee_can_view_own_performance(): void
    {
        $this->assertTrue($this->policy->viewPerformance($this->employee, $this->testEmployee));
    }

    public function test_hr_manager_can_view_all_performance(): void
    {
        $this->assertTrue($this->policy->viewPerformance($this->hrManager, $this->testEmployee));
    }

    public function test_department_head_can_view_department_employee_performance(): void
    {
        $this->assertTrue($this->policy->viewPerformance($this->departmentHead, $this->testEmployee));
    }

    public function test_department_head_can_create_performance_review_for_department_employee(): void
    {
        $this->assertTrue($this->policy->createPerformanceReview($this->departmentHead, $this->testEmployee));
    }

    public function test_employee_cannot_create_performance_review(): void
    {
        $this->assertFalse($this->policy->createPerformanceReview($this->employee, $this->testEmployee));
    }

    public function test_employee_can_view_own_commissions(): void
    {
        $this->assertTrue($this->policy->viewCommissions($this->employee, $this->testEmployee));
    }

    public function test_field_agent_can_view_own_client_assignments(): void
    {
        $agentEmployee = EmployeeModel::factory()->create([
            'user_id' => $this->fieldAgent->id,
        ]);

        $this->assertTrue($this->policy->viewClientAssignments($this->fieldAgent, $agentEmployee));
    }

    public function test_employee_can_view_own_payroll(): void
    {
        $this->assertTrue($this->policy->viewPayroll($this->employee, $this->testEmployee));
    }

    public function test_hr_manager_can_view_all_payroll(): void
    {
        $this->assertTrue($this->policy->viewPayroll($this->hrManager, $this->testEmployee));
    }

    public function test_department_head_can_view_department_employees(): void
    {
        $this->assertTrue($this->policy->viewDepartmentEmployees($this->departmentHead, $this->department->id));
    }

    public function test_department_head_cannot_view_other_department_employees(): void
    {
        $otherDepartment = DepartmentModel::factory()->create();
        $this->assertFalse($this->policy->viewDepartmentEmployees($this->departmentHead, $otherDepartment->id));
    }

    public function test_department_head_can_manage_department_employees(): void
    {
        $this->assertTrue($this->policy->manageDepartmentEmployees($this->departmentHead, $this->department->id));
    }

    public function test_regular_employee_cannot_manage_department_employees(): void
    {
        $this->assertFalse($this->policy->manageDepartmentEmployees($this->employee, $this->department->id));
    }
}