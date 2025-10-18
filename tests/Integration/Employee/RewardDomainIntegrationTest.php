<?php

namespace Tests\Integration\Employee;

use Tests\TestCase;
use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\ReferralCommission;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use App\Services\ReferralService;
use App\Domain\Reward\Services\ReferralMatrixService;
use App\Domain\Employee\Services\CommissionCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

class RewardDomainIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ReferralService $referralService;
    protected ReferralMatrixService $matrixService;
    protected CommissionCalculationService $employeeCommissionService;
    protected EmployeeModel $fieldAgent;
    protected User $investor;
    protected User $referrer;
    protected Investment $investment;
    protected InvestmentTier $tier;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->referralService = app(ReferralService::class);
        $this->matrixService = app(ReferralMatrixService::class);
        $this->employeeCommissionService = app(CommissionCalculationService::class);
        
        $this->setupTestData();
    }

    protected function setupTestData(): void
    {
        // Create investment tier
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

        // Create referrer user
        $this->referrer = User::factory()->create([
            'email' => 'referrer@test.com',
            'current_investment_tier_id' => $this->tier->id,
        ]);

        // Create investor user (referred by referrer)
        $this->investor = User::factory()->create([
            'email' => 'investor@test.com',
            'referrer_id' => $this->referrer->id,
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
            'commission_eligible' => true,
            'commission_rate' => 2.5,
        ]);

        // Create field agent employee
        $this->fieldAgent = EmployeeModel::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Agent',
            'email' => 'agent@test.com',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'commission_rate' => 2.5,
        ]);
    }

    public function test_employee_commission_integrates_with_referral_system()
    {
        // Act: Process referral commission through existing system
        $this->referralService->processReferralCommission($this->investment);

        // Verify referral commission was created
        $referralCommission = ReferralCommission::where('investment_id', $this->investment->id)
            ->where('referrer_id', $this->referrer->id)
            ->first();

        $this->assertNotNull($referralCommission);
        $this->assertEquals(1, $referralCommission->level);
        $this->assertEquals(250.0, $referralCommission->amount); // 5000 * 5%

        // Act: Calculate employee commission for the same investment
        $employeeCommission = $this->employeeCommissionService->calculateInvestmentFacilitationCommission(
            $this->fieldAgent,
            $this->investment
        );

        // Assert: Both commission systems work independently
        $this->assertEquals(125.0, $employeeCommission); // 5000 * 2.5%
        $this->assertNotEquals($referralCommission->amount, $employeeCommission);

        // Assert: Total commissions don't exceed reasonable limits
        $totalCommissions = $referralCommission->amount + $employeeCommission;
        $this->assertLessThan($this->investment->amount * 0.20, $totalCommissions); // Less than 20% of investment
    }

    public function test_performance_bonus_calculations_with_existing_reward_structures()
    {
        // Arrange: Create multiple referral commissions for performance calculation
        ReferralCommission::factory()->create([
            'referrer_id' => $this->referrer->id,
            'referred_id' => $this->investor->id,
            'investment_id' => $this->investment->id,
            'level' => 1,
            'amount' => 250.0,
            'status' => 'paid',
        ]);

        // Create employee commission record
        EmployeeCommissionModel::create([
            'employee_id' => $this->fieldAgent->id,
            'investment_id' => $this->investment->id,
            'user_id' => $this->investor->id,
            'commission_type' => 'investment_facilitation',
            'base_amount' => $this->investment->amount,
            'commission_rate' => $this->fieldAgent->commission_rate,
            'commission_amount' => 125.0,
            'calculation_date' => now(),
            'status' => 'paid',
        ]);

        // Act: Calculate performance bonus considering existing reward structures
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();
        
        $performanceBonus = $this->employeeCommissionService->calculatePerformanceBonus(
            $this->fieldAgent,
            $periodStart,
            $periodEnd
        );

        // Assert: Performance bonus is calculated correctly
        $this->assertGreaterThan(0, $performanceBonus->getBonusAmount());
        
        // Assert: Bonus details include relevant metrics
        $bonusDetails = $performanceBonus->getBonusDetails();
        $this->assertArrayHasKey('investment_facilitation_bonus', $bonusDetails);
    }

    public function test_cross_domain_event_handling_and_data_synchronization()
    {
        // Act: Process referral commission (triggers reward domain events)
        $this->referralService->processReferralCommission($this->investment);

        // Act: Process employee commission (triggers employee domain events)
        EmployeeCommissionModel::create([
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

        // Assert: Both commission types exist for the same investment
        $referralCommissions = ReferralCommission::where('investment_id', $this->investment->id)->get();
        $employeeCommissions = EmployeeCommissionModel::where('investment_id', $this->investment->id)->get();

        $this->assertCount(1, $referralCommissions);
        $this->assertCount(1, $employeeCommissions);

        // Assert: Data consistency across domains
        $referralCommission = $referralCommissions->first();
        $employeeCommission = $employeeCommissions->first();

        $this->assertEquals($this->investment->id, $referralCommission->investment_id);
        $this->assertEquals($this->investment->id, $employeeCommission->investment_id);
        $this->assertEquals($this->investor->id, $referralCommission->referred_id);
        $this->assertEquals($this->investor->id, $employeeCommission->user_id);
    }

    public function test_matrix_commission_integration_with_employee_commissions()
    {
        // Arrange: Create matrix structure
        $matrixCommissions = $this->matrixService->calculateMatrixCommissions($this->investment);

        // Act: Process both matrix and employee commissions
        if (!empty($matrixCommissions)) {
            $matrixResult = $this->matrixService->processMatrixCommissions($this->investment);
            $this->assertTrue($matrixResult['success']);
        }

        $employeeCommission = $this->employeeCommissionService->calculateInvestmentFacilitationCommission(
            $this->fieldAgent,
            $this->investment
        );

        // Assert: Matrix and employee commissions can coexist
        $this->assertEquals(125.0, $employeeCommission);
        
        // Assert: Total commission structure is reasonable
        $totalReferralCommissions = ReferralCommission::where('investment_id', $this->investment->id)
            ->sum('amount');
        
        $totalCommissions = $totalReferralCommissions + $employeeCommission;
        $this->assertLessThan($this->investment->amount * 0.25, $totalCommissions); // Less than 25% total
    }

    public function test_referral_service_integration_with_employee_assignments()
    {
        // Arrange: Create employee-client assignment
        $assignment = \App\Infrastructure\Persistence\Eloquent\EmployeeClientAssignmentModel::create([
            'employee_id' => $this->fieldAgent->id,
            'user_id' => $this->investor->id,
            'assignment_type' => 'primary',
            'assigned_date' => now(),
            'is_active' => true,
        ]);

        // Act: Process referral commission
        $this->referralService->processReferralCommission($this->investment);

        // Act: Get referral stats for the referrer
        $referralStats = $this->referralService->getUserReferralStats($this->referrer);

        // Assert: Referral stats are accurate
        $this->assertEquals(1, $referralStats['total_referrals']);
        $this->assertEquals(1, $referralStats['active_referrals']);
        $this->assertGreaterThan(0, $referralStats['pending_commission']);

        // Assert: Employee assignment doesn't interfere with referral tracking
        $this->assertTrue($assignment->is_active);
        $this->assertEquals($this->investor->id, $assignment->user_id);
        $this->assertEquals($this->fieldAgent->id, $assignment->employee_id);
    }

    public function test_commission_calculation_considers_existing_reward_caps()
    {
        // Arrange: Create multiple investments to test commission caps
        $investment2 = Investment::factory()->create([
            'user_id' => $this->investor->id,
            'tier' => $this->tier->name,
            'amount' => 10000,
            'status' => 'active',
            'investment_date' => now()->subDays(15),
            'lock_in_period_end' => now()->addMonths(12),
        ]);

        // Act: Process referral commissions for both investments
        $this->referralService->processReferralCommission($this->investment);
        $this->referralService->processReferralCommission($investment2);

        // Act: Calculate employee commissions
        $employeeCommission1 = $this->employeeCommissionService->calculateInvestmentFacilitationCommission(
            $this->fieldAgent,
            $this->investment
        );

        $employeeCommission2 = $this->employeeCommissionService->calculateInvestmentFacilitationCommission(
            $this->fieldAgent,
            $investment2
        );

        // Assert: Commissions scale appropriately
        $this->assertEquals(125.0, $employeeCommission1); // 5000 * 2.5%
        $this->assertEquals(250.0, $employeeCommission2); // 10000 * 2.5%

        // Assert: Total referral commissions are reasonable
        $totalReferralCommissions = ReferralCommission::whereIn('investment_id', [$this->investment->id, $investment2->id])
            ->sum('amount');

        $totalEmployeeCommissions = $employeeCommission1 + $employeeCommission2;
        $totalInvestmentAmount = $this->investment->amount + $investment2->amount;

        // Assert: Combined commission structure doesn't exceed reasonable limits
        $totalCommissionPercentage = ($totalReferralCommissions + $totalEmployeeCommissions) / $totalInvestmentAmount;
        $this->assertLessThan(0.20, $totalCommissionPercentage); // Less than 20% total
    }

    public function test_reward_system_handles_employee_status_changes()
    {
        // Arrange: Process initial commissions
        $this->referralService->processReferralCommission($this->investment);
        
        $initialEmployeeCommission = $this->employeeCommissionService->calculateInvestmentFacilitationCommission(
            $this->fieldAgent,
            $this->investment
        );

        // Act: Change employee status to inactive
        $this->fieldAgent->update(['employment_status' => 'inactive']);

        // Act: Try to calculate commission for inactive employee
        $inactiveEmployeeCommission = $this->employeeCommissionService->calculateInvestmentFacilitationCommission(
            $this->fieldAgent->fresh(),
            $this->investment
        );

        // Assert: Active employee gets commission, inactive doesn't
        $this->assertEquals(125.0, $initialEmployeeCommission);
        $this->assertEquals(0.0, $inactiveEmployeeCommission);

        // Assert: Referral commissions are unaffected by employee status
        $referralCommission = ReferralCommission::where('investment_id', $this->investment->id)->first();
        $this->assertNotNull($referralCommission);
        $this->assertEquals(250.0, $referralCommission->amount);
    }

    public function test_quarterly_bonus_integration_with_referral_performance()
    {
        // Arrange: Create multiple referral commissions over time
        $referralCommissions = collect();
        
        for ($i = 0; $i < 3; $i++) {
            $investment = Investment::factory()->create([
                'user_id' => User::factory()->create(['referrer_id' => $this->referrer->id])->id,
                'tier' => $this->tier->name,
                'amount' => 3000,
                'status' => 'active',
                'investment_date' => now()->subDays(30 + $i * 10),
                'lock_in_period_end' => now()->addMonths(12),
            ]);

            $this->referralService->processReferralCommission($investment);
            
            $referralCommissions->push(
                ReferralCommission::where('investment_id', $investment->id)->first()
            );
        }

        // Act: Calculate quarterly performance considering referral activity
        $quarterStart = now()->startOfQuarter();
        $quarterEnd = now()->endOfQuarter();
        
        $quarterlyCommissions = $this->employeeCommissionService->calculateQuarterlyCommissions(
            $this->fieldAgent,
            now()->year,
            now()->quarter
        );

        // Assert: Quarterly calculations work with referral system
        $this->assertGreaterThanOrEqual(0, $quarterlyCommissions->getTotalCommissions());
        
        // Assert: Referral system performance is tracked separately
        $totalReferralAmount = $referralCommissions->sum('amount');
        $this->assertEquals(450.0, $totalReferralAmount); // 3 * 3000 * 5%
    }

    public function test_data_consistency_across_reward_and_employee_domains()
    {
        // Arrange: Create comprehensive test scenario
        $this->referralService->processReferralCommission($this->investment);
        
        EmployeeCommissionModel::create([
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
        $investment = Investment::with(['user', 'referralCommissions'])->find($this->investment->id);
        $referralCommission = $investment->referralCommissions->first();
        $employeeCommission = EmployeeCommissionModel::where('investment_id', $investment->id)->first();

        // Assert: All relationships are consistent
        $this->assertEquals($investment->user_id, $this->investor->id);
        $this->assertEquals($referralCommission->referred_id, $this->investor->id);
        $this->assertEquals($employeeCommission->user_id, $this->investor->id);
        
        // Assert: Investment amounts match across domains
        $this->assertEquals($investment->amount, $referralCommission->amount / 0.05); // 5% commission
        $this->assertEquals($investment->amount, $employeeCommission->base_amount);
        
        // Assert: Commission calculations are independent but consistent
        $expectedReferralCommission = $investment->amount * 0.05; // 5%
        $expectedEmployeeCommission = $investment->amount * 0.025; // 2.5%
        
        $this->assertEquals($expectedReferralCommission, $referralCommission->amount);
        $this->assertEquals($expectedEmployeeCommission, $employeeCommission->commission_amount);
    }

    public function test_reward_system_spillover_with_employee_tracking()
    {
        // Arrange: Create matrix structure for spillover testing
        $matrixData = $this->matrixService->buildMatrix($this->referrer);
        
        // Act: Process spillover if applicable
        if ($matrixData['has_position']) {
            $spilloverResult = $this->matrixService->processSpillover($this->investor, $this->referrer);
            $this->assertTrue($spilloverResult);
        }

        // Act: Calculate employee commission considering matrix structure
        $employeeCommission = $this->employeeCommissionService->calculateInvestmentFacilitationCommission(
            $this->fieldAgent,
            $this->investment
        );

        // Assert: Employee commission calculation is unaffected by matrix spillover
        $this->assertEquals(125.0, $employeeCommission);
        
        // Assert: Matrix and employee systems operate independently
        $matrixCommissions = $this->matrixService->calculateMatrixCommissions($this->investment);
        $this->assertIsArray($matrixCommissions);
    }
}