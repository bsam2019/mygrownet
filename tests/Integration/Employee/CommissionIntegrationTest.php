<?php

declare(strict_types=1);

namespace Tests\Integration\Employee;

use Tests\TestCase;
use App\Models\User;
use App\Models\Investment;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use App\Domain\Employee\Services\CommissionCalculationService;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObjects\EmployeeId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;
use App\Models\Permission;

class CommissionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $fieldAgent;
    private EmployeeModel $employee;
    private Investment $investment;
    private CommissionCalculationService $commissionService;
    private EmployeeRepositoryInterface $employeeRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions and roles
        Permission::create(['name' => 'Calculate Commissions', 'slug' => 'calculate-commissions', 'guard_name' => 'web']);
        $fieldAgentRole = Role::create(['name' => 'Field Agent', 'slug' => 'field-agent']);
        $fieldAgentRole->permissions()->attach(Permission::where('slug', 'calculate-commissions')->first());

        // Create test user
        $this->fieldAgent = User::factory()->create();
        $this->fieldAgent->assignRole($fieldAgentRole);

        // Create department and position
        $department = DepartmentModel::factory()->create();
        $position = PositionModel::factory()->create([
            'department_id' => $department->id,
            'commission_eligible' => true,
            'commission_rate' => 5.0,
        ]);

        // Create employee
        $this->employee = EmployeeModel::factory()->create([
            'user_id' => $this->fieldAgent->id,
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employment_status' => 'active',
            'commission_rate' => 5.0,
        ]);

        // Create investment
        $this->investment = Investment::factory()->create([
            'user_id' => User::factory()->create()->id,
            'amount' => 10000.00,
            'status' => 'active',
            'tier_name' => 'Leader',
        ]);

        // Get services from container
        $this->commissionService = app(CommissionCalculationService::class);
        $this->employeeRepository = app(EmployeeRepositoryInterface::class);
    }

    public function test_end_to_end_commission_calculation_and_storage(): void
    {
        // Step 1: Calculate commission via API
        $response = $this->actingAs($this->fieldAgent)
            ->postJson(route('commissions.calculate'), [
                'employee_id' => $this->employee->id,
                'investment_id' => $this->investment->id,
                'commission_type' => 'investment_facilitation',
            ]);

        $response->assertStatus(200);
        $calculationData = $response->json('commission');

        $this->assertGreaterThan(0, $calculationData['amount']);
        $this->assertEquals(5.0, $calculationData['base_rate']);
        $this->assertGreaterThan(1.0, $calculationData['tier_multiplier']); // Leader tier should have multiplier

        // Step 2: Store the calculated commission
        $storeResponse = $this->actingAs($this->fieldAgent)
            ->postJson(route('commissions.store'), [
                'employee_id' => $this->employee->id,
                'investment_id' => $this->investment->id,
                'user_id' => $this->investment->user_id,
                'commission_type' => 'investment_facilitation',
                'base_amount' => $this->investment->amount,
                'commission_rate' => $calculationData['base_rate'],
                'commission_amount' => $calculationData['amount'],
                'notes' => 'Commission for Leader tier investment facilitation',
            ]);

        $storeResponse->assertStatus(201);

        // Verify commission record was created
        $this->assertDatabaseHas('employee_commissions', [
            'employee_id' => $this->employee->id,
            'investment_id' => $this->investment->id,
            'commission_type' => 'investment_facilitation',
            'status' => 'pending',
        ]);

        $commission = EmployeeCommissionModel::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($commission);
        $this->assertEquals($calculationData['amount'], $commission->commission_amount);
    }

    public function test_commission_approval_workflow(): void
    {
        // Create a manager user
        Permission::create(['name' => 'Approve Commissions', 'slug' => 'approve-commissions', 'guard_name' => 'web']);
        $managerRole = Role::create(['name' => 'Manager', 'slug' => 'manager']);
        $managerRole->permissions()->attach(Permission::where('slug', 'approve-commissions')->first());
        
        $manager = User::factory()->create();
        $manager->assignRole($managerRole);

        // Create a pending commission
        $commission = EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'investment_id' => $this->investment->id,
            'commission_amount' => 500.00,
            'status' => 'pending',
        ]);

        // Step 1: Approve the commission
        $approveResponse = $this->actingAs($manager)
            ->patchJson(route('commissions.approve', $commission->id), [
                'notes' => 'Approved after verification of investment details',
            ]);

        $approveResponse->assertStatus(200);

        // Verify status changed to approved
        $commission->refresh();
        $this->assertEquals('approved', $commission->status);

        // Step 2: Mark as paid
        Permission::create(['name' => 'Mark Commissions Paid', 'slug' => 'mark-commissions-paid', 'guard_name' => 'web']);
        $managerRole->permissions()->attach(Permission::where('slug', 'mark-commissions-paid')->first());

        $paymentDate = now()->subDays(1)->toDateString();
        $paidResponse = $this->actingAs($manager)
            ->patchJson(route('commissions.mark-paid', $commission->id), [
                'payment_date' => $paymentDate,
                'notes' => 'Payment processed via bank transfer',
            ]);

        $paidResponse->assertStatus(200);

        // Verify final status
        $commission->refresh();
        $this->assertEquals('paid', $commission->status);
        $this->assertEquals($paymentDate, $commission->payment_date);
    }

    public function test_monthly_commission_report_integration(): void
    {
        // Create multiple commission records for current month
        $commissions = EmployeeCommissionModel::factory()->count(3)->create([
            'employee_id' => $this->employee->id,
            'calculation_date' => now()->toDateString(),
            'commission_amount' => 200.00,
            'status' => 'paid',
            'commission_type' => 'investment_facilitation',
        ]);

        // Create one referral commission
        EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'calculation_date' => now()->toDateString(),
            'commission_amount' => 150.00,
            'status' => 'paid',
            'commission_type' => 'referral',
        ]);

        // Get monthly report
        $response = $this->actingAs($this->fieldAgent)
            ->getJson(route('commissions.reports.monthly', [
                'employee_id' => $this->employee->id,
                'year' => now()->year,
                'month' => now()->month,
            ]));

        $response->assertStatus(200);
        
        $report = $response->json('report');
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('breakdown', $report);
        
        // Verify employee information
        $this->assertEquals($this->employee->id, $report['employee']['id']);
        $this->assertStringContainsString($this->employee->first_name, $report['employee']['name']);
    }

    public function test_commission_dashboard_integration(): void
    {
        // Create various commission records
        EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'commission_amount' => 300.00,
            'status' => 'paid',
            'calculation_date' => now()->toDateString(),
        ]);

        EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'commission_amount' => 250.00,
            'status' => 'pending',
            'calculation_date' => now()->toDateString(),
        ]);

        EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'commission_amount' => 400.00,
            'status' => 'approved',
            'calculation_date' => now()->toDateString(),
        ]);

        // Access dashboard
        $response = $this->actingAs($this->fieldAgent)
            ->get(route('commissions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Employee/Commission/Dashboard')
                ->has('employee')
                ->has('monthlyCommissions')
                ->has('recentCommissions')
                ->has('statistics')
                ->where('statistics.pending_commissions', 250.00)
                ->where('statistics.approved_commissions', 400.00)
        );
    }

    public function test_commission_calculation_with_different_tiers(): void
    {
        $tierTests = [
            ['tier' => 'Basic', 'amount' => 500.00, 'expected_multiplier' => 1.0],
            ['tier' => 'Starter', 'amount' => 1500.00, 'expected_multiplier' => 1.1],
            ['tier' => 'Builder', 'amount' => 3500.00, 'expected_multiplier' => 1.2],
            ['tier' => 'Leader', 'amount' => 7500.00, 'expected_multiplier' => 1.3],
            ['tier' => 'Elite', 'amount' => 10000.00, 'expected_multiplier' => 1.5],
        ];

        foreach ($tierTests as $test) {
            $investment = Investment::factory()->create([
                'user_id' => User::factory()->create()->id,
                'amount' => $test['amount'],
                'status' => 'active',
                'tier_name' => $test['tier'],
            ]);

            $response = $this->actingAs($this->fieldAgent)
                ->postJson(route('commissions.calculate'), [
                    'employee_id' => $this->employee->id,
                    'investment_id' => $investment->id,
                    'commission_type' => 'investment_facilitation',
                ]);

            $response->assertStatus(200);
            
            $commission = $response->json('commission');
            $this->assertEquals($test['expected_multiplier'], $commission['tier_multiplier'], 
                "Tier multiplier mismatch for {$test['tier']} tier");
            
            // Verify base commission calculation
            $expectedBaseCommission = $test['amount'] * 0.05; // 5% base rate
            $expectedFinalCommission = $expectedBaseCommission * $test['expected_multiplier'];
            
            $this->assertEqualsWithDelta($expectedFinalCommission, $commission['amount'], 0.01,
                "Commission amount mismatch for {$test['tier']} tier");
        }
    }

    public function test_commission_service_integration_with_repository(): void
    {
        // Test direct service integration
        $employee = $this->employeeRepository->findById(new EmployeeId($this->employee->id));
        $this->assertNotNull($employee);

        // Test commission calculation through service
        $result = $this->commissionService->calculateFieldAgentCommission($employee, $this->investment);
        
        $this->assertGreaterThan(0, $result->getCommissionAmount());
        $this->assertEquals(5.0, $result->getBaseCommissionRate());
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->getCalculatedAt());
        
        // Verify calculation details
        $details = $result->getCalculationDetails();
        $this->assertArrayHasKey('base_commission', $details);
        $this->assertArrayHasKey('tier_multiplier', $details);
        $this->assertArrayHasKey('performance_multiplier', $details);
        $this->assertArrayHasKey('final_commission', $details);
    }
}