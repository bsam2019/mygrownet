<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\Services;

use App\Domain\Employee\Services\CommissionCalculationService;
use App\Domain\Employee\Services\CommissionCalculationResult;
use App\Domain\Employee\Services\PerformanceBonusResult;
use App\Domain\Employee\Services\MonthlyCommissionSummary;
use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\Entities\Department;
use App\Domain\Employee\Entities\Position;
use App\Domain\Employee\ValueObjects\Email;
use App\Domain\Employee\ValueObjects\Salary;
use App\Domain\Employee\ValueObjects\PerformanceMetrics;
use App\Domain\Employee\ValueObjects\DepartmentId;
use App\Domain\Employee\ValueObjects\PositionId;
use App\Domain\Employee\Exceptions\CommissionCalculationException;
use App\Domain\Employee\Exceptions\InvalidCommissionRateException;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Investment\Services\InvestmentTierService;
use App\Domain\Reward\Services\ReferralMatrixService;
use App\Models\Investment;
use App\Models\User;
use App\Models\InvestmentTier;
use DateTimeImmutable;
use Mockery;
use Tests\TestCase;

class CommissionCalculationServiceTest extends TestCase
{
    private EmployeeRepositoryInterface $employeeRepository;
    private InvestmentTierService $investmentTierService;
    private ReferralMatrixService $referralMatrixService;
    private CommissionCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->employeeRepository = Mockery::mock(EmployeeRepositoryInterface::class);
        $this->investmentTierService = Mockery::mock(InvestmentTierService::class);
        $this->referralMatrixService = Mockery::mock(ReferralMatrixService::class);
        
        $this->service = new CommissionCalculationService(
            $this->employeeRepository,
            $this->investmentTierService,
            $this->referralMatrixService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_calculate_field_agent_commission(): void
    {
        // Arrange
        $fieldAgent = $this->createFieldAgentWithPerformance();
        $investment = $this->createInvestment(10000.0);
        $tier = $this->createInvestmentTier('Builder', 5000.0);

        $this->investmentTierService
            ->shouldReceive('calculateTierForAmount')
            ->with(10000.0)
            ->once()
            ->andReturn($tier);

        // Act
        $result = $this->service->calculateFieldAgentCommission($fieldAgent, $investment);

        // Assert
        $this->assertInstanceOf(CommissionCalculationResult::class, $result);
        $this->assertEquals($fieldAgent->getId(), $result->getEmployee()->getId());
        $this->assertGreaterThan(0, $result->getCommissionAmount());
        $this->assertEquals(5.0, $result->getBaseCommissionRate()); // From position
        $this->assertEquals(1.2, $result->getTierMultiplier()); // Builder tier multiplier
        $this->assertGreaterThanOrEqual(1.0, $result->getPerformanceMultiplier()); // Good performance
    }

    public function test_throws_exception_for_inactive_employee(): void
    {
        // Arrange
        $inactiveAgent = $this->createInactiveFieldAgent();
        $investment = $this->createInvestment(10000.0);

        // Act & Assert
        $this->expectException(CommissionCalculationException::class);
        $this->service->calculateFieldAgentCommission($inactiveAgent, $investment);
    }

    public function test_throws_exception_for_zero_commission_rate(): void
    {
        // Arrange
        $fieldAgent = $this->createFieldAgentWithZeroCommission();
        $investment = $this->createInvestment(10000.0);

        // Act & Assert
        $this->expectException(InvalidCommissionRateException::class);
        $this->service->calculateFieldAgentCommission($fieldAgent, $investment);
    }

    public function test_throws_exception_for_invalid_investment_status(): void
    {
        // Arrange
        $fieldAgent = $this->createFieldAgentWithPerformance();
        $investment = $this->createInvestment(10000.0, 'pending');

        // Act & Assert
        $this->expectException(CommissionCalculationException::class);
        $this->service->calculateFieldAgentCommission($fieldAgent, $investment);
    }

    public function test_can_calculate_referral_commission(): void
    {
        // Arrange
        $fieldAgent = $this->createFieldAgentWithUser();
        $referredUser = User::factory()->make(['id' => 2]);
        $investment = $this->createInvestment(10000.0);

        $matrixCommissions = [
            [
                'referrer_id' => $fieldAgent->getUser()->id,
                'referee_id' => $referredUser->id,
                'investment_id' => $investment->id,
                'level' => 1,
                'amount' => 500.0,
                'commission_type' => 'matrix',
                'matrix_position' => 1,
                'tier_name' => 'Builder',
                'calculation_details' => [
                    'investment_amount' => 10000.0,
                    'tier_rate' => 5.0,
                    'matrix_multiplier' => 1.0,
                    'formula' => '(10000 * 5% * 1.0) / 100'
                ]
            ]
        ];

        $this->referralMatrixService
            ->shouldReceive('calculateMatrixCommissions')
            ->with(Mockery::type(Investment::class))
            ->once()
            ->andReturn($matrixCommissions);

        // Act
        $result = $this->service->calculateReferralCommission($fieldAgent, $referredUser, $investment);

        // Assert
        $this->assertInstanceOf(CommissionCalculationResult::class, $result);
        $this->assertEquals(500.0, $result->getCommissionAmount());
        $this->assertEquals(5.0, $result->getBaseCommissionRate());
        $this->assertEquals(1.0, $result->getTierMultiplier());
    }

    public function test_returns_zero_commission_when_no_referral_applicable(): void
    {
        // Arrange
        $fieldAgent = $this->createFieldAgentWithUser();
        $referredUser = User::factory()->make(['id' => 2]);
        $investment = $this->createInvestment(10000.0);

        $this->referralMatrixService
            ->shouldReceive('calculateMatrixCommissions')
            ->with(Mockery::type(Investment::class))
            ->once()
            ->andReturn([]); // No commissions

        // Act
        $result = $this->service->calculateReferralCommission($fieldAgent, $referredUser, $investment);

        // Assert
        $this->assertEquals(0.0, $result->getCommissionAmount());
        $this->assertStringContainsString('No referral commission applicable', $result->getCalculationDetails()['reason']);
    }

    public function test_can_calculate_performance_bonus(): void
    {
        // Arrange
        $fieldAgent = $this->createFieldAgentWithHighPerformance();
        $periodStart = new DateTimeImmutable('2024-01-01');
        $periodEnd = new DateTimeImmutable('2024-01-31');

        // Act
        $result = $this->service->calculatePerformanceBonus($fieldAgent, $periodStart, $periodEnd);

        // Assert
        $this->assertInstanceOf(PerformanceBonusResult::class, $result);
        $this->assertGreaterThan(0, $result->getBonusAmount());
        $this->assertEquals($periodStart, $result->getPeriodStart());
        $this->assertEquals($periodEnd, $result->getPeriodEnd());
        
        $bonusDetails = $result->getBonusDetails();
        $this->assertArrayHasKey('investment_facilitation_bonus', $bonusDetails);
        $this->assertArrayHasKey('client_retention_bonus', $bonusDetails);
        $this->assertArrayHasKey('goal_achievement_bonus', $bonusDetails);
        $this->assertArrayHasKey('new_client_acquisition_bonus', $bonusDetails);
    }

    public function test_returns_zero_bonus_when_no_performance_metrics(): void
    {
        // Arrange
        $fieldAgent = $this->createFieldAgentWithoutPerformance();
        $periodStart = new DateTimeImmutable('2024-01-01');
        $periodEnd = new DateTimeImmutable('2024-01-31');

        // Act
        $result = $this->service->calculatePerformanceBonus($fieldAgent, $periodStart, $periodEnd);

        // Assert
        $this->assertEquals(0.0, $result->getBonusAmount());
        $this->assertEquals('No performance metrics available', $result->getDescription());
    }

    public function test_can_calculate_monthly_commissions(): void
    {
        // Arrange
        $fieldAgent = $this->createFieldAgentWithPerformance();
        $month = new DateTimeImmutable('2024-01-15');

        // Act
        $result = $this->service->calculateMonthlyCommissions($fieldAgent, $month);

        // Assert
        $this->assertInstanceOf(MonthlyCommissionSummary::class, $result);
        $this->assertEquals($fieldAgent->getId(), $result->getEmployee()->getId());
        $this->assertIsFloat($result->getTotalCommissions());
        $this->assertIsInt($result->getInvestmentCount());
        $this->assertIsFloat($result->getReferralCommissions());
        $this->assertIsFloat($result->getPerformanceBonus());
        $this->assertIsArray($result->getCommissionBreakdown());
    }

    public function test_applies_tier_multipliers_correctly(): void
    {
        // Arrange
        $fieldAgent = $this->createFieldAgentWithPerformance();
        $investment = $this->createInvestment(10000.0);
        
        // Test different tiers
        $testCases = [
            ['Basic', 1.0],
            ['Starter', 1.1],
            ['Builder', 1.2],
            ['Leader', 1.3],
            ['Elite', 1.5]
        ];

        foreach ($testCases as [$tierName, $expectedMultiplier]) {
            $tier = $this->createInvestmentTier($tierName, 1000.0);
            
            $this->investmentTierService
                ->shouldReceive('calculateTierForAmount')
                ->with(10000.0)
                ->once()
                ->andReturn($tier);

            // Act
            $result = $this->service->calculateFieldAgentCommission($fieldAgent, $investment);

            // Assert
            $this->assertEquals($expectedMultiplier, $result->getTierMultiplier(), "Failed for tier: {$tierName}");
        }
    }

    public function test_applies_performance_multipliers_correctly(): void
    {
        // Arrange
        $investment = $this->createInvestment(10000.0);
        $tier = $this->createInvestmentTier('Basic', 500.0);

        $this->investmentTierService
            ->shouldReceive('calculateTierForAmount')
            ->with(10000.0)
            ->times(4)
            ->andReturn($tier);

        // Test different performance levels
        $testCases = [
            [9.5, 1.3], // Exceptional
            [8.5, 1.2], // Excellent
            [7.5, 1.1], // Good
            [6.5, 1.0], // Average
        ];

        foreach ($testCases as [$performanceScore, $expectedMultiplier]) {
            $fieldAgent = $this->createFieldAgentWithSpecificPerformance($performanceScore);

            // Act
            $result = $this->service->calculateFieldAgentCommission($fieldAgent, $investment);

            // Assert
            $this->assertEquals($expectedMultiplier, $result->getPerformanceMultiplier(), "Failed for performance score: {$performanceScore}");
        }
    }

    public function test_applies_commission_caps(): void
    {
        // Arrange
        $fieldAgent = $this->createFieldAgentWithHighCommissionRate();
        $investment = $this->createInvestment(1000.0); // Small investment
        $tier = $this->createInvestmentTier('Elite', 500.0);

        $this->investmentTierService
            ->shouldReceive('calculateTierForAmount')
            ->with(1000.0)
            ->once()
            ->andReturn($tier);

        // Act
        $result = $this->service->calculateFieldAgentCommission($fieldAgent, $investment);

        // Assert
        // Commission should be capped at minimum K100
        $this->assertGreaterThanOrEqual(100.0, $result->getCommissionAmount());
        // Commission should not exceed 20% of investment (K200)
        $this->assertLessThanOrEqual(200.0, $result->getCommissionAmount());
    }

    private function createFieldAgentWithPerformance(): Employee
    {
        $department = $this->createDepartment('Sales');
        $position = $this->createPosition($department, 'Field Agent', 5.0);
        $employee = $this->createEmployee($department, $position);
        
        $metrics = new PerformanceMetrics(
            25000.0, // investments facilitated (amount in Kwacha)
            85.0, // client retention rate
            4000.0, // commission generated
            8, // new client acquisitions
            78.0, // goal achievement rate
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-03-31')
        );

        $employee->updatePerformance($metrics, new DateTimeImmutable());
        
        return $employee;
    }

    private function createFieldAgentWithHighPerformance(): Employee
    {
        $department = $this->createDepartment('Sales');
        $position = $this->createPosition($department, 'Field Agent', 5.0);
        $employee = $this->createEmployee($department, $position);
        
        $metrics = new PerformanceMetrics(
            25.0, // high investments facilitated
            95.0, // high client retention rate
            25000.0, // high commission generated
            8, // high new client acquisitions
            95.0, // high goal achievement rate
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-03-31')
        );

        $employee->updatePerformance($metrics, new DateTimeImmutable());
        
        return $employee;
    }

    private function createFieldAgentWithoutPerformance(): Employee
    {
        $department = $this->createDepartment('Sales');
        $position = $this->createPosition($department, 'Field Agent', 5.0);
        return $this->createEmployee($department, $position);
    }

    private function createFieldAgentWithSpecificPerformance(float $overallScore): Employee
    {
        $department = $this->createDepartment('Sales');
        $position = $this->createPosition($department, 'Field Agent', 5.0);
        $employee = $this->createEmployee($department, $position);
        
        // Create metrics that will result in the desired overall score
        // Based on the normalization logic in PerformanceMetrics
        $metrics = match (true) {
            $overallScore >= 9.0 => new PerformanceMetrics(
                45000.0, // investments facilitated (90% of 50k threshold = 9.0 score)
                95.0, // client retention rate (9.5 score)
                4500.0, // commission generated (9.0 score)
                18, // new client acquisitions (9.0 score)
                90.0, // goal achievement rate (9.0 score)
                new DateTimeImmutable('2024-01-01'),
                new DateTimeImmutable('2024-03-31')
            ),
            $overallScore >= 8.0 => new PerformanceMetrics(
                40000.0, // investments facilitated (8.0 score)
                85.0, // client retention rate (8.5 score)
                4000.0, // commission generated (8.0 score)
                16, // new client acquisitions (8.0 score)
                80.0, // goal achievement rate (8.0 score)
                new DateTimeImmutable('2024-01-01'),
                new DateTimeImmutable('2024-03-31')
            ),
            $overallScore >= 7.0 => new PerformanceMetrics(
                35000.0, // investments facilitated (7.0 score)
                75.0, // client retention rate (7.5 score)
                3500.0, // commission generated (7.0 score)
                14, // new client acquisitions (7.0 score)
                70.0, // goal achievement rate (7.0 score)
                new DateTimeImmutable('2024-01-01'),
                new DateTimeImmutable('2024-03-31')
            ),
            default => new PerformanceMetrics(
                30000.0, // investments facilitated (6.0 score)
                65.0, // client retention rate (6.5 score)
                3000.0, // commission generated (6.0 score)
                12, // new client acquisitions (6.0 score)
                60.0, // goal achievement rate (6.0 score)
                new DateTimeImmutable('2024-01-01'),
                new DateTimeImmutable('2024-03-31')
            )
        };

        $employee->updatePerformance($metrics, new DateTimeImmutable());
        
        return $employee;
    }

    private function createFieldAgentWithUser(): Employee
    {
        $employee = $this->createFieldAgentWithPerformance();
        $user = User::factory()->make(['id' => 1]);
        $employee->assignToUser($user);
        return $employee;
    }

    private function createInactiveFieldAgent(): Employee
    {
        $department = $this->createDepartment('Sales');
        $position = $this->createPosition($department, 'Field Agent', 5.0);
        $employee = $this->createEmployee($department, $position);
        
        // Make employee inactive
        $employee->changeEmploymentStatus(\App\Domain\Employee\ValueObjects\EmploymentStatus::inactive('Test inactive status'));
        
        return $employee;
    }

    private function createFieldAgentWithZeroCommission(): Employee
    {
        $department = $this->createDepartment('Sales');
        $position = $this->createPosition($department, 'Field Agent', 0.0); // Zero commission rate
        return $this->createEmployee($department, $position);
    }

    private function createFieldAgentWithHighCommissionRate(): Employee
    {
        $department = $this->createDepartment('Sales');
        $position = $this->createPosition($department, 'Field Agent', 50.0); // Very high commission rate
        $employee = $this->createEmployee($department, $position);
        
        $metrics = new PerformanceMetrics(
            10.0, 90.0, 15000.0, 5, 85.0,
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-03-31')
        );

        $employee->updatePerformance($metrics, new DateTimeImmutable());
        
        return $employee;
    }

    private function createDepartment(string $name = 'Sales'): Department
    {
        return new Department(
            DepartmentId::generate(),
            $name,
            'Test department',
            null, // parent department
            true  // isActive
        );
    }

    private function createPosition(Department $department, string $title = 'Field Agent', float $commissionRate = 5.0): Position
    {
        return new Position(
            PositionId::generate(),
            $title,
            'Test position',
            $department,
            Salary::fromKwacha(40000),
            Salary::fromKwacha(80000),
            true, // Commission eligible
            $commissionRate
        );
    }

    private function createEmployee(Department $department, Position $position): Employee
    {
        return Employee::create(
            'EMP20250001',
            'John',
            'Doe',
            Email::fromString('john.doe@example.com'),
            new DateTimeImmutable('2024-01-15'),
            $department,
            $position,
            Salary::fromKwacha(50000)
        );
    }

    private function createInvestment(float $amount, string $status = 'active'): Investment
    {
        return new Investment([
            'id' => 1,
            'user_id' => 1,
            'amount' => $amount,
            'status' => $status,
            'investment_date' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function createInvestmentTier(string $name, float $minimumInvestment): InvestmentTier
    {
        return new InvestmentTier([
            'id' => 1,
            'name' => $name,
            'minimum_investment' => $minimumInvestment,
            'fixed_profit_rate' => 10.0,
            'direct_referral_rate' => 5.0,
            'level2_referral_rate' => 3.0,
            'level3_referral_rate' => 2.0,
            'reinvestment_bonus_rate' => 2.0,
            'is_active' => true
        ]);
    }
}