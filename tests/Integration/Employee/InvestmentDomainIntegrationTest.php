<?php

namespace Tests\Integration\Employee;

use Tests\TestCase;
use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeClientAssignmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use App\Domain\Employee\Services\CommissionCalculationService;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\EmploymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

class InvestmentDomainIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected CommissionCalculationService $commissionService;
    protected EmployeeModel $fieldAgent;
    protected User $investor;
    protected Investment $investment;
    protected InvestmentTier $tier;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->commissionService = app(CommissionCalculationService::class);
        
        // Create test data
        $this->setupTestData();
    }

    protected function setupTestData(): void
    {
        // Find or create investment tier
        $this->tier = InvestmentTier::firstOrCreate(
            ['name' => 'Builder'],
            [
                'minimum_investment' => 2500,
                'fixed_profit_rate' => 7.0,
                'direct_referral_rate' => 5.0,
                'level2_referral_rate' => 3.0,
                'level3_referral_rate' => 1.0,
                'benefits' => [],
                'is_active' => true,
                'description' => 'Builder tier for testing',
                'order' => 3,
            ]
        );

        // Create investor user
        $this->investor = User::factory()->create([
            'email' => 'investor@test.com',
            'current_investment_tier_id' => $this->tier->id,
        ]);

        // Create investment
        $this->investment = Investment::factory()->create([
            'user_id' => $this->investor->id,
            'tier' => $this->tier->name,
            'amount' => 5000,
            'status' => 'active',
            'investment_date' => now()->subDays(30),
            'lock_in_period_end' => now()->addMonths(12),
        ]);

        // Create department and position for field agent
        $department = DepartmentModel::factory()->create([
            'name' => 'Field Operations',
        ]);

        $position = PositionModel::factory()->create([
            'title' => 'Field Agent',
            'department_id' => $department->id,
            'base_commission_rate' => 2.5,
        ]);

        // Create field agent employee
        $this->fieldAgent = EmployeeModel::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Agent',
            'email' => 'agent@test.com',
            'employment_status' => EmploymentStatus::ACTIVE,
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);
    }

    /** @test */
    public function field_agent_can_be_assigned_to_investor_portfolio()
    {
        // Act: Assign field agent to investor
        $assignment = EmployeeClientAssignmentModel::create([
            'employee_id' => $this->fieldAgent->id,
            'user_id' => $this->investor->id,
            'assignment_type' => 'primary',
            'assigned_date' => now(),
            'is_active' => true,
        ]);

        // Assert: Assignment was created successfully
        $this->assertDatabaseHas('employee_client_assignments', [
            'employee_id' => $this->fieldAgent->id,
            'user_id' => $this->investor->id,
            'assignment_type' => 'primary',
            'is_active' => true,
        ]);

        // Assert: Field agent can access investor's portfolio
        $this->assertTrue($assignment->is_active);
        $this->assertEquals('primary', $assignment->assignment_type);
        $this->assertEquals($this->fieldAgent->id, $assignment->employee_id);
        $this->assertEquals($this->investor->id, $assignment->user_id);
    }

    /** @test */
    public function field_agent_can_have_multiple_client_assignments()
    {
        // Create additional investors
        $investor2 = User::factory()->create(['email' => 'investor2@test.com']);
        $investor3 = User::factory()->create(['email' => 'investor3@test.com']);

        // Act: Assign field agent to multiple investors
        EmployeeClientAssignmentModel::create([
            'employee_id' => $this->fieldAgent->id,
            'user_id' => $this->investor->id,
            'assignment_type' => 'primary',
            'assigned_date' => now(),
            'is_active' => true,
        ]);

        EmployeeClientAssignmentModel::create([
            'employee_id' => $this->fieldAgent->id,
            'user_id' => $investor2->id,
            'assignment_type' => 'primary',
            'assigned_date' => now(),
            'is_active' => true,
        ]);

        EmployeeClientAssignmentModel::create([
            'employee_id' => $this->fieldAgent->id,
            'user_id' => $investor3->id,
            'assignment_type' => 'secondary',
            'assigned_date' => now(),
            'is_active' => true,
        ]);

        // Assert: All assignments exist
        $assignments = EmployeeClientAssignmentModel::where('employee_id', $this->fieldAgent->id)
            ->where('is_active', true)
            ->get();

        $this->assertCount(3, $assignments);
        
        // Assert: Primary assignments
        $primaryAssignments = $assignments->where('assignment_type', 'primary');
        $this->assertCount(2, $primaryAssignments);
        
        // Assert: Secondary assignments
        $secondaryAssignments = $assignments->where('assignment_type', 'secondary');
        $this->assertCount(1, $secondaryAssignments);
    }

    /** @test */
    public function commission_is_calculated_based_on_investment_facilitation()
    {
        // Arrange: Assign field agent to investor
        EmployeeClientAssignmentModel::create([
            'employee_id' => $this->fieldAgent->id,
            'user_id' => $this->investor->id,
            'assignment_type' => 'primary',
            'assigned_date' => now(),
            'is_active' => true,
        ]);

        // Act: Calculate commission for investment facilitation
        $commissionAmount = $this->commissionService->calculateInvestmentFacilitationCommission(
            $this->fieldAgent,
            $this->investment
        );

        // Assert: Commission is calculated correctly
        $expectedCommission = $this->investment->amount * ($this->fieldAgent->commission_rate / 100);
        $this->assertEquals($expectedCommission, $commissionAmount);
        $this->assertEquals(125.0, $commissionAmount); // 5000 * 2.5% = 125
    }

    /** @test */
    public function commission_record_is_created_for_investment_facilitation()
    {
        // Arrange: Assign field agent to investor
        EmployeeClientAssignmentModel::create([
            'employee_id' => $this->fieldAgent->id,
            'user_id' => $this->investor->id,
            'assignment_type' => 'primary',
            'assigned_date' => now(),
            'is_active' => true,
        ]);

        // Act: Record commission for investment facilitation
        $commission = EmployeeCommissionModel::create([
            'employee_id' => $this->fieldAgent->id,
            'investment_id' => $this->investment->id,
            'user_id' => $this->investor->id,
            'commission_type' => 'investment_facilitation',
            'base_amount' => $this->investment->amount,
            'commission_rate' => $this->fieldAgent->commission_rate,
            'commission_amount' => $this->investment->amount * ($this->fieldAgent->commission_rate / 100),
            'calculation_date' => now(),
            'status' => 'pending',
        ]);

        // Assert: Commission record exists
        $this->assertDatabaseHas('employee_commissions', [
            'employee_id' => $this->fieldAgent->id,
            'investment_id' => $this->investment->id,
            'commission_type' => 'investment_facilitation',
            'commission_amount' => 125.0,
            'status' => 'pending',
        ]);

        // Assert: Commission relationships work
        $this->assertEquals($this->fieldAgent->id, $commission->employee_id);
        $this->assertEquals($this->investment->id, $commission->investment_id);
        $this->assertEquals($this->investor->id, $commission->user_id);
    }

    /** @test */
    public function field_agent_commission_varies_by_investment_tier()
    {
        // Find or create different tier investments
        $basicTier = InvestmentTier::firstOrCreate(
            ['name' => 'Basic'],
            [
                'minimum_investment' => 500,
                'fixed_profit_rate' => 3.0,
                'direct_referral_rate' => 3.0,
                'level2_referral_rate' => 2.0,
                'level3_referral_rate' => 1.0,
                'benefits' => [],
                'is_active' => true,
                'description' => 'Basic tier for testing',
                'order' => 1,
            ]
        );

        $eliteTier = InvestmentTier::firstOrCreate(
            ['name' => 'Elite'],
            [
                'minimum_investment' => 10000,
                'fixed_profit_rate' => 15.0,
                'direct_referral_rate' => 10.0,
                'level2_referral_rate' => 7.0,
                'level3_referral_rate' => 3.0,
                'benefits' => [],
                'is_active' => true,
                'description' => 'Elite tier for testing',
                'order' => 5,
            ]
        );

        $basicInvestment = Investment::factory()->create([
            'user_id' => $this->investor->id,
            'tier' => $basicTier->name,
            'amount' => 1000,
            'status' => 'active',
            'investment_date' => now()->subDays(60),
            'lock_in_period_end' => now()->addMonths(12),
        ]);

        $eliteInvestment = Investment::factory()->create([
            'user_id' => $this->investor->id,
            'tier' => $eliteTier->name,
            'amount' => 15000,
            'status' => 'active',
            'investment_date' => now()->subDays(90),
            'lock_in_period_end' => now()->addMonths(12),
        ]);

        // Act: Calculate commissions for different tiers
        $basicCommission = $this->commissionService->calculateInvestmentFacilitationCommission(
            $this->fieldAgent,
            $basicInvestment
        );

        $eliteCommission = $this->commissionService->calculateInvestmentFacilitationCommission(
            $this->fieldAgent,
            $eliteInvestment
        );

        // Assert: Commissions scale with investment amounts
        $this->assertEquals(25.0, $basicCommission); // 1000 * 2.5%
        $this->assertEquals(375.0, $eliteCommission); // 15000 * 2.5%
        $this->assertTrue($eliteCommission > $basicCommission);
    }

    /** @test */
    public function data_consistency_is_maintained_between_employee_and_investment_contexts()
    {
        // Arrange: Create assignment and commission
        $assignment = EmployeeClientAssignmentModel::create([
            'employee_id' => $this->fieldAgent->id,
            'user_id' => $this->investor->id,
            'assignment_type' => 'primary',
            'assigned_date' => now(),
            'is_active' => true,
        ]);

        $commission = EmployeeCommissionModel::create([
            'employee_id' => $this->fieldAgent->id,
            'investment_id' => $this->investment->id,
            'user_id' => $this->investor->id,
            'commission_type' => 'investment_facilitation',
            'base_amount' => $this->investment->amount,
            'commission_rate' => $this->fieldAgent->commission_rate,
            'commission_amount' => 125.0,
            'calculation_date' => now(),
            'status' => 'pending',
        ]);

        // Act: Verify data consistency
        $employeeFromAssignment = EmployeeModel::find($assignment->employee_id);
        $investorFromAssignment = User::find($assignment->user_id);
        $investmentFromCommission = Investment::find($commission->investment_id);

        // Assert: All relationships are consistent
        $this->assertEquals($this->fieldAgent->id, $employeeFromAssignment->id);
        $this->assertEquals($this->investor->id, $investorFromAssignment->id);
        $this->assertEquals($this->investment->id, $investmentFromCommission->id);

        // Assert: Cross-context data integrity
        $this->assertEquals($assignment->user_id, $commission->user_id);
        $this->assertEquals($assignment->employee_id, $commission->employee_id);
        $this->assertEquals($investmentFromCommission->user_id, $investorFromAssignment->id);
    }

    /** @test */
    public function field_agent_can_track_client_investment_performance()
    {
        // Arrange: Assign field agent and create multiple investments
        EmployeeClientAssignmentModel::create([
            'employee_id' => $this->fieldAgent->id,
            'user_id' => $this->investor->id,
            'assignment_type' => 'primary',
            'assigned_date' => now(),
            'is_active' => true,
        ]);

        // Create additional investments for the same client
        $investment2 = Investment::factory()->create([
            'user_id' => $this->investor->id,
            'tier' => $this->tier->name,
            'amount' => 3000,
            'status' => 'active',
            'investment_date' => now()->subDays(60),
            'lock_in_period_end' => now()->addMonths(12),
        ]);

        // Act: Get client's investment portfolio
        $clientInvestments = Investment::where('user_id', $this->investor->id)
            ->where('status', 'active')
            ->get();

        $totalInvestmentValue = $clientInvestments->sum('amount');
        $totalCurrentValue = $clientInvestments->sum(function ($investment) {
            return $investment->getCurrentValue();
        });

        // Assert: Field agent can track client performance
        $this->assertCount(2, $clientInvestments);
        $this->assertEquals(8000, $totalInvestmentValue); // 5000 + 3000
        $this->assertGreaterThan($totalInvestmentValue, $totalCurrentValue);

        // Assert: Performance metrics are available
        foreach ($clientInvestments as $investment) {
            $metrics = $investment->getPerformanceMetrics();
            $this->assertArrayHasKey('roi', $metrics);
            $this->assertArrayHasKey('growth_rate', $metrics);
            $this->assertIsNumeric($metrics['roi']);
        }
    }

    /** @test */
    public function commission_calculation_handles_investment_status_changes()
    {
        // Arrange: Create assignment and initial commission
        EmployeeClientAssignmentModel::create([
            'employee_id' => $this->fieldAgent->id,
            'user_id' => $this->investor->id,
            'assignment_type' => 'primary',
            'assigned_date' => now(),
            'is_active' => true,
        ]);

        // Act: Test commission calculation for active investment
        $activeCommission = $this->commissionService->calculateInvestmentFacilitationCommission(
            $this->fieldAgent,
            $this->investment
        );

        // Change investment status to inactive
        $this->investment->update(['status' => 'inactive']);

        // Test commission calculation for inactive investment
        $inactiveCommission = $this->commissionService->calculateInvestmentFacilitationCommission(
            $this->fieldAgent,
            $this->investment
        );

        // Assert: Commission calculation adapts to investment status
        $this->assertEquals(125.0, $activeCommission);
        $this->assertEquals(0.0, $inactiveCommission); // No commission for inactive investments
    }

    /** @test */
    public function field_agent_assignment_can_be_transferred()
    {
        // Arrange: Create initial assignment
        $originalAssignment = EmployeeClientAssignmentModel::create([
            'employee_id' => $this->fieldAgent->id,
            'user_id' => $this->investor->id,
            'assignment_type' => 'primary',
            'assigned_date' => now()->subDays(30),
            'is_active' => true,
        ]);

        // Create new field agent
        $newAgent = EmployeeModel::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'NewAgent',
            'email' => 'newagent@test.com',
            'employment_status' => EmploymentStatus::ACTIVE,
            'department_id' => $this->fieldAgent->department_id,
            'position_id' => $this->fieldAgent->position_id,
            'commission_rate' => 3.0,
        ]);

        // Act: Transfer assignment
        $originalAssignment->update([
            'is_active' => false,
            'unassigned_date' => now(),
        ]);

        $newAssignment = EmployeeClientAssignmentModel::create([
            'employee_id' => $newAgent->id,
            'user_id' => $this->investor->id,
            'assignment_type' => 'primary',
            'assigned_date' => now(),
            'is_active' => true,
        ]);

        // Assert: Assignment transfer is recorded correctly
        $this->assertFalse($originalAssignment->fresh()->is_active);
        $this->assertNotNull($originalAssignment->fresh()->unassigned_date);
        $this->assertTrue($newAssignment->is_active);
        $this->assertEquals($newAgent->id, $newAssignment->employee_id);

        // Assert: Only one active assignment exists
        $activeAssignments = EmployeeClientAssignmentModel::where('user_id', $this->investor->id)
            ->where('is_active', true)
            ->get();
        
        $this->assertCount(1, $activeAssignments);
        $this->assertEquals($newAgent->id, $activeAssignments->first()->employee_id);
    }

    /** @test */
    public function investment_tier_changes_affect_commission_calculations()
    {
        // Arrange: Create assignment
        EmployeeClientAssignmentModel::create([
            'employee_id' => $this->fieldAgent->id,
            'user_id' => $this->investor->id,
            'assignment_type' => 'primary',
            'assigned_date' => now(),
            'is_active' => true,
        ]);

        // Find or create elite tier
        $eliteTier = InvestmentTier::firstOrCreate(
            ['name' => 'Elite'],
            [
                'minimum_investment' => 10000,
                'fixed_profit_rate' => 15.0,
                'direct_referral_rate' => 10.0,
                'level2_referral_rate' => 7.0,
                'level3_referral_rate' => 3.0,
                'benefits' => [],
                'is_active' => true,
                'description' => 'Elite tier for testing',
                'order' => 5,
            ]
        );

        // Act: Upgrade investment to elite tier
        $this->investment->update([
            'tier' => $eliteTier->name,
            'amount' => 12000,
        ]);

        $upgradedCommission = $this->commissionService->calculateInvestmentFacilitationCommission(
            $this->fieldAgent,
            $this->investment->fresh()
        );

        // Assert: Commission reflects tier upgrade
        $this->assertEquals(300.0, $upgradedCommission); // 12000 * 2.5%
        $this->assertEquals($eliteTier->id, $this->investment->fresh()->tier_id);
    }
}