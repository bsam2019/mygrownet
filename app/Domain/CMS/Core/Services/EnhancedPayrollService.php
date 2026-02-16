<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PayrollItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PayrollItemDetailModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerAllowanceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerDeductionModel;
use App\Infrastructure\Persistence\Eloquent\CMS\AttendanceRecordModel;
use App\Infrastructure\Persistence\Eloquent\CMS\OvertimeRecordModel;
use Carbon\Carbon;

class EnhancedPayrollService
{
    public function __construct(
        private ZambianTaxCalculator $taxCalculator
    ) {}

    /**
     * Calculate comprehensive payroll for a worker
     */
    public function calculateWorkerPayroll(
        int $workerId,
        int $payrollRunId,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        $worker = WorkerModel::with([
            'allowances.allowanceType',
            'deductions.deductionType'
        ])->findOrFail($workerId);

        // 1. Calculate basic salary (prorated if needed)
        $basicSalary = $this->calculateBasicSalary($worker, $periodStart, $periodEnd);

        // 2. Calculate allowances
        $allowances = $this->calculateAllowances($worker, $periodStart, $periodEnd);

        // 3. Calculate overtime
        $overtime = $this->calculateOvertimePay($workerId, $periodStart, $periodEnd);

        // 4. Calculate gross pay
        $grossPay = $basicSalary + $allowances['total'] + $overtime['total'];

        // 5. Calculate statutory deductions
        $statutory = $this->taxCalculator->calculateAllStatutoryDeductions($grossPay);

        // 6. Calculate other deductions
        $otherDeductions = $this->calculateDeductions($worker, $periodStart, $periodEnd);

        // 7. Calculate net pay
        $totalDeductions = $statutory['total_employee_deductions'] + $otherDeductions['total'];
        $netPay = $grossPay - $totalDeductions;

        // 8. Get attendance data
        $attendance = $this->getAttendanceData($workerId, $periodStart, $periodEnd);

        return [
            'worker_id' => $workerId,
            'payroll_run_id' => $payrollRunId,
            'basic_salary' => $basicSalary,
            'total_allowances' => $allowances['total'],
            'overtime_pay' => $overtime['total'],
            'bonus' => 0, // Can be added later
            'commission' => 0, // Can be added later
            'gross_pay' => $grossPay,
            'napsa_employee' => $statutory['napsa_employee'],
            'napsa_employer' => $statutory['napsa_employer'],
            'nhima' => $statutory['nhima'],
            'paye' => $statutory['paye'],
            'total_statutory_deductions' => $statutory['total_employee_deductions'],
            'total_other_deductions' => $otherDeductions['total'],
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'working_days' => $attendance['working_days'],
            'days_worked' => $attendance['days_worked'],
            'days_absent' => $attendance['days_absent'],
            'days_on_leave' => $attendance['days_on_leave'],
            'payment_status' => 'pending',
            'allowance_details' => $allowances['details'],
            'deduction_details' => $otherDeductions['details'],
            'overtime_details' => $overtime['details'],
        ];
    }

    private function calculateBasicSalary(WorkerModel $worker, Carbon $periodStart, Carbon $periodEnd): float
    {
        $monthlySalary = $worker->monthly_salary ?? 0;
        
        // For monthly payroll, return full salary
        // For partial months, prorate based on days
        $daysInMonth = $periodStart->daysInMonth;
        $daysInPeriod = $periodStart->diffInDays($periodEnd) + 1;
        
        if ($daysInPeriod >= $daysInMonth) {
            return $monthlySalary;
        }
        
        return round(($monthlySalary / $daysInMonth) * $daysInPeriod, 2);
    }

    private function calculateAllowances(WorkerModel $worker, Carbon $periodStart, Carbon $periodEnd): array
    {
        $total = 0;
        $details = [];

        foreach ($worker->allowances as $allowance) {
            if (!$allowance->is_active) {
                continue;
            }

            // Check if allowance is effective in this period
            if ($allowance->effective_from > $periodEnd || 
                ($allowance->effective_to && $allowance->effective_to < $periodStart)) {
                continue;
            }

            $amount = $allowance->amount;
            $total += $amount;

            $details[] = [
                'name' => $allowance->allowanceType->allowance_name,
                'amount' => $amount,
                'is_taxable' => $allowance->allowanceType->is_taxable,
            ];
        }

        return [
            'total' => $total,
            'details' => $details,
        ];
    }

    private function calculateOvertimePay(int $workerId, Carbon $periodStart, Carbon $periodEnd): array
    {
        $overtimeRecords = OvertimeRecordModel::where('worker_id', $workerId)
            ->where('overtime_date', '>=', $periodStart)
            ->where('overtime_date', '<=', $periodEnd)
            ->where('status', 'approved')
            ->get();

        $total = 0;
        $details = [];

        foreach ($overtimeRecords as $record) {
            $total += $record->overtime_pay;
            $details[] = [
                'date' => $record->overtime_date->format('Y-m-d'),
                'hours' => $record->overtime_hours,
                'rate' => $record->overtime_rate,
                'amount' => $record->overtime_pay,
                'type' => $record->overtime_type,
            ];
        }

        return [
            'total' => $total,
            'details' => $details,
        ];
    }

    private function calculateDeductions(WorkerModel $worker, Carbon $periodStart, Carbon $periodEnd): array
    {
        $total = 0;
        $details = [];

        foreach ($worker->deductions as $deduction) {
            if (!$deduction->is_active) {
                continue;
            }

            // Check if deduction is effective in this period
            if ($deduction->effective_from > $periodEnd || 
                ($deduction->effective_to && $deduction->effective_to < $periodStart)) {
                continue;
            }

            $amount = $deduction->amount;
            $total += $amount;

            $details[] = [
                'name' => $deduction->deductionType->deduction_name,
                'amount' => $amount,
            ];
        }

        return [
            'total' => $total,
            'details' => $details,
        ];
    }

    private function getAttendanceData(int $workerId, Carbon $periodStart, Carbon $periodEnd): array
    {
        $attendanceRecords = AttendanceRecordModel::where('worker_id', $workerId)
            ->whereBetween('attendance_date', [$periodStart, $periodEnd])
            ->get();

        $workingDays = $periodStart->diffInDaysFiltered(function (Carbon $date) {
            return !$date->isWeekend();
        }, $periodEnd) + 1;

        $daysWorked = $attendanceRecords->whereIn('status', ['present', 'late'])->count();
        $daysAbsent = $attendanceRecords->where('status', 'absent')->count();
        $daysOnLeave = $attendanceRecords->where('status', 'on_leave')->count();

        return [
            'working_days' => $workingDays,
            'days_worked' => $daysWorked,
            'days_absent' => $daysAbsent,
            'days_on_leave' => $daysOnLeave,
        ];
    }

    /**
     * Save payroll item with all details
     */
    public function savePayrollItem(array $payrollData): PayrollItemModel
    {
        // Create main payroll item
        $payrollItem = PayrollItemModel::create([
            'payroll_run_id' => $payrollData['payroll_run_id'],
            'worker_id' => $payrollData['worker_id'],
            'basic_salary' => $payrollData['basic_salary'],
            'total_allowances' => $payrollData['total_allowances'],
            'overtime_pay' => $payrollData['overtime_pay'],
            'bonus' => $payrollData['bonus'],
            'commission' => $payrollData['commission'],
            'gross_pay' => $payrollData['gross_pay'],
            'napsa_employee' => $payrollData['napsa_employee'],
            'napsa_employer' => $payrollData['napsa_employer'],
            'nhima' => $payrollData['nhima'],
            'paye' => $payrollData['paye'],
            'total_statutory_deductions' => $payrollData['total_statutory_deductions'],
            'total_other_deductions' => $payrollData['total_other_deductions'],
            'total_deductions' => $payrollData['total_deductions'],
            'net_pay' => $payrollData['net_pay'],
            'working_days' => $payrollData['working_days'],
            'days_worked' => $payrollData['days_worked'],
            'days_absent' => $payrollData['days_absent'],
            'days_on_leave' => $payrollData['days_on_leave'],
            'payment_status' => $payrollData['payment_status'],
        ]);

        // Save allowance details
        foreach ($payrollData['allowance_details'] as $allowance) {
            PayrollItemDetailModel::create([
                'payroll_item_id' => $payrollItem->id,
                'item_type' => 'allowance',
                'item_name' => $allowance['name'],
                'amount' => $allowance['amount'],
            ]);
        }

        // Save deduction details
        foreach ($payrollData['deduction_details'] as $deduction) {
            PayrollItemDetailModel::create([
                'payroll_item_id' => $payrollItem->id,
                'item_type' => 'deduction',
                'item_name' => $deduction['name'],
                'amount' => $deduction['amount'],
            ]);
        }

        // Save overtime details
        foreach ($payrollData['overtime_details'] as $overtime) {
            PayrollItemDetailModel::create([
                'payroll_item_id' => $payrollItem->id,
                'item_type' => 'overtime',
                'item_name' => "Overtime - {$overtime['date']}",
                'amount' => $overtime['amount'],
            ]);
        }

        return $payrollItem->load('details');
    }
}
