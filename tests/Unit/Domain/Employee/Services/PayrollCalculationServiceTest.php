<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\Services;

use App\Domain\Employee\Services\PayrollCalculationService;
use App\Domain\Employee\Services\PayrollCalculationResult;
use App\Domain\Employee\Services\PayrollReport;
use App\Domain\Employee\Services\Payslip;
use App\Domain\Employee\Services\YearToDateSummary;
use App\Domain\Employee\Services\CommissionCalculationService;
use App\Domain\Employee\Services\PerformanceTrackingService;
use App\Domain\Employee\Services\MonthlyCommissionSummary;
use App\Domain\Employee\Services\PerformanceBonusResult;
use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\Entities\Department;
use App\Domain\Employee\Entities\Position;
use App\Domain\Employee\ValueObjects\Email;
use App\Domain\Employee\ValueObjects\Salary;
use App\Domain\Employee\ValueObjects\PerformanceMetrics;
use App\Domain\Employee\ValueObjects\DepartmentId;
use App\Domain\Employee\ValueObjects\PositionId;
use App\Domain\Employee\Exceptions\EmployeeException;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use DateTimeImmutable;
use Mockery;
use Tests\TestCase;

class PayrollCalculationServiceTest extends TestCase
{
    private EmployeeRepositoryInterface $employeeRepository;
    private CommissionCalculationService $commissionCalculationService;
    private PerformanceTrackingService $performanceTrackingService;
    private PayrollCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->employeeRepository = Mockery::mock(EmployeeRepositoryInterface::class);
        $this->commissionCalculationService = Mockery::mock(CommissionCalculationService::class);
        $this->performanceTrackingService = Mockery::mock(PerformanceTrackingService::class);
        
        $this->service = new PayrollCalculationService(
            $this->employeeRepository,
            $this->commissionCalculationService,
            $this->performanceTrackingService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_calculate_total_compensation(): void
    {
        // Arrange
        $employee = $this->createEmployee();
        $periodStart = new DateTimeImmutable('2024-01-01');
        $periodEnd = new DateTimeImmutable('2024-01-31');

        $this->mockCommissionCalculation($employee, $periodStart, 5000.0);
        $this->mockPerformanceBonusCalculation($employee, $periodStart, $periodEnd, 2000.0);

        // Act
        $result = $this->service->calculateTotalCompensation($employee, $periodStart, $periodEnd);

        // Assert
        $this->assertInstanceOf(PayrollCalculationResult::class, $result);
        $this->assertEquals($employee->getId(), $result->getEmployee()->getId());
        $this->assertEquals($periodStart, $result->getPeriodStart());
        $this->assertEquals($periodEnd, $result->getPeriodEnd());
        $this->assertEquals(50000.0, $result->getBaseSalary()); // Full month salary
        $this->assertEquals(5000.0, $result->getCommissions());
        $this->assertEquals(2000.0, $result->getPerformanceBonus());
        $this->assertGreaterThan(0, $result->getAllowances());
        $this->assertGreaterThan(0, $result->getGrossPay());
        $this->assertGreaterThan(0, $result->getDeductions()['total']);
        $this->assertLessThan($result->getGrossPay(), $result->getNetPay());
    }

    public function test_prorates_salary_for_partial_month(): void
    {
        // Arrange
        $employee = $this->createEmployee();
        $periodStart = new DateTimeImmutable('2024-01-15'); // Mid-month start
        $periodEnd = new DateTimeImmutable('2024-01-31');

        $this->mockCommissionCalculation($employee, $periodStart, 2500.0);
        $this->mockPerformanceBonusCalculation($employee, $periodStart, $periodEnd, 1000.0);

        // Act
        $result = $this->service->calculateTotalCompensation($employee, $periodStart, $periodEnd);

        // Assert
        // Should be prorated for 17 days out of 31 days in January
        $expectedSalary = (50000.0 / 31) * 17;
        $this->assertEquals(round($expectedSalary, 2), round($result->getBaseSalary(), 2));
    }

    public function test_throws_exception_for_inactive_employee(): void
    {
        // Arrange
        $inactiveEmployee = $this->createInactiveEmployee();
        $periodStart = new DateTimeImmutable('2024-01-01');
        $periodEnd = new DateTimeImmutable('2024-01-31');

        // Act & Assert
        $this->expectException(EmployeeException::class);
        $this->service->calculateTotalCompensation($inactiveEmployee, $periodStart, $periodEnd);
    }

    public function test_throws_exception_for_invalid_period(): void
    {
        // Arrange
        $employee = $this->createEmployee();
        $periodStart = new DateTimeImmutable('2024-01-31');
        $periodEnd = new DateTimeImmutable('2024-01-01'); // End before start

        // Act & Assert
        $this->expectException(EmployeeException::class);
        $this->service->calculateTotalCompensation($employee, $periodStart, $periodEnd);
    }

    public function test_calculates_allowances_based_on_position(): void
    {
        // Arrange
        $fieldAgent = $this->createFieldAgent();
        $manager = $this->createManager();
        $periodStart = new DateTimeImmutable('2024-01-01');
        $periodEnd = new DateTimeImmutable('2024-01-31');

        $this->mockCommissionCalculation($fieldAgent, $periodStart, 3000.0);
        $this->mockPerformanceBonusCalculation($fieldAgent, $periodStart, $periodEnd, 1000.0);
        
        $this->mockCommissionCalculation($manager, $periodStart, 0.0);
        $this->mockPerformanceBonusCalculation($manager, $periodStart, $periodEnd, 1500.0);

        // Act
        $fieldAgentResult = $this->service->calculateTotalCompensation($fieldAgent, $periodStart, $periodEnd);
        $managerResult = $this->service->calculateTotalCompensation($manager, $periodStart, $periodEnd);

        // Assert
        // Field agent should get transport allowance (K1,000) + communication allowance (K300) = K1,300
        $this->assertEquals(1300.0, $fieldAgentResult->getAllowances());
        
        // Manager should get housing allowance (K2,000) + communication allowance (K300) = K2,300
        $this->assertEquals(2300.0, $managerResult->getAllowances());
    }

    public function test_calculates_taxes_correctly(): void
    {
        // Arrange
        $employee = $this->createHighSalaryEmployee();
        $grossPay = 100000.0; // High gross pay to test tax brackets
        $periodStart = new DateTimeImmutable('2024-01-01');
        $periodEnd = new DateTimeImmutable('2024-01-31');

        // Act
        $taxes = $this->service->calculatePayrollTaxes($employee, $grossPay, $periodStart, $periodEnd);

        // Assert
        $this->assertArrayHasKey('paye', $taxes);
        $this->assertArrayHasKey('napsa', $taxes);
        $this->assertArrayHasKey('nhima', $taxes);
        $this->assertArrayHasKey('total_taxes', $taxes);
        
        $this->assertGreaterThan(0, $taxes['paye']);
        $this->assertEquals(2500.0, $taxes['napsa']); // Capped at K2,500
        $this->assertEquals(1000.0, $taxes['nhima']); // 1% of gross pay
        $this->assertEquals($taxes['paye'] + $taxes['napsa'] + $taxes['nhima'], $taxes['total_taxes']);
    }

    public function test_can_generate_payroll_report(): void
    {
        // Arrange
        $employees = [
            $this->createEmployee('John', 'Doe'),
            $this->createEmployee('Jane', 'Smith'),
            $this->createFieldAgent('Bob', 'Johnson')
        ];
        $periodStart = new DateTimeImmutable('2024-01-01');
        $periodEnd = new DateTimeImmutable('2024-01-31');

        // Mock calculations for each employee
        foreach ($employees as $employee) {
            $this->mockCommissionCalculation($employee, $periodStart, 2000.0);
            $this->mockPerformanceBonusCalculation($employee, $periodStart, $periodEnd, 1000.0);
        }

        // Act
        $report = $this->service->generatePayrollReport($employees, $periodStart, $periodEnd);

        // Assert
        $this->assertInstanceOf(PayrollReport::class, $report);
        $this->assertEquals($periodStart, $report->getPeriodStart());
        $this->assertEquals($periodEnd, $report->getPeriodEnd());
        $this->assertEquals(3, $report->getEmployeeCount());
        $this->assertGreaterThan(0, $report->getTotalGrossPay());
        $this->assertGreaterThan(0, $report->getTotalNetPay());
        $this->assertGreaterThan(0, $report->getTotalCommissions());
        $this->assertGreaterThan(0, $report->getTotalBonuses());
        $this->assertCount(3, $report->getPayrollCalculations());
    }

    public function test_can_generate_payslip(): void
    {
        // Arrange
        $employee = $this->createEmployee();
        $periodStart = new DateTimeImmutable('2024-01-01');
        $periodEnd = new DateTimeImmutable('2024-01-31');

        $this->mockCommissionCalculation($employee, $periodStart, 3000.0);
        $this->mockPerformanceBonusCalculation($employee, $periodStart, $periodEnd, 1500.0);

        // Act
        $payslip = $this->service->generatePayslip($employee, $periodStart, $periodEnd);

        // Assert
        $this->assertInstanceOf(Payslip::class, $payslip);
        $this->assertEquals($employee->getId(), $payslip->getEmployee()->getId());
        $this->assertInstanceOf(PayrollCalculationResult::class, $payslip->getCalculation());
        $this->assertEquals('PS-EMP20250001-2024-01', $payslip->getPayslipNumber());
        $this->assertInstanceOf(DateTimeImmutable::class, $payslip->getGeneratedAt());
    }

    public function test_can_calculate_year_to_date_earnings(): void
    {
        // Arrange
        $employee = $this->createEmployee();
        $asOfDate = new DateTimeImmutable('2024-03-31'); // End of Q1

        // Mock calculations for each month
        for ($month = 1; $month <= 3; $month++) {
            $monthStart = new DateTimeImmutable(sprintf("2024-%02d-01", $month));
            $this->mockCommissionCalculation($employee, $monthStart, 2000.0);
            $this->mockPerformanceBonusCalculation($employee, $monthStart, $monthStart->modify('last day of this month'), 1000.0);
        }

        // Act
        $ytdSummary = $this->service->calculateYearToDateEarnings($employee, $asOfDate);

        // Assert
        $this->assertInstanceOf(YearToDateSummary::class, $ytdSummary);
        $this->assertEquals($employee->getId(), $ytdSummary->getEmployee()->getId());
        $this->assertEquals(new DateTimeImmutable('2024-01-01'), $ytdSummary->getYearStart());
        $this->assertEquals($asOfDate, $ytdSummary->getAsOfDate());
        $this->assertGreaterThan(0, $ytdSummary->getTotalGrossPay());
        $this->assertGreaterThan(0, $ytdSummary->getTotalNetPay());
        $this->assertEquals(6000.0, $ytdSummary->getTotalCommissions()); // 3 months * K2,000
        $this->assertEquals(3000.0, $ytdSummary->getTotalBonuses()); // 3 months * K1,000
        $this->assertCount(3, $ytdSummary->getMonthlyBreakdown());
    }

    public function test_excludes_inactive_employees_from_payroll_report(): void
    {
        // Arrange
        $employees = [
            $this->createEmployee('Active', 'Employee'),
            $this->createInactiveEmployee('Inactive', 'Employee')
        ];
        $periodStart = new DateTimeImmutable('2024-01-01');
        $periodEnd = new DateTimeImmutable('2024-01-31');

        // Mock calculations only for active employee
        $this->mockCommissionCalculation($employees[0], $periodStart, 2000.0);
        $this->mockPerformanceBonusCalculation($employees[0], $periodStart, $periodEnd, 1000.0);

        // Act
        $report = $this->service->generatePayrollReport($employees, $periodStart, $periodEnd);

        // Assert
        $this->assertEquals(1, $report->getEmployeeCount()); // Only active employee
        $this->assertCount(1, $report->getPayrollCalculations());
    }

    public function test_calculates_zero_commission_for_non_eligible_employee(): void
    {
        // Arrange
        $nonCommissionEmployee = $this->createNonCommissionEmployee();
        $periodStart = new DateTimeImmutable('2024-01-01');
        $periodEnd = new DateTimeImmutable('2024-01-31');

        $this->mockPerformanceBonusCalculation($nonCommissionEmployee, $periodStart, $periodEnd, 500.0);

        // Act
        $result = $this->service->calculateTotalCompensation($nonCommissionEmployee, $periodStart, $periodEnd);

        // Assert
        $this->assertEquals(0.0, $result->getCommissions());
    }

    private function createEmployee(string $firstName = 'John', string $lastName = 'Doe'): Employee
    {
        $department = new Department(
            DepartmentId::generate(),
            'IT Department',
            'Test department',
            null, // parent department
            true  // isActive
        );

        $position = new Position(
            PositionId::generate(),
            'Software Developer',
            'Test position',
            $department,
            Salary::fromKwacha(40000),
            Salary::fromKwacha(80000),
            true, // Commission eligible
            5.0
        );

        return Employee::create(
            'EMP20250001',
            $firstName,
            $lastName,
            Email::fromString(strtolower($firstName . '.' . $lastName . '@example.com')),
            new DateTimeImmutable('2024-01-15'),
            $department,
            $position,
            Salary::fromKwacha(50000)
        );
    }

    private function createFieldAgent(string $firstName = 'Field', string $lastName = 'Agent'): Employee
    {
        $department = new Department(
            DepartmentId::generate(),
            'Sales',
            'Sales department',
            null, // parent department
            true  // isActive
        );

        $position = new Position(
            PositionId::generate(),
            'Field Agent',
            'Field agent position',
            $department,
            Salary::fromKwacha(30000),
            Salary::fromKwacha(60000),
            true, // Commission eligible
            8.0
        );

        return Employee::create(
            'EMP20250002',
            $firstName,
            $lastName,
            Email::fromString(strtolower($firstName . '.' . $lastName . '@example.com')),
            new DateTimeImmutable('2024-01-15'),
            $department,
            $position,
            Salary::fromKwacha(40000)
        );
    }

    private function createManager(string $firstName = 'Manager', string $lastName = 'Boss'): Employee
    {
        $department = new Department(
            DepartmentId::generate(),
            'Management',
            'Management department',
            null, // parent department
            true  // isActive
        );

        $position = new Position(
            PositionId::generate(),
            'Department Manager',
            'Manager position',
            $department,
            Salary::fromKwacha(60000),
            Salary::fromKwacha(120000),
            false, // Not commission eligible
            0.0
        );

        return Employee::create(
            'EMP20250003',
            $firstName,
            $lastName,
            Email::fromString(strtolower($firstName . '.' . $lastName . '@example.com')),
            new DateTimeImmutable('2024-01-15'),
            $department,
            $position,
            Salary::fromKwacha(80000)
        );
    }

    private function createHighSalaryEmployee(): Employee
    {
        $department = new Department(
            DepartmentId::generate(),
            'Executive',
            'Executive department',
            null, // parent department
            true  // isActive
        );

        $position = new Position(
            PositionId::generate(),
            'Executive',
            'Executive position',
            $department,
            Salary::fromKwacha(80000),
            Salary::fromKwacha(200000),
            false,
            0.0
        );

        return Employee::create(
            'EMP20250004',
            'High',
            'Earner',
            Email::fromString('high.earner@example.com'),
            new DateTimeImmutable('2024-01-15'),
            $department,
            $position,
            Salary::fromKwacha(150000)
        );
    }

    private function createInactiveEmployee(string $firstName = 'Inactive', string $lastName = 'Employee'): Employee
    {
        $employee = $this->createEmployee($firstName, $lastName);
        $employee->changeEmploymentStatus(\App\Domain\Employee\ValueObjects\EmploymentStatus::inactive());
        return $employee;
    }

    private function createNonCommissionEmployee(): Employee
    {
        $department = new Department(
            DepartmentId::generate(),
            'Administration',
            'Admin department',
            null, // parent department
            true  // isActive
        );

        $position = new Position(
            PositionId::generate(),
            'Administrator',
            'Admin position',
            $department,
            Salary::fromKwacha(35000),
            Salary::fromKwacha(70000),
            false, // Not commission eligible
            0.0
        );

        return Employee::create(
            'EMP20250005',
            'Admin',
            'User',
            Email::fromString('admin.user@example.com'),
            new DateTimeImmutable('2024-01-15'),
            $department,
            $position,
            Salary::fromKwacha(45000)
        );
    }

    private function mockCommissionCalculation(Employee $employee, DateTimeImmutable $periodStart, float $commissionAmount): void
    {
        $mockSummary = Mockery::mock(MonthlyCommissionSummary::class);
        $mockSummary->shouldReceive('getTotalCommissions')->andReturn($commissionAmount);

        $this->commissionCalculationService
            ->shouldReceive('calculateMonthlyCommissions')
            ->with(Mockery::type(Employee::class), Mockery::type(DateTimeImmutable::class))
            ->andReturn($mockSummary);
    }

    private function mockPerformanceBonusCalculation(Employee $employee, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd, float $bonusAmount): void
    {
        $mockBonus = Mockery::mock(PerformanceBonusResult::class);
        $mockBonus->shouldReceive('getBonusAmount')->andReturn($bonusAmount);

        $this->performanceTrackingService
            ->shouldReceive('calculatePerformanceBonus')
            ->with(Mockery::type(Employee::class), Mockery::type(DateTimeImmutable::class), Mockery::type(DateTimeImmutable::class))
            ->andReturn($mockBonus);
    }
}