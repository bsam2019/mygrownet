<?php

declare(strict_types=1);

namespace App\Domain\Employee\Events;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\Salary;
use DateTimeImmutable;

/**
 * Domain event fired when an employee's payroll is processed
 */
final readonly class PayrollProcessed
{
    public function __construct(
        public EmployeeId $employeeId,
        public DateTimeImmutable $payrollPeriodStart,
        public DateTimeImmutable $payrollPeriodEnd,
        public Salary $baseSalary,
        public float $totalCommissions,
        public float $totalBonuses,
        public float $totalDeductions,
        public float $grossPay,
        public float $netPay,
        public array $commissionBreakdown = [], // Array of commission details
        public array $bonusBreakdown = [], // Array of bonus details
        public array $deductionBreakdown = [], // Array of deduction details
        public string $payrollStatus = 'processed', // processed, paid, cancelled
        public ?string $paymentReference = null,
        public ?string $notes = null,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {}

    /**
     * Get event data as array for serialization
     */
    public function toArray(): array
    {
        return [
            'employee_id' => $this->employeeId->toString(),
            'payroll_period_start' => $this->payrollPeriodStart->format('Y-m-d'),
            'payroll_period_end' => $this->payrollPeriodEnd->format('Y-m-d'),
            'base_salary' => $this->baseSalary->getAmount(),
            'total_commissions' => $this->totalCommissions,
            'total_bonuses' => $this->totalBonuses,
            'total_deductions' => $this->totalDeductions,
            'gross_pay' => $this->grossPay,
            'net_pay' => $this->netPay,
            'commission_breakdown' => $this->commissionBreakdown,
            'bonus_breakdown' => $this->bonusBreakdown,
            'deduction_breakdown' => $this->deductionBreakdown,
            'payroll_status' => $this->payrollStatus,
            'payment_reference' => $this->paymentReference,
            'notes' => $this->notes,
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Get event name for logging/tracking
     */
    public function getEventName(): string
    {
        return 'employee.payroll_processed';
    }

    /**
     * Check if payroll has been paid
     */
    public function isPaid(): bool
    {
        return $this->payrollStatus === 'paid';
    }

    /**
     * Check if payroll was cancelled
     */
    public function isCancelled(): bool
    {
        return $this->payrollStatus === 'cancelled';
    }

    /**
     * Check if employee has commissions in this payroll
     */
    public function hasCommissions(): bool
    {
        return $this->totalCommissions > 0;
    }

    /**
     * Check if employee has bonuses in this payroll
     */
    public function hasBonuses(): bool
    {
        return $this->totalBonuses > 0;
    }

    /**
     * Check if employee has deductions in this payroll
     */
    public function hasDeductions(): bool
    {
        return $this->totalDeductions > 0;
    }

    /**
     * Get commission percentage of total pay
     */
    public function getCommissionPercentage(): float
    {
        if ($this->grossPay <= 0) {
            return 0.0;
        }
        
        return ($this->totalCommissions / $this->grossPay) * 100;
    }

    /**
     * Validate event data integrity
     */
    public function isValid(): bool
    {
        $validStatuses = ['processed', 'paid', 'cancelled'];
        
        return $this->payrollPeriodStart < $this->payrollPeriodEnd
            && $this->payrollPeriodEnd <= $this->occurredAt
            && in_array($this->payrollStatus, $validStatuses, true)
            && $this->baseSalary->getAmount() >= 0
            && $this->totalCommissions >= 0
            && $this->totalBonuses >= 0
            && $this->totalDeductions >= 0
            && $this->grossPay >= 0
            && $this->netPay >= 0
            && $this->grossPay === ($this->baseSalary->getAmountInKwacha() + $this->totalCommissions + $this->totalBonuses)
            && $this->netPay === ($this->grossPay - $this->totalDeductions);
    }
}