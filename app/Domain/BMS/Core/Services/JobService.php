<?php

namespace App\Domain\CMS\Core\Services;

use App\Domain\CMS\Core\Events\JobCreated;
use App\Domain\CMS\Core\Events\JobCompleted;
use App\Domain\CMS\Core\Events\JobAssigned;
use App\Domain\CMS\Core\ValueObjects\JobNumber;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobStatusHistoryModel;
use App\Notifications\CMS\JobStatusChangedNotification;
use Illuminate\Support\Facades\DB;

class JobService
{
    public function __construct(
        private AuditTrailService $auditTrail
    ) {}

    private function recordStatusChange(
        JobModel $job,
        ?string $oldStatus,
        string $newStatus,
        int $changedBy,
        ?string $notes = null
    ): void {
        JobStatusHistoryModel::create([
            'company_id' => $job->company_id,
            'job_id' => $job->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy,
            'notes' => $notes,
        ]);

        // Send notification to customer if status changed (not initial creation)
        if ($oldStatus !== null) {
            $customer = $job->customer;
            if ($customer && $customer->user) {
                $customer->user->notify(new JobStatusChangedNotification(
                    job: [
                        'id' => $job->id,
                        'job_number' => $job->job_number,
                        'job_type' => $job->job_type,
                        'customer_name' => $customer->name,
                    ],
                    oldStatus: $oldStatus,
                    newStatus: $newStatus
                ));
            }
        }
    }

    public function createJob(array $data): JobModel
    {
        return DB::transaction(function () use ($data) {
            // Generate job number
            $year = date('Y');
            $lastJob = JobModel::where('company_id', $data['company_id'])
                ->where('job_number', 'like', "JOB-{$year}-%")
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastJob ? (int) substr($lastJob->job_number, -4) + 1 : 1;
            $jobNumber = JobNumber::generate($year, $sequence);

            $job = JobModel::create([
                'company_id' => $data['company_id'],
                'customer_id' => $data['customer_id'],
                'job_number' => $jobNumber->value(),
                'job_type' => $data['job_type'],
                'description' => $data['description'] ?? null,
                'quoted_value' => $data['quoted_value'] ?? null,
                'priority' => $data['priority'] ?? 'normal',
                'deadline' => $data['deadline'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'],
            ]);

            // Record initial status
            $this->recordStatusChange(
                job: $job,
                oldStatus: null,
                newStatus: 'pending',
                changedBy: $data['created_by'],
                notes: 'Job created'
            );

            // Log audit trail
            $this->auditTrail->log(
                companyId: $job->company_id,
                userId: $data['created_by'],
                entityType: 'job',
                entityId: $job->id,
                action: 'created',
                newValues: $job->toArray()
            );

            // Dispatch event
            event(new JobCreated(
                jobId: $job->id,
                companyId: $job->company_id,
                customerId: $job->customer_id,
                jobNumber: $job->job_number,
                jobType: $job->job_type,
                quotedValue: $job->quoted_value,
                createdBy: $job->created_by
            ));

            return $job;
        });
    }

    public function assignJob(JobModel $job, int $assignedTo, int $assignedBy): JobModel
    {
        if ($job->isLocked()) {
            throw new \DomainException('Cannot assign a locked job');
        }

        $oldValues = $job->toArray();
        $oldStatus = $job->status;

        $job->update([
            'assigned_to' => $assignedTo,
            'status' => 'in_progress',
            'started_at' => $job->started_at ?? now(),
        ]);

        // Record status change
        $this->recordStatusChange(
            job: $job,
            oldStatus: $oldStatus,
            newStatus: 'in_progress',
            changedBy: $assignedBy,
            notes: 'Job assigned and started'
        );

        // Log audit trail
        $this->auditTrail->log(
            companyId: $job->company_id,
            userId: $assignedBy,
            entityType: 'job',
            entityId: $job->id,
            action: 'updated',
            oldValues: $oldValues,
            newValues: $job->fresh()->toArray()
        );

        // Dispatch event
        event(new JobAssigned(
            jobId: $job->id,
            companyId: $job->company_id,
            assignedTo: $assignedTo,
            assignedBy: $assignedBy
        ));

        return $job->fresh();
    }

    public function completeJob(JobModel $job, array $data, int $completedBy): JobModel
    {
        if ($job->isLocked()) {
            throw new \DomainException('Cannot complete a locked job');
        }

        if (!$job->isInProgress()) {
            throw new \DomainException('Only jobs in progress can be completed');
        }

        return DB::transaction(function () use ($job, $data, $completedBy) {
            $oldValues = $job->toArray();
            $oldStatus = $job->status;

            $job->update([
                'actual_value' => $data['actual_value'],
                'material_cost' => $data['material_cost'] ?? 0,
                'labor_cost' => $data['labor_cost'] ?? 0,
                'overhead_cost' => $data['overhead_cost'] ?? 0,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Calculate profit
            $job->calculateProfit();
            $job->save();

            // Record status change
            $this->recordStatusChange(
                job: $job,
                oldStatus: $oldStatus,
                newStatus: 'completed',
                changedBy: $completedBy,
                notes: 'Job completed with final costs'
            );

            // Log audit trail
            $this->auditTrail->log(
                companyId: $job->company_id,
                userId: $completedBy,
                entityType: 'job',
                entityId: $job->id,
                action: 'updated',
                oldValues: $oldValues,
                newValues: $job->fresh()->toArray()
            );

            // Dispatch event (triggers invoice generation, commission calculation, etc.)
            event(new JobCompleted(
                jobId: $job->id,
                companyId: $job->company_id,
                customerId: $job->customer_id,
                jobNumber: $job->job_number,
                actualValue: $job->actual_value,
                totalCost: $job->total_cost,
                profitAmount: $job->profit_amount,
                completedBy: $completedBy
            ));

            return $job->fresh();
        });
    }

    public function lockJob(JobModel $job, int $lockedBy): JobModel
    {
        if (!$job->isCompleted()) {
            throw new \DomainException('Only completed jobs can be locked');
        }

        $job->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $lockedBy,
        ]);

        $this->auditTrail->log(
            companyId: $job->company_id,
            userId: $lockedBy,
            entityType: 'job',
            entityId: $job->id,
            action: 'locked',
            newValues: ['is_locked' => true]
        );

        return $job->fresh();
    }

    public function unlockJob(JobModel $job, int $unlockedBy): JobModel
    {
        $job->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
        ]);

        $this->auditTrail->log(
            companyId: $job->company_id,
            userId: $unlockedBy,
            entityType: 'job',
            entityId: $job->id,
            action: 'unlocked',
            newValues: ['is_locked' => false]
        );

        return $job->fresh();
    }

    public function updateJobStatus(
        JobModel $job,
        string $newStatus,
        int $changedBy,
        ?string $notes = null
    ): JobModel {
        if ($job->isLocked()) {
            throw new \DomainException('Cannot update status of a locked job');
        }

        $oldStatus = $job->status;

        if ($oldStatus === $newStatus) {
            return $job;
        }

        $job->update(['status' => $newStatus]);

        // Record status change
        $this->recordStatusChange(
            job: $job,
            oldStatus: $oldStatus,
            newStatus: $newStatus,
            changedBy: $changedBy,
            notes: $notes
        );

        // Log audit trail
        $this->auditTrail->log(
            companyId: $job->company_id,
            userId: $changedBy,
            entityType: 'job',
            entityId: $job->id,
            action: 'status_changed',
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $newStatus]
        );

        return $job->fresh();
    }
}
