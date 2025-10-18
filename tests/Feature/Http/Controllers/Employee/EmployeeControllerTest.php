<?php

namespace Tests\Feature\Http\Controllers\Employee;

use Tests\TestCase;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Inertia\Testing\AssertableInertia as Assert;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $adminUser;
    private User $hrUser;
    private User $regularUser;
    private DepartmentModel $department;
    private PositionModel $position;
    private EmployeeModel $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $hrRole = Role::create(['name' => 'HR Manager', 'guard_name' => 'web']);
        $employeeRole = Role::create(['name' => 'Employee', 'guard_name' => 'web']);

        $permissions = [
            'view_employees',
            'create_employees',
            'edit_employees',
            'delete_employees'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole->givePermissionTo($permissions);
        $hrRole->givePermissionTo(['view_employees', 'create_employees', 'edit_employees']);

        // Create test users
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);

        $this->hrUser = User::factory()->create();
        $this->hrUser->assignRole($hrRole);

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole($employeeRole);

        // Create test data
        $this->department = DepartmentModel::factory()->create([
            'name' => 'Human Resources',
            'is_active' => true
        ]);

        $this->position = PositionModel::factory()->create([
            'title' => 'HR Specialist',
            'department_id' => $this->department->id,
            'base_salary_min' => 5000,
            'base_salary_max' => 8000,
            'is_active' => true
        ]);

        $this->employee = EmployeeModel::factory()->create([
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'active'
        ]);
    }

    /** @test */
    public function it_displays_employee_index_page_for_authorized_users()
    {
        $this->actingAs($this->hrUser)
            ->get(route('employees.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => 
                $page->component('Employee/Index')
                    ->has('employees')
                    ->has('departments')
                    ->has('positions')
                    ->has('stats')
            );
    }

    /** @test */
    public function it_denies_access_to_employee_index_for_unauthorized_users()
    {
        $this->actingAs($this->regularUser)
            ->get(route('employees.index'))
            ->assertForbidden();
    }

    /** @test */
    public function it_filters_employees_by_search_term()
    {
        $employee1 = EmployeeModel::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id
        ]);

        $employee2 = EmployeeModel::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id
        ]);

        $this->actingAs($this->hrUser)
            ->get(route('employees.index', ['search' => 'John']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => 
                $page->component('Employee/Index')
                    ->where('employees.data.0.first_name', 'John')
                    ->where('employees.data.0.last_name', 'Doe')
            );
    }

    /** @test */
    public function it_filters_employees_by_department()
    {
        $salesDept = DepartmentModel::factory()->create(['name' => 'Sales']);
        $salesPosition = PositionModel::factory()->create([
            'department_id' => $salesDept->id,
            'title' => 'Sales Representative'
        ]);

        EmployeeModel::factory()->create([
            'department_id' => $salesDept->id,
            'position_id' => $salesPosition->id
        ]);

        $this->actingAs($this->hrUser)
            ->get(route('employees.index', ['department' => $this->department->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => 
                $page->component('Employee/Index')
                    ->where('employees.data.0.department.name', 'Human Resources')
            );
    }

    /** @test */
    public function it_displays_employee_create_form_for_authorized_users()
    {
        $this->actingAs($this->hrUser)
            ->get(route('employees.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => 
                $page->component('Employee/Create')
                    ->has('departments')
                    ->has('positions')
                    ->has('managers')
            );
    }

    /** @test */
    public function it_creates_employee_with_valid_data()
    {
        $employeeData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+260 123 456 789',
            'address' => '123 Main Street, Lusaka',
            'hire_date' => now()->format('Y-m-d'),
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'base_salary' => 6000,
            'create_system_account' => true
        ];

        $this->actingAs($this->hrUser)
            ->post(route('employees.store'), $employeeData)
            ->assertRedirect();

        $this->assertDatabaseHas('employees', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_employee()
    {
        $this->actingAs($this->hrUser)
            ->post(route('employees.store'), [])
            ->assertSessionHasErrors([
                'first_name',
                'last_name',
                'email',
                'hire_date',
                'department_id',
                'position_id',
                'base_salary'
            ]);
    }

    /** @test */
    public function it_validates_email_uniqueness_when_creating_employee()
    {
        $existingEmployee = EmployeeModel::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $employeeData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'existing@example.com',
            'hire_date' => now()->format('Y-m-d'),
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'base_salary' => 6000
        ];

        $this->actingAs($this->hrUser)
            ->post(route('employees.store'), $employeeData)
            ->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_validates_salary_within_position_range()
    {
        $employeeData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'hire_date' => now()->format('Y-m-d'),
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'base_salary' => 3000 // Below minimum for position
        ];

        $this->actingAs($this->hrUser)
            ->post(route('employees.store'), $employeeData)
            ->assertSessionHasErrors(['base_salary']);
    }

    /** @test */
    public function it_displays_employee_details_for_authorized_users()
    {
        $this->actingAs($this->hrUser)
            ->get(route('employees.show', $this->employee->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => 
                $page->component('Employee/Show')
                    ->has('employee')
                    ->has('performanceMetrics')
                    ->has('commissionSummary')
            );
    }

    /** @test */
    public function it_returns_404_for_non_existent_employee()
    {
        $this->actingAs($this->hrUser)
            ->get(route('employees.show', 99999))
            ->assertNotFound();
    }

    /** @test */
    public function it_displays_employee_edit_form_for_authorized_users()
    {
        $this->actingAs($this->hrUser)
            ->get(route('employees.edit', $this->employee->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => 
                $page->component('Employee/Edit')
                    ->has('employee')
                    ->has('departments')
                    ->has('positions')
                    ->has('managers')
            );
    }

    /** @test */
    public function it_updates_employee_with_valid_data()
    {
        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
            'phone' => '+260 987 654 321',
            'address' => '456 Updated Street',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'base_salary' => 7000,
            'employment_status' => 'active'
        ];

        $this->actingAs($this->hrUser)
            ->put(route('employees.update', $this->employee->id), $updateData)
            ->assertRedirect(route('employees.show', $this->employee->id));

        $this->assertDatabaseHas('employees', [
            'id' => $this->employee->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com'
        ]);
    }

    /** @test */
    public function it_validates_email_uniqueness_when_updating_employee()
    {
        $otherEmployee = EmployeeModel::factory()->create([
            'email' => 'other@example.com'
        ]);

        $updateData = [
            'first_name' => $this->employee->first_name,
            'last_name' => $this->employee->last_name,
            'email' => 'other@example.com', // Trying to use another employee's email
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'base_salary' => 6000,
            'employment_status' => 'active'
        ];

        $this->actingAs($this->hrUser)
            ->put(route('employees.update', $this->employee->id), $updateData)
            ->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_prevents_circular_manager_relationships()
    {
        $manager = EmployeeModel::factory()->create([
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'manager_id' => $this->employee->id // Manager reports to employee
        ]);

        $updateData = [
            'first_name' => $this->employee->first_name,
            'last_name' => $this->employee->last_name,
            'email' => $this->employee->email,
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'manager_id' => $manager->id, // Would create circular relationship
            'base_salary' => 6000,
            'employment_status' => 'active'
        ];

        $this->actingAs($this->hrUser)
            ->put(route('employees.update', $this->employee->id), $updateData)
            ->assertSessionHasErrors(['manager_id']);
    }

    /** @test */
    public function it_terminates_employee_for_authorized_users()
    {
        $this->actingAs($this->adminUser)
            ->delete(route('employees.destroy', $this->employee->id))
            ->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', [
            'id' => $this->employee->id,
            'employment_status' => 'terminated'
        ]);

        $this->assertSoftDeleted('employees', [
            'id' => $this->employee->id
        ]);
    }

    /** @test */
    public function it_denies_employee_termination_for_unauthorized_users()
    {
        $this->actingAs($this->hrUser) // HR can't delete, only admin can
            ->delete(route('employees.destroy', $this->employee->id))
            ->assertForbidden();
    }

    /** @test */
    public function it_deactivates_user_account_when_terminating_employee()
    {
        $user = User::factory()->create();
        $this->employee->update(['user_id' => $user->id]);

        $this->actingAs($this->adminUser)
            ->delete(route('employees.destroy', $this->employee->id))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'inactive'
        ]);
    }

    /** @test */
    public function it_validates_position_belongs_to_department()
    {
        $otherDepartment = DepartmentModel::factory()->create();
        $otherPosition = PositionModel::factory()->create([
            'department_id' => $otherDepartment->id
        ]);

        $employeeData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'hire_date' => now()->format('Y-m-d'),
            'department_id' => $this->department->id,
            'position_id' => $otherPosition->id, // Position from different department
            'base_salary' => 6000
        ];

        $this->actingAs($this->hrUser)
            ->post(route('employees.store'), $employeeData)
            ->assertSessionHasErrors(['position_id']);
    }

    /** @test */
    public function it_prevents_self_assignment_as_manager()
    {
        $updateData = [
            'first_name' => $this->employee->first_name,
            'last_name' => $this->employee->last_name,
            'email' => $this->employee->email,
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'manager_id' => $this->employee->id, // Self as manager
            'base_salary' => 6000,
            'employment_status' => 'active'
        ];

        $this->actingAs($this->hrUser)
            ->put(route('employees.update', $this->employee->id), $updateData)
            ->assertSessionHasErrors(['manager_id']);
    }

    /** @test */
    public function it_formats_names_properly_during_creation()
    {
        $employeeData = [
            'first_name' => 'john  doe', // Multiple spaces, lowercase
            'last_name' => 'SMITH-JONES', // Uppercase with hyphen
            'email' => 'JOHN.DOE@EXAMPLE.COM', // Uppercase email
            'hire_date' => now()->format('Y-m-d'),
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'base_salary' => 6000
        ];

        $this->actingAs($this->hrUser)
            ->post(route('employees.store'), $employeeData);

        $this->assertDatabaseHas('employees', [
            'first_name' => 'John Doe', // Properly formatted
            'last_name' => 'Smith-jones', // Properly formatted
            'email' => 'john.doe@example.com' // Lowercase
        ]);
    }
}