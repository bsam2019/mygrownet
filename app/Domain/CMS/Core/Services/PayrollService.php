<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerAttendanceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CommissionModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PayrollRunModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PayrollItemModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollService
{
    public function __construct(
        private AuditTrailService $auditTrail
    ) {}

    public function createWorker(array $data): WorkerModel
    {
        // Generate worker number
        if (empty($data['worker_number'])) {
            $lastWorker = WorkerModel::where('company_id', $data['company_id'])
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastWorker ? (int) substr($lastWorker->worker_number, 4) + 1 : 1;
            $data['worker_number'] = 'WKR-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        }

        $worker = WorkerModel::create($data);

        $this->auditTrail->log(
            companyId: $worker->company_id,
            userId: $data['created_by'],
            entityType: 'worker',
            entityId: $worker->id,
            action: 'created',
            newValues: $worker->toArray()
        );

        return $worker;
    }

    public function recordAttendance(array $data): WorkerAttendanceModel
    {
        $worker = WorkerModel::findOrFail($data['worker_id']);

        // Calculate amount earned
        $amountEarned = 0;
        if (!empty($data['hours_worked'])) {
            $amountEarned = $data['hours_worked'] * $worker->hourly_rate;
        } elseif (!empty($data['days_worked'])) {
            $amountEarned = $data['days_worked'] * $worker->daily_rate;
        }

        $attendance = WorkerAttendanceModel::create([
            'company_id' => $worker->company_id,
            'worker_id' => $worker->id,
            'job_id' => $data['job_id'] ?? null,
            'work_date' => $data['work_date'],
            'hours_worked' => $data['hours_worked'] ?? 0,
            'days_worked' => $data['days_worked'] ?? 0,
            'amount_earned' => $amountEarned,
            'work_description' => $data['work_description'] ?? null,
            'status' => 'pending',
            'created_by' => $data['created_by'],
        ]);

        return $attendance;
    }

    public function approveAttendance(WorkerAttendanceModel $attendance, int $userId): WorkerAttendanceModel
    {
        $attendance->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $attendance;
    }

    public function calculateCommission(array $data): CommissionModel
    {
        $commissionAmount = ($data['base_amount'] * $data['commission_rate']) / 100;

        $commission = CommissionModel::create([
            'company_id' => $data['company_id'],
            'worker_id' => $data['worker_id'] ?? null,
            'cms_user_id' => $data['cms_user_id'] ?? null,
            'job_id' => $data['job_id'] ?? null,
            'invoice_id' => $data['invoice_id'] ?? null,
            'commission_type' => $data['commission_type'],
            'base_amount' => $data['base_amount'],
            'commission_rate' => $data['commission_rate'],
            'commission_amount' => $commissionAmount,
            'description' => $data['description'] ?? null,
            'status' => 'pending',
            'created_by' => $data['created_by'],
        ]);

        return $commission;
    }

    public function approveCommission(CommissionModel $commission, int $userId): CommissionModel
    {
        $commission->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $commission;
    }

    public function createPayrollRun(array $data): PayrollRunModel
    {
        return DB::transaction(function () use ($data) {
            // Generate payroll number
            $year = Carbon::parse($data['period_start'])->year;
            $lastPayroll = PayrollRunModel::where('company_id', $data['company_id'])
                ->where('payroll_number', 'like', "PAY-{$year}-%")
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastPayroll ? (int) substr($lastPayroll->payroll_number, -3) + 1 : 1;
            $payrollNumber = "PAY-{$year}-" . str_pad($sequence, 3, '0', STR_PAD_LEFT);

            // Get approved attendance and commissions for the period
            $attendance = WorkerAttendanceModel::where('company_id', $data['company_id'])
                ->where('status', 'approved')
                ->whereBetween('work_date', [$data['period_start'], $data['period_end']])
                ->whereNull('payroll_run_id')
                ->get();

            $commissions = CommissionModel::where('company_id', $data['company_id'])
                ->where('status', 'approved')
                ->whereBetween('created_at', [$data['period_start'], $data['period_end']])
                ->whereNull('payroll_run_id')
                ->get();

            // Calculate totals
            $totalWages = $attendance->sum('amount_earned');
            $totalCommissions = $commissions->sum('commission_amount');
            $totalNetPay = $totalWages + $totalCommissions;

            // Create payroll run
            $payrollRun = PayrollRunModel::create([
                'company_id' => $data['company_id'],
                'payroll_number' => $payrollNumber,
                'period_type' => $data['period_type'] ?? 'weekly',
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
                'total_wages' => $totalWages,
                'total_commissions' => $totalCommissions,
                'total_deductions' => 0,
                'total_net_pay' => $totalNetPay,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'],
            ]);

            // Create payroll items grouped by worker/user
            $this->createPayrollItems($payrollRun, $attendance, $commissions);

            return $payrollRun;
        });
    }

    private function createPayrollItems(PayrollRunModel $payrollRun, $attendance, $commissions): void
    {
        // Group by worker
        $workerGroups = $attendance->groupBy('worker_id');
        foreach ($workerGroups as $workerId => $records) {
            $wages = $records->sum('amount_earned');
            $workerCommissions = $commissions->where('worker_id', $workerId)->sum('commission_amount');

            PayrollItemModel::create([
                'company_id' => $payrollRun->company_id,
                'payroll_run_id' => $payrollRun->id,
                'worker_id' => $workerId,
                'wages' => $wages,
                'commissions' => $workerCommissions,
                'bonuses' => 0,
                'deductions' => 0,
                'net_pay' => $wages + $workerCommissions,
            ]);
        }

        // Group by CMS user
        $userCommissions = $commissions->whereNotNull('cms_user_id')->groupBy('cms_user_id');
        foreach ($userCommissions as $userId => $records) {
            $totalCommissions = $records->sum('commission_amount');

            PayrollItemModel::create([
                'company_id' => $payrollRun->company_id,
                'payroll_run_id' => $payrollRun->id,
                'cms_user_id' => $userId,
                'wages' => 0,
                'commissions' => $totalCommissions,
                'bonuses' => 0,
                'deductions' => 0,
                'net_pay' => $totalCommissions,
            ]);
        }
    }

    public function approvePayrollRun(PayrollRunModel $payrollRun, int $userId): PayrollRunModel
    {
        $payrollRun->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $payrollRun;
    }

    public function markPayrollAsPaid(PayrollRunModel $payrollRun, string $paidDate): PayrollRunModel
    {
        $payrollRun->update([
            'status' => 'paid',
            'paid_date' => $paidDate,
        ]);

        // Mark all related attendance and commissions as paid
        WorkerAttendanceModel::whereIn('id', function ($query) use ($payrollRun) {
            $query->select('id')
                ->from('cms_worker_attendance')
                ->whereBetween('work_date', [$payrollRun->period_start, $payrollRun->period_end])
                ->where('status', 'approved');
        })->update(['status' => 'paid']);

        CommissionModel::whereIn('id', function ($query) use ($payrollRun) {
            $query->select('id')
                ->from('cms_commissions')
                ->whereBetween('created_at', [$payrollRun->period_start, $payrollRun->period_end])
                ->where('status', 'approved');
        })->update(['status' => 'paid']);

        return $payrollRun;
    }
}
