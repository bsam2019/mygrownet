<?php

declare(strict_types=1);

namespace App\Domain\Employee\Services;

use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\ValueObjects\Salary;
use App\Domain\Employee\Exceptions\EmployeeException;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use DateTimeImmutable;
use DateInterval;

class PayrollCalculationService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private CommissionCalculationService $commissionCalculationService,
        private PerformanceTrackingService $performanceTrackingService
    ) {}

    public function calculateTotalCompensation(Employee $employee, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): PayrollCalculationResult
    {
        $this->validateEmployee($employee);
        $this->validatePeriod($periodStart, $periodEnd);

        // Calculate base salary for the period
        $baseSalary = $this->calculateBaseSalaryForPeriod($employee, $periodStart, $periodEnd);

        // Calculate commissions for the period
        $commissions = $this->calculateCommissionsForPeriod($employee, $periodStart, $periodEnd);

        // Calculate performance bonuses
        $performanceBonus = $this->calculatePerformanceBonusForPeriod($employee, $periodStart, $periodEnd);

        // Calculate overtime (if applicable)
        $overtime = $this->calculateOvertimeForPeriod($employee, $periodStart, $periodEnd);

        // Calculate allowances
        $allowances = $this->calculateAllowancesForPeriod($employee, $periodStart, $periodEnd);

        // Calculate gross pay
        $grossPay = $baseSalary + $commissions + $performanceBonus + $overtime + $allowances;

        // Calculate deductions
        $deductions = $this->calculateDeductionsForPeriod($employee, $grossPay, $periodStart, $periodEnd);

        // Calculate net pay
        $netPay = $grossPay - $deductions['total'];

        return new PayrollCalculationResult(
            $employee,
            $periodStart,
            $periodEnd,
            $baseSalary,
            $commissions,
            $performanceBonus,
            $overtime,
            $allowances,
            $grossPay,
            $deductions,
            $netPay,
            new DateTimeImmutable()
        );
    }

    public function generatePayrollReport(array $employees, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): PayrollReport
    {
        $payrollCalculations = [];
        $totalGrossPay = 0.0;
        $totalNetPay = 0.0;
        $totalDeductions = 0.0;
        $totalCommissions = 0.0;
        $totalBonuses = 0.0;

        foreach ($employees as $employee) {
            if (!$employee->isActive()) {
                continue; // Skip inactive employees
            }

            $calculation = $this->calculateTotalCompensation($employee, $periodStart, $periodEnd);
            $payrollCalculations[] = $calculation;

            $totalGrossPay += $calculation->getGrossPay();
            $totalNetPay += $calculation->getNetPay();
            $totalDeductions += $calculation->getDeductions()['total'];
            $totalCommissions += $calculation->getCommissions();
            $totalBonuses += $calculation->getPerformanceBonus();
        }

        return new PayrollReport(
            $periodStart,
            $periodEnd,
            $payrollCalculations,
            $totalGrossPay,
            $totalNetPay,
            $totalDeductions,
            $totalCommissions,
            $totalBonuses,
            count($payrollCalculations),
            new DateTimeImmutable()
        );
    }

    public function generatePayslip(Employee $employee, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): Payslip
    {
        $calculation = $this->calculateTotalCompensation($employee, $periodStart, $periodEnd);

        return new Payslip(
            $employee,
            $calculation,
            $this->generatePayslipNumber($employee, $periodStart),
            new DateTimeImmutable()
        );
    }

    public function calculateYearToDateEarnings(Employee $employee, DateTimeImmutable $asOfDate): YearToDateSummary
    {
        $yearStart = new DateTimeImmutable($asOfDate->format('Y') . '-01-01');
        
        $totalGrossPay = 0.0;
        $totalNetPay = 0.0;
        $totalCommissions = 0.0;
        $totalBonuses = 0.0;
        $totalDeductions = 0.0;
        $monthlyBreakdown = [];

        // Calculate month by month
        $currentMonth = $yearStart;
        while ($currentMonth <= $asOfDate) {
            $monthEnd = $currentMonth->modify('last day of this month');
            if ($monthEnd > $asOfDate) {
                $monthEnd = $asOfDate;
            }

            $monthlyCalculation = $this->calculateTotalCompensation($employee, $currentMonth, $monthEnd);
            
            $totalGrossPay += $monthlyCalculation->getGrossPay();
            $totalNetPay += $monthlyCalculation->getNetPay();
            $totalCommissions += $monthlyCalculation->getCommissions();
            $totalBonuses += $monthlyCalculation->getPerformanceBonus();
            $totalDeductions += $monthlyCalculation->getDeductions()['total'];

            $monthlyBreakdown[] = [
                'month' => $currentMonth->format('Y-m'),
                'gross_pay' => $monthlyCalculation->getGrossPay(),
                'net_pay' => $monthlyCalculation->getNetPay(),
                'commissions' => $monthlyCalculation->getCommissions(),
                'bonuses' => $monthlyCalculation->getPerformanceBonus(),
                'deductions' => $monthlyCalculation->getDeductions()['total']
            ];

            $currentMonth = $currentMonth->add(new DateInterval('P1M'));
        }

        return new YearToDateSummary(
            $employee,
            $yearStart,
            $asOfDate,
            $totalGrossPay,
            $totalNetPay,
            $totalCommissions,
            $totalBonuses,
            $totalDeductions,
            $monthlyBreakdown
        );
    }

    public function calculatePayrollTaxes(Employee $employee, float $grossPay, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): array
    {
        // Zambian tax calculation (simplified)
        $monthlyGross = $grossPay;
        $annualGross = $monthlyGross * 12;

        // PAYE calculation (simplified Zambian tax brackets)
        $paye = $this->calculatePAYE($annualGross) / 12;

        // NAPSA (National Pension Scheme Authority) - 5% employee contribution
        $napsa = min($monthlyGross * 0.05, 2500); // Capped at K2,500

        // NHIMA (National Health Insurance Management Authority) - 1% employee contribution
        $nhima = $monthlyGross * 0.01;

        return [
            'paye' => round($paye, 2),
            'napsa' => round($napsa, 2),
            'nhima' => round($nhima, 2),
            'total_taxes' => round($paye + $napsa + $nhima, 2)
        ];
    }

    public function processPayrollForDepartment(string $departmentName, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): DepartmentPayrollSummary
    {
        // Get all active employees in the department
        $employees = $this->employeeRepository->findAll([
            'department_name' => $departmentName,
            'status' => 'active'
        ]);

        $payrollCalculations = [];
        $totalCost = 0.0;
        $totalEmployees = 0;

        foreach ($employees as $employee) {
            $calculation = $this->calculateTotalCompensation($employee, $periodStart, $periodEnd);
            $payrollCalculations[] = $calculation;
            $totalCost += $calculation->getGrossPay();
            $totalEmployees++;
        }

        $averagePay = $totalEmployees > 0 ? $totalCost / $totalEmployees : 0.0;

        return new DepartmentPayrollSummary(
            $departmentName,
            $periodStart,
            $periodEnd,
            $payrollCalculations,
            $totalCost,
            $totalEmployees,
            $averagePay
        );
    }

    private function validateEmployee(Employee $employee): void
    {
        if (!$employee->isActive()) {
            throw EmployeeException::inactiveEmployeePayroll($employee->getId()->toString());
        }
    }

    private function validatePeriod(DateTimeImmutable $start, DateTimeImmutable $end): void
    {
        if ($start >= $end) {
            throw EmployeeException::invalidPayrollPeriod($start->format('Y-m-d'), $end->format('Y-m-d'));
        }
    }

    private function calculateBaseSalaryForPeriod(Employee $employee, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): float
    {
        $baseSalary = $employee->getBaseSalary()->getAmountInKwacha();
        $daysInPeriod = $periodStart->diff($periodEnd)->days + 1;
        $daysInMonth = (int)$periodStart->format('t');

        // Prorate salary based on actual days worked
        return ($baseSalary / $daysInMonth) * $daysInPeriod;
    }

    private function calculateCommissionsForPeriod(Employee $employee, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): float
    {
        if (!$employee->isEligibleForCommission()) {
            return 0.0;
        }

        // Calculate monthly commissions using the commission service
        $monthlyCommissions = $this->commissionCalculationService->calculateMonthlyCommissions($employee, $periodStart);
        
        return $monthlyCommissions->getTotalCommissions();
    }

    private function calculatePerformanceBonusForPeriod(Employee $employee, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): float
    {
        $performanceBonus = $this->performanceTrackingService->calculatePerformanceBonus($employee, $periodStart, $periodEnd);
        
        return $performanceBonus->getBonusAmount();
    }

    private function calculateOvertimeForPeriod(Employee $employee, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): float
    {
        // Simplified overtime calculation
        // In a real implementation, this would track actual overtime hours
        return 0.0;
    }

    private function calculateAllowancesForPeriod(Employee $employee, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): float
    {
        // Calculate various allowances
        $allowances = 0.0;

        // Transport allowance for field agents
        if (str_contains(strtolower($employee->getPosition()->getTitle()), 'field agent')) {
            $allowances += 1000.0; // K1,000 transport allowance
        }

        // Housing allowance for managers
        if (str_contains(strtolower($employee->getPosition()->getTitle()), 'manager')) {
            $allowances += 2000.0; // K2,000 housing allowance
        }

        // Communication allowance for all employees
        $allowances += 300.0; // K300 communication allowance

        return $allowances;
    }

    private function calculateDeductionsForPeriod(Employee $employee, float $grossPay, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): array
    {
        // Calculate taxes
        $taxes = $this->calculatePayrollTaxes($employee, $grossPay, $periodStart, $periodEnd);

        // Other deductions
        $loanDeductions = $this->calculateLoanDeductions($employee);
        $medicalDeductions = $this->calculateMedicalDeductions($employee);
        $otherDeductions = $this->calculateOtherDeductions($employee);

        $totalDeductions = $taxes['total_taxes'] + $loanDeductions + $medicalDeductions + $otherDeductions;

        return [
            'taxes' => $taxes,
            'loan_deductions' => $loanDeductions,
            'medical_deductions' => $medicalDeductions,
            'other_deductions' => $otherDeductions,
            'total' => $totalDeductions
        ];
    }

    private function calculatePAYE(float $annualGross): float
    {
        // Simplified Zambian PAYE calculation
        $taxableIncome = max(0, $annualGross - 48000); // K48,000 annual tax-free threshold

        $paye = 0;

        // Tax brackets (simplified)
        if ($taxableIncome > 0) {
            if ($taxableIncome <= 57600) {
                $paye = $taxableIncome * 0.20; // 20% on first bracket
            } elseif ($taxableIncome <= 86400) {
                $paye = 57600 * 0.20 + ($taxableIncome - 57600) * 0.30; // 30% on second bracket
            } else {
                $paye = 57600 * 0.20 + 28800 * 0.30 + ($taxableIncome - 86400) * 0.375; // 37.5% on highest bracket
            }
        }

        return $paye;
    }

    private function calculateLoanDeductions(Employee $employee): float
    {
        // In a real implementation, this would check for active loans
        return 0.0;
    }

    private function calculateMedicalDeductions(Employee $employee): float
    {
        // Medical insurance deduction
        return 200.0; // K200 monthly medical insurance
    }

    private function calculateOtherDeductions(Employee $employee): float
    {
        // Other miscellaneous deductions
        return 0.0;
    }

    private function generatePayslipNumber(Employee $employee, DateTimeImmutable $period): string
    {
        return sprintf(
            'PS-%s-%s',
            $employee->getEmployeeNumber(),
            $period->format('Y-m')
        );
    }
}

// Data Transfer Objects for payroll calculations

class PayrollCalculationResult
{
    public function __construct(
        private Employee $employee,
        private DateTimeImmutable $periodStart,
        private DateTimeImmutable $periodEnd,
        private float $baseSalary,
        private float $commissions,
        private float $performanceBonus,
        private float $overtime,
        private float $allowances,
        private float $grossPay,
        private array $deductions,
        private float $netPay,
        private DateTimeImmutable $calculatedAt
    ) {}

    public function getEmployee(): Employee { return $this->employee; }
    public function getPeriodStart(): DateTimeImmutable { return $this->periodStart; }
    public function getPeriodEnd(): DateTimeImmutable { return $this->periodEnd; }
    public function getBaseSalary(): float { return $this->baseSalary; }
    public function getCommissions(): float { return $this->commissions; }
    public function getPerformanceBonus(): float { return $this->performanceBonus; }
    public function getOvertime(): float { return $this->overtime; }
    public function getAllowances(): float { return $this->allowances; }
    public function getGrossPay(): float { return $this->grossPay; }
    public function getDeductions(): array { return $this->deductions; }
    public function getNetPay(): float { return $this->netPay; }
    public function getCalculatedAt(): DateTimeImmutable { return $this->calculatedAt; }
}

class PayrollReport
{
    public function __construct(
        private DateTimeImmutable $periodStart,
        private DateTimeImmutable $periodEnd,
        private array $payrollCalculations,
        private float $totalGrossPay,
        private float $totalNetPay,
        private float $totalDeductions,
        private float $totalCommissions,
        private float $totalBonuses,
        private int $employeeCount,
        private DateTimeImmutable $generatedAt
    ) {}

    public function getPeriodStart(): DateTimeImmutable { return $this->periodStart; }
    public function getPeriodEnd(): DateTimeImmutable { return $this->periodEnd; }
    public function getPayrollCalculations(): array { return $this->payrollCalculations; }
    public function getTotalGrossPay(): float { return $this->totalGrossPay; }
    public function getTotalNetPay(): float { return $this->totalNetPay; }
    public function getTotalDeductions(): float { return $this->totalDeductions; }
    public function getTotalCommissions(): float { return $this->totalCommissions; }
    public function getTotalBonuses(): float { return $this->totalBonuses; }
    public function getEmployeeCount(): int { return $this->employeeCount; }
    public function getGeneratedAt(): DateTimeImmutable { return $this->generatedAt; }
}

class Payslip
{
    public function __construct(
        private Employee $employee,
        private PayrollCalculationResult $calculation,
        private string $payslipNumber,
        private DateTimeImmutable $generatedAt
    ) {}

    public function getEmployee(): Employee { return $this->employee; }
    public function getCalculation(): PayrollCalculationResult { return $this->calculation; }
    public function getPayslipNumber(): string { return $this->payslipNumber; }
    public function getGeneratedAt(): DateTimeImmutable { return $this->generatedAt; }
}

class YearToDateSummary
{
    public function __construct(
        private Employee $employee,
        private DateTimeImmutable $yearStart,
        private DateTimeImmutable $asOfDate,
        private float $totalGrossPay,
        private float $totalNetPay,
        private float $totalCommissions,
        private float $totalBonuses,
        private float $totalDeductions,
        private array $monthlyBreakdown
    ) {}

    public function getEmployee(): Employee { return $this->employee; }
    public function getYearStart(): DateTimeImmutable { return $this->yearStart; }
    public function getAsOfDate(): DateTimeImmutable { return $this->asOfDate; }
    public function getTotalGrossPay(): float { return $this->totalGrossPay; }
    public function getTotalNetPay(): float { return $this->totalNetPay; }
    public function getTotalCommissions(): float { return $this->totalCommissions; }
    public function getTotalBonuses(): float { return $this->totalBonuses; }
    public function getTotalDeductions(): float { return $this->totalDeductions; }
    public function getMonthlyBreakdown(): array { return $this->monthlyBreakdown; }
}

class DepartmentPayrollSummary
{
    public function __construct(
        private string $departmentName,
        private DateTimeImmutable $periodStart,
        private DateTimeImmutable $periodEnd,
        private array $payrollCalculations,
        private float $totalCost,
        private int $totalEmployees,
        private float $averagePay
    ) {}

    public function getDepartmentName(): string { return $this->departmentName; }
    public function getPeriodStart(): DateTimeImmutable { return $this->periodStart; }
    public function getPeriodEnd(): DateTimeImmutable { return $this->periodEnd; }
    public function getPayrollCalculations(): array { return $this->payrollCalculations; }
    public function getTotalCost(): float { return $this->totalCost; }
    public function getTotalEmployees(): int { return $this->totalEmployees; }
    public function getAveragePay(): float { return $this->averagePay; }
}