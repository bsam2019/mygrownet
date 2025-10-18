<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeClientAssignmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_has_fillable_attributes(): void
    {
        $fillable = [
            'employee_id',
            'user_id',
            'first_name',
            'last_name',
            'email',
            'phone',
            'address',
            'date_of_birth',
            'gender',
            'national_id',
            'department_id',
            'position_id',
            'manager_id',
            'employment_status',
            'hire_date',
            'termination_date',
            'current_salary',
            'emergency_contacts',
            'qualifications',
            'notes',
        ];

        $employee = new EmployeeModel();
        
        $this->assertEquals($fillable, $employee->getFillable());
    }

    public function test_employee_casts_attributes_correctly(): void
    {
        $employee = EmployeeModel::factory()->create([
            'date_of_birth' => '1990-05-15',
            'hire_date' => '2023-01-15',
            'termination_date' => '2024-01-15',
            'current_salary' => '50000.00',
            'emergency_contacts' => [['name' => 'John Doe', 'phone' => '123-456-7890']],
            'qualifications' => ['Bachelor Degree', 'Certification'],
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $employee->date_of_birth);
        $this->assertInstanceOf(\Carbon\Carbon::class, $employee->hire_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $employee->termination_date);
        $this->assertIsFloat($employee->current_salary);
        $this->assertIsArray($employee->emergency_contacts);
        $this->assertIsArray($employee->qualifications);
    }

    public function test_employee_belongs_to_department(): void
    {
        $department = DepartmentModel::factory()->create();
        $employee = EmployeeModel::factory()->create([
            'department_id' => $department->id,
        ]);

        $this->assertInstanceOf(DepartmentModel::class, $employee->department);
        $this->assertEquals($department->id, $employee->department->id);
    }

    public function test_employee_belongs_to_position(): void
    {
        $position = PositionModel::factory()->create();
        $employee = EmployeeModel::factory()->create([
            'position_id' => $position->id,
        ]);

        $this->assertInstanceOf(PositionModel::class, $employee->position);
        $this->assertEquals($position->id, $employee->position->id);
    }

    public function test_employee_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $employee = EmployeeModel::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $employee->user);
        $this->assertEquals($user->id, $employee->user->id);
    }

    public function test_employee_belongs_to_manager(): void
    {
        $manager = EmployeeModel::factory()->create();
        $employee = EmployeeModel::factory()->create([
            'manager_id' => $manager->id,
        ]);

        $this->assertInstanceOf(EmployeeModel::class, $employee->manager);
        $this->assertEquals($manager->id, $employee->manager->id);
    }

    public function test_employee_has_many_direct_reports(): void
    {
        $manager = EmployeeModel::factory()->create();
        $directReports = EmployeeModel::factory()->count(3)->create([
            'manager_id' => $manager->id,
        ]);

        $this->assertCount(3, $manager->directReports);
        
        foreach ($directReports as $report) {
            $this->assertTrue($manager->directReports->contains($report));
        }
    }

    public function test_employee_has_many_performance_reviews(): void
    {
        $employee = EmployeeModel::factory()->create();
        $reviews = EmployeePerformanceModel::factory()->count(4)->create([
            'employee_id' => $employee->id,
        ]);

        $this->assertCount(4, $employee->performanceReviews);
        
        foreach ($reviews as $review) {
            $this->assertTrue($employee->performanceReviews->contains($review));
        }
    }

    public function test_employee_has_many_commissions(): void
    {
        $employee = EmployeeModel::factory()->create();
        $commissions = EmployeeCommissionModel::factory()->count(5)->create([
            'employee_id' => $employee->id,
        ]);

        $this->assertCount(5, $employee->commissions);
        
        foreach ($commissions as $commission) {
            $this->assertTrue($employee->commissions->contains($commission));
        }
    }

    public function test_employee_has_many_client_assignments(): void
    {
        $employee = EmployeeModel::factory()->create();
        $assignments = EmployeeClientAssignmentModel::factory()->count(3)->create([
            'employee_id' => $employee->id,
        ]);

        $this->assertCount(3, $employee->clientAssignments);
        
        foreach ($assignments as $assignment) {
            $this->assertTrue($employee->clientAssignments->contains($assignment));
        }
    }

    public function test_employee_has_many_reviews_given(): void
    {
        $reviewer = EmployeeModel::factory()->create();
        $reviewsGiven = EmployeePerformanceModel::factory()->count(2)->create([
            'reviewer_id' => $reviewer->id,
        ]);

        $this->assertCount(2, $reviewer->reviewsGiven);
        
        foreach ($reviewsGiven as $review) {
            $this->assertTrue($reviewer->reviewsGiven->contains($review));
        }
    }

    public function test_active_scope_filters_active_employees(): void
    {
        EmployeeModel::factory()->count(3)->create(['employment_status' => 'active']);
        EmployeeModel::factory()->count(2)->create(['employment_status' => 'terminated']);

        $activeEmployees = EmployeeModel::active()->get();

        $this->assertCount(3, $activeEmployees);
        
        foreach ($activeEmployees as $employee) {
            $this->assertEquals('active', $employee->employment_status);
        }
    }

    public function test_employment_status_scopes_work_correctly(): void
    {
        EmployeeModel::factory()->create(['employment_status' => 'active']);
        EmployeeModel::factory()->create(['employment_status' => 'inactive']);
        EmployeeModel::factory()->create(['employment_status' => 'terminated']);
        EmployeeModel::factory()->create(['employment_status' => 'suspended']);

        $this->assertCount(1, EmployeeModel::active()->get());
        $this->assertCount(1, EmployeeModel::inactive()->get());
        $this->assertCount(1, EmployeeModel::terminated()->get());
        $this->assertCount(1, EmployeeModel::suspended()->get());
    }

    public function test_in_department_scope_filters_by_department(): void
    {
        $department1 = DepartmentModel::factory()->create();
        $department2 = DepartmentModel::factory()->create();
        
        EmployeeModel::factory()->count(3)->create(['department_id' => $department1->id]);
        EmployeeModel::factory()->count(2)->create(['department_id' => $department2->id]);

        $employeesInDepartment1 = EmployeeModel::inDepartment($department1->id)->get();

        $this->assertCount(3, $employeesInDepartment1);
        
        foreach ($employeesInDepartment1 as $employee) {
            $this->assertEquals($department1->id, $employee->department_id);
        }
    }

    public function test_commission_eligible_scope_filters_commission_eligible_employees(): void
    {
        $commissionPosition = PositionModel::factory()->commissionEligible()->create();
        $nonCommissionPosition = PositionModel::factory()->noCommission()->create();
        
        EmployeeModel::factory()->count(2)->create(['position_id' => $commissionPosition->id]);
        EmployeeModel::factory()->count(3)->create(['position_id' => $nonCommissionPosition->id]);

        $commissionEligibleEmployees = EmployeeModel::commissionEligible()->get();

        $this->assertCount(2, $commissionEligibleEmployees);
    }

    public function test_search_scope_searches_multiple_fields(): void
    {
        EmployeeModel::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'employee_id' => 'EMP001',
        ]);
        EmployeeModel::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'employee_id' => 'EMP002',
        ]);

        $searchByFirstName = EmployeeModel::search('John')->get();
        $searchByLastName = EmployeeModel::search('Smith')->get();
        $searchByEmail = EmployeeModel::search('john.doe')->get();
        $searchByEmployeeId = EmployeeModel::search('EMP001')->get();

        $this->assertCount(1, $searchByFirstName);
        $this->assertCount(1, $searchByLastName);
        $this->assertCount(1, $searchByEmail);
        $this->assertCount(1, $searchByEmployeeId);
    }

    public function test_full_name_accessor(): void
    {
        $employee = EmployeeModel::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $employee->full_name);
    }

    public function test_is_active_accessor(): void
    {
        $activeEmployee = EmployeeModel::factory()->create(['employment_status' => 'active']);
        $inactiveEmployee = EmployeeModel::factory()->create(['employment_status' => 'terminated']);

        $this->assertTrue($activeEmployee->is_active);
        $this->assertFalse($inactiveEmployee->is_active);
    }

    public function test_years_of_service_accessor(): void
    {
        $employee = EmployeeModel::factory()->create([
            'hire_date' => now()->subYears(3),
            'termination_date' => null,
        ]);

        $this->assertEquals(3, $employee->years_of_service);
    }

    public function test_years_of_service_accessor_with_termination_date(): void
    {
        $hireDate = now()->subYears(5);
        $terminationDate = now()->subYears(2);
        
        $employee = EmployeeModel::factory()->create([
            'hire_date' => $hireDate,
            'termination_date' => $terminationDate,
        ]);

        $this->assertEquals(3, $employee->years_of_service);
    }

    public function test_employee_factory_states_work_correctly(): void
    {
        $activeEmployee = EmployeeModel::factory()->active()->create();
        $terminatedEmployee = EmployeeModel::factory()->terminated()->create();
        $fieldAgent = EmployeeModel::factory()->fieldAgent()->create();
        $manager = EmployeeModel::factory()->manager()->create();

        $this->assertEquals('active', $activeEmployee->employment_status);
        $this->assertEquals('terminated', $terminatedEmployee->employment_status);
        $this->assertNotNull($terminatedEmployee->termination_date);
        $this->assertEquals('active', $fieldAgent->employment_status);
        $this->assertEquals('active', $manager->employment_status);
    }

    public function test_employee_soft_deletes(): void
    {
        $employee = EmployeeModel::factory()->create();
        $employeeId = $employee->id;

        $employee->delete();

        $this->assertSoftDeleted('employees', ['id' => $employeeId]);
        $this->assertNotNull($employee->fresh()->deleted_at);
    }
}