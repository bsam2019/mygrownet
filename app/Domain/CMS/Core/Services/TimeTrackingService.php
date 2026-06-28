<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\TimeEntryModel;
use App\Infrastructure\Persistence\Eloquent\CMS\TimesheetModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimeTrackingService
{
    public function __construct(
        private AuditTrailService $auditTrail
    ) {}

    public function startTimer(array $data): TimeEntryModel
    {
        // Check if worker already has a running timer
        $runningTimer = TimeEntryModel::where('company_id', $data['company_id'])
            ->where('worker_id', $data['worker_id'])
            ->where('is_running', true)
            ->first();

        if ($runningTimer) {
            throw new \Exception('Worker already has a running timer. Please stop it first.');
        }

        $worker = WorkerModel::findOrFail($data['worker_id']);

        $entry = TimeEntryModel::create([
            'company_id' => $data['company_id'],
            'worker_id' => $data['worker_id'],
            'job_id' => $data['job_id'] ?? null,
            'created_by' => $data['created_by'],
            'start_time' => now(),
            'is_running' => true,
            'is_billable' => $data['is_billable'] ?? true,
            'hourly_rate' => $data['hourly_rate'] ?? $worker->hourly_rate,
            'description' => $data['description'] ?? null,
        ]);

        $this->auditTrail->log(
            companyId: $entry->company_id,
            userId: $data['created_by'],
            entityType: 'time_entry',
            entityId: $entry->id,
            action: 'timer_started',
            newValues: $entry->toArray()
        );

        return $entry;
    }

    public function stopTimer(TimeEntryModel $entry, int $userId): TimeEntryModel
    {
        if (!$entry->is_running) {
            throw new \Exception('Timer is not running.');
        }

        $endTime = now();
        $durationMinutes = $entry->start_time->diffInMinutes($endTime);
        $totalAmount = 0;

        if ($entry->is_billable && $entry->hourly_rate) {
            $totalAmount = ($durationMinutes / 60) * $entry->hourly_rate;
        }

        $entry->update([
            'end_time' => $endTime,
            'duration_minutes' => $durationMinutes,
            'is_running' => false,
            'total_amount' => $totalAmount,
        ]);

        $this->auditTrail->log(
            companyId: $entry->company_id,
            userId: $userId,
            entityType: 'time_entry',
            entityId: $entry->id,
            action: 'timer_stopped',
            newValues: ['duration_minutes' => $durationMinutes, 'total_amount' => $totalAmount]
        );

        return $entry;
    }

    public function createManualEntry(array $data): TimeEntryModel
    {
        $worker = WorkerModel::findOrFail($data['worker_id']);
        
        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);
        $durationMinutes = $startTime->diffInMinutes($endTime);
        
        $hourlyRate = $data['hourly_rate'] ?? $worker->hourly_rate;
        $totalAmount = 0;

        if (($data['is_billable'] ?? true) && $hourlyRate) {
            $totalAmount = ($durationMinutes / 60) * $hourlyRate;
        }

        $entry = TimeEntryModel::create([
            'company_id' => $data['company_id'],
            'worker_id' => $data['worker_id'],
            'job_id' => $data['job_id'] ?? null,
            'created_by' => $data['created_by'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_minutes' => $durationMinutes,
            'is_running' => false,
            'is_billable' => $data['is_billable'] ?? true,
            'hourly_rate' => $hourlyRate,
            'total_amount' => $totalAmount,
            'description' => $data['description'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'draft',
        ]);

        $this->auditTrail->log(
            companyId: $entry->company_id,
            userId: $data['created_by'],
            entityType: 'time_entry',
            entityId: $entry->id,
            action: 'created',
            newValues: $entry->toArray()
        );

        return $entry;
    }

    public function updateEntry(TimeEntryModel $entry, array $data, int $userId): TimeEntryModel
    {
        if ($entry->is_running) {
            throw new \Exception('Cannot update a running timer. Stop it first.');
        }

        if ($entry->status !== 'draft') {
            throw new \Exception('Can only update draft entries.');
        }

        $oldValues = $entry->toArray();

        // Recalculate if times changed
        if (isset($data['start_time']) || isset($data['end_time'])) {
            $startTime = isset($data['start_time']) ? Carbon::parse($data['start_time']) : $entry->start_time;
            $endTime = isset($data['end_time']) ? Carbon::parse($data['end_time']) : $entry->end_time;
            $data['duration_minutes'] = $startTime->diffInMinutes($endTime);
            
            if ($entry->is_billable && $entry->hourly_rate) {
                $data['total_amount'] = ($data['duration_minutes'] / 60) * $entry->hourly_rate;
            }
        }

        $entry->update($data);

        $this->auditTrail->log(
            companyId: $entry->company_id,
            userId: $userId,
            entityType: 'time_entry',
            entityId: $entry->id,
            action: 'updated',
            oldValues: $oldValues,
            newValues: $entry->toArray()
        );

        return $entry;
    }

    public function submitEntry(TimeEntryModel $entry, int $userId): TimeEntryModel
    {
        if ($entry->is_running) {
            throw new \Exception('Cannot submit a running timer.');
        }

        $entry->update(['status' => 'submitted']);

        $this->auditTrail->log(
            companyId: $entry->company_id,
            userId: $userId,
            entityType: 'time_entry',
            entityId: $entry->id,
            action: 'submitted'
        );

        return $entry;
    }

    public function approveEntry(TimeEntryModel $entry, int $userId): TimeEntryModel
    {
        $entry->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        $this->auditTrail->log(
            companyId: $entry->company_id,
            userId: $userId,
            entityType: 'time_entry',
            entityId: $entry->id,
            action: 'approved'
        );

        return $entry;
    }

    public function rejectEntry(TimeEntryModel $entry, int $userId, string $reason): TimeEntryModel
    {
        $entry->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->auditTrail->log(
            companyId: $entry->company_id,
            userId: $userId,
            entityType: 'time_entry',
            entityId: $entry->id,
            action: 'rejected',
            newValues: ['rejection_reason' => $reason]
        );

        return $entry;
    }

    public function generateTimesheet(int $companyId, int $workerId, string $startDate, string $endDate, string $periodType = 'weekly'): TimesheetModel
    {
        return DB::transaction(function () use ($companyId, $workerId, $startDate, $endDate, $periodType) {
            // Check if timesheet already exists
            $existing = TimesheetModel::where('company_id', $companyId)
                ->where('worker_id', $workerId)
                ->where('start_date', $startDate)
                ->where('end_date', $endDate)
                ->first();

            if ($existing) {
                return $existing;
            }

            // Get approved time entries for the period
            $entries = TimeEntryModel::where('company_id', $companyId)
                ->where('worker_id', $workerId)
                ->where('status', 'approved')
                ->whereBetween('start_time', [$startDate, $endDate])
                ->get();

            $totalMinutes = $entries->sum('duration_minutes');
            $billableMinutes = $entries->where('is_billable', true)->sum('duration_minutes');
            $nonBillableMinutes = $totalMinutes - $billableMinutes;
            $totalAmount = $entries->sum('total_amount');

            $timesheet = TimesheetModel::create([
                'company_id' => $companyId,
                'worker_id' => $workerId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'period_type' => $periodType,
                'total_hours' => round($totalMinutes / 60, 2),
                'billable_hours' => round($billableMinutes / 60, 2),
                'non_billable_hours' => round($nonBillableMinutes / 60, 2),
                'total_amount' => $totalAmount,
                'status' => 'draft',
            ]);

            return $timesheet;
        });
    }

    public function submitTimesheet(TimesheetModel $timesheet, int $userId): TimesheetModel
    {
        $timesheet->update([
            'status' => 'submitted',
            'submitted_by' => $userId,
            'submitted_at' => now(),
        ]);

        return $timesheet;
    }

    public function approveTimesheet(TimesheetModel $timesheet, int $userId): TimesheetModel
    {
        $timesheet->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $timesheet;
    }

    public function rejectTimesheet(TimesheetModel $timesheet, int $userId, string $reason): TimesheetModel
    {
        $timesheet->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $timesheet;
    }

    public function getTimeReport(int $companyId, array $filters = []): array
    {
        $query = TimeEntryModel::where('company_id', $companyId)
            ->where('status', 'approved');

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['job_id'])) {
            $query->where('job_id', $filters['job_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('start_time', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('start_time', '<=', $filters['end_date']);
        }

        $entries = $query->with(['worker', 'job'])->get();

        return [
            'total_entries' => $entries->count(),
            'total_hours' => round($entries->sum('duration_minutes') / 60, 2),
            'billable_hours' => round($entries->where('is_billable', true)->sum('duration_minutes') / 60, 2),
            'non_billable_hours' => round($entries->where('is_billable', false)->sum('duration_minutes') / 60, 2),
            'total_amount' => $entries->sum('total_amount'),
            'entries' => $entries,
        ];
    }
}
