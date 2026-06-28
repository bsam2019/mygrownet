<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\AttendanceRecordModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\OvertimeRecordModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PublicHolidayModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use Carbon\Carbon;

class OvertimeService
{
    public function __construct(
        private AuditTrailService $auditTrail
    ) {}

    /**
     * Create overtime record from attendance
     */
    public function createFromAttendance(AttendanceRecordModel $attendance): ?OvertimeRecordModel
    {
        if (!$attendance->overtime_minutes || $attendance->overtime_minutes <= 0) {
            return null;
        }

        $worker = $attendance->worker;
        $company = CompanyModel::find($attendance->company_id);

        // Determine overtime type
        $overtimeType = $this->determineOvertimeType($attendance->attendance_date, $attendance->company_id);

        // Get overtime rate multiplier
        $multiplier = $this->getOvertimeMultiplier($company, $overtimeType);

        // Calculate amount
        $baseRate = $worker->hourly_rate ?? $worker->monthly_salary / 160; // Assume 160 hours/month
        $overtimeAmount = ($attendance->overtime_minutes / 60) * $baseRate * $multiplier;

        $record = OvertimeRecordModel::create([
            'company_id' => $attendance->company_id,
            'worker_id' => $attendance->worker_id,
            'attendance_record_id' => $attendance->id,
            'overtime_date' => $attendance->attendance_date,
            'overtime_minutes' => $attendance->overtime_minutes,
            'overtime_type' => $overtimeType,
            'overtime_rate_multiplier' => $multiplier,
            'base_hourly_rate' => $baseRate,
            'overtime_amount' => $overtimeAmount,
            'status' => $company->overtime_requires_approval ? 'pending' : 'approved',
            'created_by' => auth()->id(),
        ]);

        $this->auditTrail->log(
            companyId: $record->company_id,
            userId: auth()->id() ?? $attendance->worker_id,
            entityType: 'overtime',
            entityId: $record->id,
            action: 'created',
            newValues: $record->toArray()
        );

        return $record;
    }

    /**
     * Determine overtime type based on date
     */
    private function determineOvertimeType(Carbon $date, int $companyId): string
    {
        // Check if it's a public holiday
        $isHoliday = PublicHolidayModel::where('company_id', $companyId)
            ->where('holiday_date', $date)
            ->exists();

        if ($isHoliday) {
            return 'holiday';
        }

        // Check if it's a weekend
        if ($date->isWeekend()) {
            return 'weekend';
        }

        return 'daily';
    }

    /**
     * Get overtime multiplier based on type
     */
    private function getOvertimeMultiplier(CompanyModel $company, string $overtimeType): float
    {
        return match ($overtimeType) {
            'holiday' => $company->holiday_overtime_multiplier ?? 2.0,
            'weekend' => $company->weekend_overtime_multiplier ?? 1.5,
            'daily' => $company->overtime_rate_multiplier ?? 1.5,
            default => 1.5,
        };
    }

    /**
     * Create manual overtime entry
     */
    public function createManualOvertime(array $data): OvertimeRecordModel
    {
        $worker = WorkerModel::findOrFail($data['worker_id']);
        $company = CompanyModel::find($data['company_id']);

        $overtimeType = $data['overtime_type'] ?? 'manual';
        $multiplier = $data['overtime_rate_multiplier'] ?? $this->getOvertimeMultiplier($company, $overtimeType);

        $baseRate = $data['base_hourly_rate'] ?? $worker->hourly_rate ?? ($worker->monthly_salary / 160);
        $overtimeAmount = ($data['overtime_minutes'] / 60) * $baseRate * $multiplier;

        $record = OvertimeRecordModel::create([
            'company_id' => $data['company_id'],
            'worker_id' => $data['worker_id'],
            'attendance_record_id' => $data['attendance_record_id'] ?? null,
            'overtime_date' => $data['overtime_date'],
            'overtime_minutes' => $data['overtime_minutes'],
            'overtime_type' => $overtimeType,
            'overtime_rate_multiplier' => $multiplier,
            'base_hourly_rate' => $baseRate,
            'overtime_amount' => $overtimeAmount,
            'reason' => $data['reason'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $company->overtime_requires_approval ? 'pending' : 'approved',
            'created_by' => auth()->id(),
        ]);

        $this->auditTrail->log(
            companyId: $record->company_id,
            userId: auth()->id(),
            entityType: 'overtime',
            entityId: $record->id,
            action: 'manual_entry_created',
            newValues: $record->toArray()
        );

        return $record;
    }

    /**
     * Approve overtime
     */
    public function approveOvertime(OvertimeRecordModel $record): OvertimeRecordModel
    {
        if ($record->status !== 'pending') {
            throw new \Exception('Only pending overtime can be approved.');
        }

        $record->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->auditTrail->log(
            companyId: $record->company_id,
            userId: auth()->id(),
            entityType: 'overtime',
            entityId: $record->id,
            action: 'approved'
        );

        return $record;
    }

    /**
     * Reject overtime
     */
    public function rejectOvertime(OvertimeRecordModel $record, string $reason): OvertimeRecordModel
    {
        if ($record->status !== 'pending') {
            throw new \Exception('Only pending overtime can be rejected.');
        }

        $record->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->auditTrail->log(
            companyId: $record->company_id,
            userId: auth()->id(),
            entityType: 'overtime',
            entityId: $record->id,
            action: 'rejected',
            newValues: ['rejection_reason' => $reason]
        );

        return $record;
    }

    /**
     * Calculate weekly overtime
     */
    public function calculateWeeklyOvertime(int $companyId, int $workerId, Carbon $weekStart): ?OvertimeRecordModel
    {
        $company = CompanyModel::find($companyId);
        $weekEnd = $weekStart->copy()->endOfWeek();

        // Get total hours worked in the week
        $totalMinutes = AttendanceRecordModel::where('company_id', $companyId)
            ->where('worker_id', $workerId)
            ->dateRange($weekStart, $weekEnd)
            ->sum('total_minutes');

        $weeklyThreshold = ($company->weekly_overtime_threshold_hours ?? 40) * 60;

        if ($totalMinutes <= $weeklyThreshold) {
            return null;
        }

        $overtimeMinutes = $totalMinutes - $weeklyThreshold;
        $worker = WorkerModel::find($workerId);

        $baseRate = $worker->hourly_rate ?? ($worker->monthly_salary / 160);
        $multiplier = $company->overtime_rate_multiplier ?? 1.5;
        $overtimeAmount = ($overtimeMinutes / 60) * $baseRate * $multiplier;

        $record = OvertimeRecordModel::create([
            'company_id' => $companyId,
            'worker_id' => $workerId,
            'overtime_date' => $weekEnd,
            'overtime_minutes' => $overtimeMinutes,
            'overtime_type' => 'weekly',
            'overtime_rate_multiplier' => $multiplier,
            'base_hourly_rate' => $baseRate,
            'overtime_amount' => $overtimeAmount,
            'reason' => "Weekly overtime for week starting {$weekStart->format('Y-m-d')}",
            'status' => $company->overtime_requires_approval ? 'pending' : 'approved',
            'created_by' => auth()->id(),
        ]);

        return $record;
    }

    /**
     * Get overtime records
     */
    public function getOvertimeRecords(int $companyId, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = OvertimeRecordModel::where('company_id', $companyId)
            ->with(['worker', 'attendanceRecord']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['overtime_type'])) {
            $query->where('overtime_type', $filters['overtime_type']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('overtime_date', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->orderBy('overtime_date', 'desc')->get();
    }

    /**
     * Get overtime summary
     */
    public function getOvertimeSummary(int $companyId, int $workerId, Carbon $startDate, Carbon $endDate): array
    {
        $records = OvertimeRecordModel::where('company_id', $companyId)
            ->where('worker_id', $workerId)
            ->whereBetween('overtime_date', [$startDate, $endDate])
            ->get();

        $approved = $records->where('status', 'approved');
        $pending = $records->where('status', 'pending');

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'total' => [
                'records' => $records->count(),
                'hours' => round($records->sum('overtime_minutes') / 60, 2),
                'amount' => $records->sum('overtime_amount'),
            ],
            'approved' => [
                'records' => $approved->count(),
                'hours' => round($approved->sum('overtime_minutes') / 60, 2),
                'amount' => $approved->sum('overtime_amount'),
            ],
            'pending' => [
                'records' => $pending->count(),
                'hours' => round($pending->sum('overtime_minutes') / 60, 2),
                'amount' => $pending->sum('overtime_amount'),
            ],
            'by_type' => [
                'daily' => round($records->where('overtime_type', 'daily')->sum('overtime_minutes') / 60, 2),
                'weekly' => round($records->where('overtime_type', 'weekly')->sum('overtime_minutes') / 60, 2),
                'holiday' => round($records->where('overtime_type', 'holiday')->sum('overtime_minutes') / 60, 2),
                'weekend' => round($records->where('overtime_type', 'weekend')->sum('overtime_minutes') / 60, 2),
            ],
        ];
    }

    /**
     * Get overtime for payroll
     */
    public function getOvertimeForPayroll(int $companyId, Carbon $startDate, Carbon $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return OvertimeRecordModel::where('company_id', $companyId)
            ->where('status', 'approved')
            ->where('included_in_payroll', false)
            ->whereBetween('overtime_date', [$startDate, $endDate])
            ->with('worker')
            ->get();
    }

    /**
     * Mark overtime as included in payroll
     */
    public function markAsIncludedInPayroll(array $overtimeIds, int $payrollId): int
    {
        return OvertimeRecordModel::whereIn('id', $overtimeIds)
            ->update([
                'included_in_payroll' => true,
                'payroll_id' => $payrollId,
            ]);
    }
}
