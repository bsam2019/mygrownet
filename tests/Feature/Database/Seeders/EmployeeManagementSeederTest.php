<?php

namespace Tests\Feature\Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeClientAssignmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Models\User;
use Database\Seeders\EmployeeManagementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeManagementSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions first
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmployeePermissionsSeeder']);
    }

    public function test_employee_management_seeder_creates_all_required_data(): void
    {
        // Run the employee management seeder
        $this->artisan('db:seed', ['--class' => EmployeeManagementSeeder::class]);

        // Verify departments were created
        $this->assertGreaterThan(0, DepartmentModel::count());
        $this->assertTrue(DepartmentModel::where('name', 'Human Resources')->exists());
        $this->assertTrue(DepartmentModel::where('name', 'Investment Management')->exists());
        $this->assertTrue(DepartmentModel::where('name', 'Field Operations')->exists());

        // Verify positions were created
        $this->assertGreaterThan(0, PositionModel::count());
        $this->assertTrue(PositionModel::where('title', 'HR Manager')->exists());
        $this->assertTrue(PositionModel::where('title', 'Field Agent')->exists());
        $this->assertTrue(PositionModel::where('title', 'Investment Director')->exists());

        // Verify employees were created
        $this->assertGreaterThan(0, EmployeeModel::count());
        $this->assertTrue(EmployeeModel::where('employee_number', 'EMP001')->exists());

        // Verify performance reviews were created
        $this->assertGreaterThan(0, EmployeePerformanceModel::count());

        // Verify commission records were created
        $this->assertGreaterThan(0, EmployeeCommissionModel::count());

        // Verify client assignments were created
        $this->assertGreaterThan(0, EmployeeClientAssignmentModel::count());
    }

    public function test_department_hierarchy_is_properly_established(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeManagementSeeder::class]);

        // Check that Field Operations is a sub-department of Investment Management
        $investmentDept = DepartmentModel::where('name', 'Investment Management')->first();
        $fieldDept = DepartmentModel::where('name', 'Field Operations')->first();

        $this->assertNotNull($investmentDept);
        $this->assertNotNull($fieldDept);
        $this->assertEquals($investmentDept->id, $fieldDept->parent_department_id);
    }

    public function test_department_heads_are_properly_assigned(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeManagementSeeder::class]);

        $hrDept = DepartmentModel::where('name', 'Human Resources')->first();
        $this->assertNotNull($hrDept->head_employee_id);

        $hrHead = EmployeeModel::find($hrDept->head_employee_id);
        $this->assertNotNull($hrHead);
        $this->assertEquals('HR Manager', $hrHead->position->title);
    }

    public function test_employees_have_proper_relationships(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeManagementSeeder::class]);

        $employee = EmployeeModel::with(['department', 'position', 'user'])->first();

        // Verify employee has required relationships
        $this->assertNotNull($employee->department);
        $this->assertNotNull($employee->position);
        $this->assertNotNull($employee->user);

        // Verify department and position are linked
        $this->assertEquals($employee->department_id, $employee->position->department_id);
    }

    public function test_manager_relationships_are_established(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeManagementSeeder::class]);

        // Find employees with managers
        $employeesWithManagers = EmployeeModel::whereNotNull('manager_id')->get();
        $this->assertGreaterThan(0, $employeesWithManagers->count());

        foreach ($employeesWithManagers as $employee) {
            $manager = EmployeeModel::find($employee->manager_id);
            $this->assertNotNull($manager);
            
            // Manager should be in same department or parent department
            $this->assertTrue(
                $manager->department_id === $employee->department_id ||
                $manager->department_id === $employee->department->parent_department_id
            );
        }
    }

    public function test_performance_reviews_have_valid_data(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeManagementSeeder::class]);

        $performanceReviews = EmployeePerformanceModel::all();
        $this->assertGreaterThan(0, $performanceReviews->count());

        foreach ($performanceReviews as $review) {
            // Verify employee exists
            $this->assertNotNull(EmployeeModel::find($review->employee_id));
            
            // Verify reviewer exists
            $this->assertNotNull(EmployeeModel::find($review->reviewer_id));
            
            // Verify scores are within valid range
            $this->assertGreaterThanOrEqual(1, $review->overall_score);
            $this->assertLessThanOrEqual(5, $review->overall_score);
            
            // Verify review period is valid
            $this->assertNotNull($review->review_period_start);
            $this->assertNotNull($review->review_period_end);
            $this->assertTrue($review->review_period_start < $review->review_period_end);
        }
    }

    public function test_commission_records_have_valid_data(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeManagementSeeder::class]);

        $commissions = EmployeeCommissionModel::all();
        $this->assertGreaterThan(0, $commissions->count());

        foreach ($commissions as $commission) {
            // Verify employee exists
            $this->assertNotNull(EmployeeModel::find($commission->employee_id));
            
            // Verify user exists (client)
            $this->assertNotNull(User::find($commission->user_id));
            
            // Verify amounts are positive
            $this->assertGreaterThan(0, $commission->commission_amount);
            $this->assertGreaterThan(0, $commission->investment_amount);
            
            // Verify commission rate is reasonable
            $this->assertGreaterThan(0, $commission->commission_rate);
            $this->assertLessThanOrEqual(100, $commission->commission_rate);
            
            // Verify status is valid
            $this->assertContains($commission->status, ['pending', 'paid', 'cancelled']);
        }
    }

    public function test_client_assignments_are_valid(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeManagementSeeder::class]);

        $assignments = EmployeeClientAssignmentModel::all();
        $this->assertGreaterThan(0, $assignments->count());

        foreach ($assignments as $assignment) {
            // Verify employee exists
            $employee = EmployeeModel::find($assignment->employee_id);
            $this->assertNotNull($employee);
            
            // Verify user exists (client)
            $this->assertNotNull(User::find($assignment->user_id));
            
            // Verify assignment type is valid
            $this->assertContains($assignment->assignment_type, ['primary', 'secondary', 'support']);
            
            // Verify dates are logical
            $this->assertNotNull($assignment->assigned_at);
            if ($assignment->unassigned_at) {
                $this->assertTrue($assignment->assigned_at < $assignment->unassigned_at);
            }
        }
    }

    public function test_salary_ranges_are_appropriate_for_positions(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeManagementSeeder::class]);

        $employees = EmployeeModel::with('position')->get();

        foreach ($employees as $employee) {
            $salary = $employee->salary;
            $position = $employee->position->title;

            // Basic salary validation
            $this->assertGreaterThan(0, $salary);
            
            // Position-specific salary ranges (in Kwacha)
            switch ($position) {
                case 'Investment Director':
                    $this->assertGreaterThanOrEqual(150000, $salary); // At least K150,000
                    break;
                case 'HR Manager':
                    $this->assertGreaterThanOrEqual(100000, $salary); // At least K100,000
                    break;
                case 'Field Agent':
                    $this->assertGreaterThanOrEqual(50000, $salary); // At least K50,000
                    break;
                case 'Investment Analyst':
                    $this->assertGreaterThanOrEqual(80000, $salary); // At least K80,000
                    break;
            }
        }
    }

    public function test_employee_numbers_are_unique(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeManagementSeeder::class]);

        $employeeNumbers = EmployeeModel::pluck('employee_number')->toArray();
        $uniqueNumbers = array_unique($employeeNumbers);

        $this->assertEquals(count($employeeNumbers), count($uniqueNumbers));
    }

    public function test_department_budget_allocations_are_reasonable(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeManagementSeeder::class]);

        $departments = DepartmentModel::all();

        foreach ($departments as $department) {
            if ($department->budget) {
                $this->assertGreaterThan(0, $department->budget);
                
                // Budget should be reasonable for department size
                $employeeCount = EmployeeModel::where('department_id', $department->id)->count();
                if ($employeeCount > 0) {
                    $avgSalaryBudget = $department->budget / $employeeCount;
                    $this->assertGreaterThan(30000, $avgSalaryBudget); // At least K30,000 per employee
                }
            }
        }
    }

    public function test_seeder_is_idempotent(): void
    {
        // Run seeder twice
        $this->artisan('db:seed', ['--class' => EmployeeManagementSeeder::class]);
        $firstRunCounts = [
            'departments' => DepartmentModel::count(),
            'positions' => PositionModel::count(),
            'employees' => EmployeeModel::count(),
        ];

        $this->artisan('db:seed', ['--class' => EmployeeManagementSeeder::class]);
        $secondRunCounts = [
            'departments' => DepartmentModel::count(),
            'positions' => PositionModel::count(),
            'employees' => EmployeeModel::count(),
        ];

        // Counts should remain the same (seeder should not duplicate data)
        $this->assertEquals($firstRunCounts, $secondRunCounts);
    }
}