<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\Events;

use App\Domain\Employee\Events\PayrollProcessed;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\Salary;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PayrollProcessedTest extends TestCase
{
    private EmployeeId $employeeId;
    private Salary $baseSalary;
    private DateTimeImmutable $payrollPeriodStart;
    private DateTimeImmutable $payrollPeriodEnd;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->employeeId = EmployeeId::generate();
        $this->baseSalary = Salary::fromKwacha(5000.0);
        $this->payrollPeriodStart = new DateTimeImmutable('2024-01-01');
        $this->payrollPeriodEnd = new DateTimeImmutable('2024-01-31');
    }

    public function test_can_create_payroll_processed_event(): void
    {
        $event = new PayrollProcessed(
            employeeId: $this->employeeId,
            payrollPeriodStart: $this->payrollPeriodStart,
            payrollPeriodEnd: $this->payrollPeriodEnd,
            baseSalary: $this->baseSalary,
            totalCommissions: 1000.0,
            totalBonuses: 500.0,
            totalDeductions: 200.0,
            grossPay: 6500.0,
            netPay: 6300.0,
            commissionBreakdown: [['type' => 'investment', 'amount' => 1000.0]],
            bonusBreakdown: [['type' => 'performance', 'amount' => 500.0]],
            deductionBreakdown: [['type' => 'tax', 'amount' => 200.0]],
            payrollStatus: 'processed',
            paymentReference: 'PAY-2024-001',
            notes: 'Regular monthly payroll'
        );

        $this->assertTrue($this->employeeId->equals($event->employeeId));
        $this->assertEquals($this->payrollPeriodStart, $event->payrollPeriodStart);
        $this->assertEquals($this->payrollPeriodEnd, $event->payrollPeriodEnd);
        $this->assertTrue($this->baseSalary->equals($event->baseSalary));
        $this->assertEquals(1000.0, $event->totalCommissions);
        $this->assertEquals(500.0, $event->totalBonuses);
        $this->assertEquals(200.0, $event->totalDeductions);
        $this->assertEquals(6500.0, $event->grossPay);
        $this->assertEquals(6300.0, $event->netPay);
        $this->assertEquals('processed', $event->payrollStatus);
        $this->assertEquals('PAY-2024-001', $event->paymentReference);
        $this->assertEquals('Regular monthly payroll', $event->notes);
    }

    public function test_to_array_returns_correct_data(): void
    {
        $event = new PayrollProcessed(
            employeeId: $this->employeeId,
            payrollPeriodStart: $this->payrollPeriodStart,
            payrollPeriodEnd: $this->payrollPeriodEnd,
            baseSalary: $this->baseSalary,
            totalCommissions: 1000.0,
            totalBonuses: 500.0,
            totalDeductions: 200.0,
            grossPay: 6500.0,
            netPay: 6300.0
        );

        $array = $event->toArray();

        $this->assertEquals($this->employeeId->toString(), $array['employee_id']);
        $this->assertEquals('2024-01-01', $array['payroll_period_start']);
        $this->assertEquals('2024-01-31', $array['payroll_period_end']);
        $this->assertEquals(500000, $array['base_salary']); // 5000 * 100 (ngwee)
        $this->assertEquals(1000.0, $array['total_commissions']);
        $this->assertEquals(500.0, $array['total_bonuses']);
        $this->assertEquals(200.0, $array['total_deductions']);
        $this->assertEquals(6500.0, $array['gross_pay']);
        $this->assertEquals(6300.0, $array['net_pay']);
        $this->assertEquals('processed', $array['payroll_status']);
        $this->assertArrayHasKey('occurred_at', $array);
    }  
  public function test_get_event_name_returns_correct_name(): void
    {
        $event = new PayrollProcessed(
            employeeId: $this->employeeId,
            payrollPeriodStart: $this->payrollPeriodStart,
            payrollPeriodEnd: $this->payrollPeriodEnd,
            baseSalary: $this->baseSalary,
            totalCommissions: 1000.0,
            totalBonuses: 500.0,
            totalDeductions: 200.0,
            grossPay: 6500.0,
            netPay: 6300.0
        );

        $this->assertEquals('employee.payroll_processed', $event->getEventName());
    }

    public function test_status_checks(): void
    {
        $processedEvent = new PayrollProcessed(
            employeeId: $this->employeeId,
            payrollPeriodStart: $this->payrollPeriodStart,
            payrollPeriodEnd: $this->payrollPeriodEnd,
            baseSalary: $this->baseSalary,
            totalCommissions: 1000.0,
            totalBonuses: 500.0,
            totalDeductions: 200.0,
            grossPay: 6500.0,
            netPay: 6300.0,
            payrollStatus: 'processed'
        );

        $this->assertFalse($processedEvent->isPaid());
        $this->assertFalse($processedEvent->isCancelled());

        $paidEvent = new PayrollProcessed(
            employeeId: $this->employeeId,
            payrollPeriodStart: $this->payrollPeriodStart,
            payrollPeriodEnd: $this->payrollPeriodEnd,
            baseSalary: $this->baseSalary,
            totalCommissions: 1000.0,
            totalBonuses: 500.0,
            totalDeductions: 200.0,
            grossPay: 6500.0,
            netPay: 6300.0,
            payrollStatus: 'paid'
        );

        $this->assertTrue($paidEvent->isPaid());
        $this->assertFalse($paidEvent->isCancelled());
    }

    public function test_component_checks(): void
    {
        $event = new PayrollProcessed(
            employeeId: $this->employeeId,
            payrollPeriodStart: $this->payrollPeriodStart,
            payrollPeriodEnd: $this->payrollPeriodEnd,
            baseSalary: $this->baseSalary,
            totalCommissions: 1000.0,
            totalBonuses: 500.0,
            totalDeductions: 200.0,
            grossPay: 6500.0,
            netPay: 6300.0
        );

        $this->assertTrue($event->hasCommissions());
        $this->assertTrue($event->hasBonuses());
        $this->assertTrue($event->hasDeductions());

        $basicEvent = new PayrollProcessed(
            employeeId: $this->employeeId,
            payrollPeriodStart: $this->payrollPeriodStart,
            payrollPeriodEnd: $this->payrollPeriodEnd,
            baseSalary: $this->baseSalary,
            totalCommissions: 0.0,
            totalBonuses: 0.0,
            totalDeductions: 0.0,
            grossPay: 5000.0,
            netPay: 5000.0
        );

        $this->assertFalse($basicEvent->hasCommissions());
        $this->assertFalse($basicEvent->hasBonuses());
        $this->assertFalse($basicEvent->hasDeductions());
    }

    public function test_get_commission_percentage(): void
    {
        $event = new PayrollProcessed(
            employeeId: $this->employeeId,
            payrollPeriodStart: $this->payrollPeriodStart,
            payrollPeriodEnd: $this->payrollPeriodEnd,
            baseSalary: $this->baseSalary,
            totalCommissions: 1000.0,
            totalBonuses: 500.0,
            totalDeductions: 200.0,
            grossPay: 6500.0, // 5000 + 1000 + 500
            netPay: 6300.0
        );

        $expectedPercentage = (1000.0 / 6500.0) * 100; // ~15.38%
        $this->assertEquals($expectedPercentage, $event->getCommissionPercentage());
    }

    public function test_is_valid_returns_true_for_valid_event(): void
    {
        $event = new PayrollProcessed(
            employeeId: $this->employeeId,
            payrollPeriodStart: $this->payrollPeriodStart,
            payrollPeriodEnd: $this->payrollPeriodEnd,
            baseSalary: $this->baseSalary,
            totalCommissions: 1000.0,
            totalBonuses: 500.0,
            totalDeductions: 200.0,
            grossPay: 6500.0, // 5000 + 1000 + 500
            netPay: 6300.0 // 6500 - 200
        );

        $this->assertTrue($event->isValid());
    }

    public function test_is_valid_returns_false_for_invalid_payroll_period(): void
    {
        $event = new PayrollProcessed(
            employeeId: $this->employeeId,
            payrollPeriodStart: $this->payrollPeriodEnd, // Start after end
            payrollPeriodEnd: $this->payrollPeriodStart,
            baseSalary: $this->baseSalary,
            totalCommissions: 1000.0,
            totalBonuses: 500.0,
            totalDeductions: 200.0,
            grossPay: 6500.0,
            netPay: 6300.0
        );

        $this->assertFalse($event->isValid());
    }

    public function test_is_valid_returns_false_for_incorrect_gross_pay_calculation(): void
    {
        $event = new PayrollProcessed(
            employeeId: $this->employeeId,
            payrollPeriodStart: $this->payrollPeriodStart,
            payrollPeriodEnd: $this->payrollPeriodEnd,
            baseSalary: $this->baseSalary,
            totalCommissions: 1000.0,
            totalBonuses: 500.0,
            totalDeductions: 200.0,
            grossPay: 7000.0, // Incorrect calculation (should be 6500)
            netPay: 6300.0
        );

        $this->assertFalse($event->isValid());
    }

    public function test_is_valid_returns_false_for_incorrect_net_pay_calculation(): void
    {
        $event = new PayrollProcessed(
            employeeId: $this->employeeId,
            payrollPeriodStart: $this->payrollPeriodStart,
            payrollPeriodEnd: $this->payrollPeriodEnd,
            baseSalary: $this->baseSalary,
            totalCommissions: 1000.0,
            totalBonuses: 500.0,
            totalDeductions: 200.0,
            grossPay: 6500.0,
            netPay: 6000.0 // Incorrect calculation (should be 6300)
        );

        $this->assertFalse($event->isValid());
    }
}