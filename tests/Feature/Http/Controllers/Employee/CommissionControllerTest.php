<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Employee;

use Tests\TestCase;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Models\Investment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Role;
use App\Models\Permission;

class CommissionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private User $manager;
    private EmployeeModel $employee;
    private EmployeeModel $managerEmployee;
    private DepartmentModel $department;
    private PositionModel $position;
    private Investment $investment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'Calculate Commissions', 'slug' => 'calculate-commissions', 'guard_name' => 'web']);
        Permission::create(['name' => 'Create Commissions', 'slug' => 'create-commissions', 'guard_name' => 'web']);
        Permission::create(['name' => 'Approve Commissions', 'slug' => 'approve-commissions', 'guard_name' => 'web']);
        Permission::create(['name' => 'Mark Commissions Paid', 'slug' => 'mark-commissions-paid', 'guard_name' => 'web']);
        Permission::create(['name' => 'View Commission Analytics', 'slug' => 'viewCommissionAnalytics', 'guard_name' => 'web']);

        // Create roles
        $fieldAgentRole = Role::create(['name' => 'Field Agent', 'slug' => 'field-agent']);
        $managerRole = Role::create(['name' => 'Manager', 'slug' => 'manager']);

        // Assign permissions to roles using the relationship
        $fieldAgentPermissions = Permission::whereIn('slug', ['calculate-commissions', 'create-commissions'])->get();
        $fieldAgentRole->permissions()->attach($fieldAgentPermissions);
        
        $managerPermissions = Permission::whereIn('slug', [
            'calculate-commissions', 
            'create-commissions', 
            'approve-commissions', 
            'mark-commissions-paid',
            'viewCommissionAnalytics'
        ])->get();
        $managerRole->permissions()->attach($managerPermissions);

        // Create test users
        $this->user = User::factory()->create();
        $this->manager = User::factory()->create();

        $this->user->assignRole($fieldAgentRole);
        $this->manager->assignRole($managerRole);

        // Create department and position
        $this->department = DepartmentModel::factory()->create([
            'name' => 'Sales Department',
            'description' => 'Sales and client management'
        ]);

        $this->position = PositionModel::factory()->create([
            'title' => 'Field Agent',
            'department_id' => $this->department->id,
            'commission_eligible' => true,
            'commission_rate' => 5.0,
        ]);

        $managerPosition = PositionModel::factory()->create([
            'title' => 'Sales Manager',
            'department_id' => $this->department->id,
            'commission_eligible' => false,
            'commission_rate' => 0.0,
        ]);

        // Create employees
        $this->employee = EmployeeModel::factory()->create([
            'user_id' => $this->user->id,
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'active',
            'commission_rate' => 5.0,
        ]);

        $this->managerEmployee = EmployeeModel::factory()->create([
            'user_id' => $this->manager->id,
            'department_id' => $this->department->id,
            'position_id' => $managerPosition->id,
            'employment_status' => 'active',
            'commission_rate' => 0.0,
        ]);

        // Create test investment
        $this->investment = Investment::factory()->create([
            'user_id' => User::factory()->create()->id,
            'amount' => 5000.00,
            'status' => 'active',
            'tier_name' => 'Builder',
        ]);
    }

    public function test_index_displays_commission_dashboard_for_eligible_employee(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('commissions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Employee/Commission/Dashboard')
                ->has('employee')
                ->has('monthlyCommissions')
                ->has('recentCommissions')
                ->has('statistics')
        );
    }

    public function test_index_shows_not_eligible_for_non_employee_user(): void
    {
        $nonEmployeeUser = User::factory()->create();
        $nonEmployeeUser->assignRole($fieldAgentRole);

        $response = $this->actingAs($nonEmployeeUser)
            ->get(route('commissions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Employee/Commission/NotEligible')
                ->has('message')
        );
    }

    public function test_calculate_commission_for_investment_facilitation(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('commissions.calculate'), [
                'employee_id' => $this->employee->id,
                'investment_id' => $this->investment->id,
                'commission_type' => 'investment_facilitation',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'commission' => [
                'amount',
                'base_rate',
                'tier_multiplier',
                'performance_multiplier',
                'calculation_details',
                'calculated_at',
            ]
        ]);
    }

    public function test_calculate_commission_validation_fails_with_invalid_data(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('commissions.calculate'), [
                'employee_id' => 999999, // Non-existent employee
                'investment_id' => $this->investment->id,
                'commission_type' => 'invalid_type',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['employee_id', 'commission_type']);
    }

    public function test_calculate_commission_requires_permission(): void
    {
        $unauthorizedUser = User::factory()->create();

        $response = $this->actingAs($unauthorizedUser)
            ->postJson(route('commissions.calculate'), [
                'employee_id' => $this->employee->id,
                'investment_id' => $this->investment->id,
                'commission_type' => 'investment_facilitation',
            ]);

        $response->assertStatus(403);
    }

    public function test_store_commission_record_successfully(): void
    {
        $commissionData = [
            'employee_id' => $this->employee->id,
            'investment_id' => $this->investment->id,
            'user_id' => $this->investment->user_id,
            'commission_type' => 'investment_facilitation',
            'base_amount' => 5000.00,
            'commission_rate' => 5.0,
            'commission_amount' => 250.00,
            'notes' => 'Commission for Builder tier investment',
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('commissions.store'), $commissionData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'commission',
            'message'
        ]);

        $this->assertDatabaseHas('employee_commissions', [
            'employee_id' => $this->employee->id,
            'investment_id' => $this->investment->id,
            'commission_type' => 'investment_facilitation',
            'commission_amount' => 250.00,
            'status' => 'pending',
        ]);
    }

    public function test_store_commission_validation_fails_with_invalid_amounts(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('commissions.store'), [
                'employee_id' => $this->employee->id,
                'commission_type' => 'investment_facilitation',
                'base_amount' => -100.00, // Negative amount
                'commission_rate' => 150.0, // Rate over 100%
                'commission_amount' => 'invalid', // Non-numeric
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['base_amount', 'commission_rate', 'commission_amount']);
    }

    public function test_approve_commission_successfully(): void
    {
        $commission = EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'status' => 'pending',
            'commission_amount' => 250.00,
        ]);

        $response = $this->actingAs($this->manager)
            ->patchJson(route('commissions.approve', $commission->id), [
                'notes' => 'Approved by manager after review',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'commission',
            'message'
        ]);

        $this->assertDatabaseHas('employee_commissions', [
            'id' => $commission->id,
            'status' => 'approved',
        ]);
    }

    public function test_approve_commission_fails_for_non_pending_status(): void
    {
        $commission = EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'status' => 'paid',
            'commission_amount' => 250.00,
        ]);

        $response = $this->actingAs($this->manager)
            ->patchJson(route('commissions.approve', $commission->id));

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Commission is not in pending status'
        ]);
    }

    public function test_approve_commission_requires_permission(): void
    {
        $commission = EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user) // Field agent without approval permission
            ->patchJson(route('commissions.approve', $commission->id));

        $response->assertStatus(403);
    }

    public function test_mark_commission_as_paid_successfully(): void
    {
        $commission = EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'status' => 'approved',
            'commission_amount' => 250.00,
        ]);

        $paymentDate = now()->subDays(1)->toDateString();

        $response = $this->actingAs($this->manager)
            ->patchJson(route('commissions.mark-paid', $commission->id), [
                'payment_date' => $paymentDate,
                'notes' => 'Payment processed via bank transfer',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'commission',
            'message'
        ]);

        $this->assertDatabaseHas('employee_commissions', [
            'id' => $commission->id,
            'status' => 'paid',
            'payment_date' => $paymentDate,
        ]);
    }

    public function test_mark_commission_paid_fails_for_non_approved_status(): void
    {
        $commission = EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->manager)
            ->patchJson(route('commissions.mark-paid', $commission->id), [
                'payment_date' => now()->toDateString(),
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Commission must be approved before marking as paid'
        ]);
    }

    public function test_mark_commission_paid_validation_fails_with_future_date(): void
    {
        $commission = EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->manager)
            ->patchJson(route('commissions.mark-paid', $commission->id), [
                'payment_date' => now()->addDays(1)->toDateString(), // Future date
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_date']);
    }

    public function test_monthly_report_returns_commission_summary(): void
    {
        // Create some commission records for the current month
        EmployeeCommissionModel::factory()->count(3)->create([
            'employee_id' => $this->employee->id,
            'calculation_date' => now()->toDateString(),
            'commission_amount' => 100.00,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('commissions.reports.monthly', [
                'employee_id' => $this->employee->id,
                'year' => now()->year,
                'month' => now()->month,
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'report' => [
                'employee',
                'period',
                'summary' => [
                    'total_commissions',
                    'investment_count',
                    'referral_commissions',
                    'performance_bonus',
                ],
                'breakdown',
            ]
        ]);
    }

    public function test_quarterly_report_returns_commission_summary(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('commissions.reports.quarterly', [
                'employee_id' => $this->employee->id,
                'year' => now()->year,
                'quarter' => 1,
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'report' => [
                'employee',
                'period',
                'summary',
                'monthly_breakdown',
            ]
        ]);
    }

    public function test_analytics_page_requires_permission(): void
    {
        $response = $this->actingAs($this->user) // Field agent without analytics permission
            ->get(route('commissions.analytics'));

        $response->assertStatus(403);
    }

    public function test_analytics_page_displays_for_authorized_user(): void
    {
        // Create some commission data
        EmployeeCommissionModel::factory()->count(5)->create([
            'employee_id' => $this->employee->id,
            'commission_amount' => 150.00,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->manager)
            ->get(route('commissions.analytics'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Employee/Commission/Analytics')
                ->has('commissions')
                ->has('filters')
                ->has('statistics')
        );
    }

    public function test_analytics_filters_work_correctly(): void
    {
        // Create commissions for different departments
        $otherDepartment = DepartmentModel::factory()->create();
        $otherPosition = PositionModel::factory()->create(['department_id' => $otherDepartment->id]);
        $otherEmployee = EmployeeModel::factory()->create([
            'department_id' => $otherDepartment->id,
            'position_id' => $otherPosition->id,
        ]);

        EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'commission_amount' => 100.00,
            'status' => 'paid',
        ]);

        EmployeeCommissionModel::factory()->create([
            'employee_id' => $otherEmployee->id,
            'commission_amount' => 200.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->manager)
            ->get(route('commissions.analytics', [
                'department_id' => $this->department->id,
                'status' => 'paid',
            ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Employee/Commission/Analytics')
                ->has('commissions.data', 1) // Should only show the paid commission from our department
        );
    }

    public function test_commission_calculation_handles_errors_gracefully(): void
    {
        // Test with invalid investment status
        $invalidInvestment = Investment::factory()->create([
            'status' => 'cancelled',
            'amount' => 1000.00,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('commissions.calculate'), [
                'employee_id' => $this->employee->id,
                'investment_id' => $invalidInvestment->id,
                'commission_type' => 'investment_facilitation',
            ]);

        $response->assertStatus(500);
        $response->assertJsonStructure(['error']);
    }

    public function test_commission_statistics_calculation(): void
    {
        // Create various commission records
        EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'commission_amount' => 100.00,
            'status' => 'paid',
            'calculation_date' => now()->toDateString(),
        ]);

        EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'commission_amount' => 150.00,
            'status' => 'pending',
            'calculation_date' => now()->toDateString(),
        ]);

        EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'commission_amount' => 200.00,
            'status' => 'approved',
            'calculation_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('commissions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('statistics.total_earned_this_month')
                ->has('statistics.pending_commissions')
                ->has('statistics.approved_commissions')
        );
    }
}