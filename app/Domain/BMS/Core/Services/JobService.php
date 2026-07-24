<?php

namespace App\Domain\BMS\Core\Services;

use App\Domain\BMS\Core\Events\JobCreated;
use App\Domain\BMS\Core\Events\JobCompleted;
use App\Domain\BMS\Core\Events\JobAssigned;
use App\Domain\BMS\Core\ValueObjects\JobNumber;
use App\Domain\BMS\Entities\Job;
use App\Domain\BMS\Repositories\JobRepositoryInterface;
use Illuminate\Support\Facades\DB;

class JobService
{
    public function __construct(
        private JobRepositoryInterface $jobRepo,
        private AuditTrailService $auditTrail
    ) {}

    public function createJob(array $data): Job
    {
        return DB::transaction(function () use ($data) {
            $year = date('Y');
            $jobs = $this->jobRepo->findByCompany($data['company_id']);
            $lastJob = null;
            foreach ($jobs as $j) {
                if (str_starts_with($j->jobNumber, "JOB-{$year}-")) $lastJob = $j;
            }
            $sequence = $lastJob ? (int) substr($lastJob->jobNumber, -4) + 1 : 1;
            $jobNumber = JobNumber::generate($year, $sequence);

            $job = Job::reconstitute([
                'company_id' => $data['company_id'],
                'customer_id' => $data['customer_id'],
                'job_number' => $jobNumber->value(),
                'job_type' => $data['job_type'],
                'description' => $data['description'] ?? null,
                'quoted_value' => $data['quoted_value'] ?? null,
                'priority' => $data['priority'] ?? 'normal',
                'deadline' => $data['deadline'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'] ?? null,
                'status' => 'pending',
            ]);
            $job = $this->jobRepo->save($job);

            $this->auditTrail->log($job->companyId, $data['created_by'], 'job', $job->id, 'created', null, $job->toArray());
            event(new JobCreated($job->id, $job->companyId, $job->customerId, $job->jobNumber, $job->jobType, $job->quotedValue, $job->createdBy));

            return $job;
        });
    }

    public function assignJob(Job $job, int $assignedTo, int $assignedBy): Job
    {
        if ($job->isLocked()) throw new \DomainException('Cannot assign a locked job');

        $oldValues = $job->toArray();
        $updated = Job::reconstitute(array_merge($job->toArray(), [
            'assigned_to' => $assignedTo,
            'status' => 'in_progress',
            'started_at' => $job->startedAt ?? now()->format('Y-m-d H:i:s'),
        ]));
        $this->jobRepo->save($updated);

        $this->auditTrail->log($job->companyId, $assignedBy, 'job', $job->id, 'updated', $oldValues, $updated->toArray());
        event(new JobAssigned($job->id, $job->companyId, $assignedTo, $assignedBy));

        return $this->jobRepo->findById($job->id);
    }

    public function completeJob(Job $job, array $data, int $completedBy): Job
    {
        if ($job->isLocked()) throw new \DomainException('Cannot complete a locked job');
        if (!$job->isInProgress()) throw new \DomainException('Only jobs in progress can be completed');

        return DB::transaction(function () use ($job, $data, $completedBy) {
            $oldValues = $job->toArray();
            $totalCost = ($data['material_cost'] ?? 0) + ($data['labor_cost'] ?? 0) + ($data['overhead_cost'] ?? 0);
            $actualValue = $data['actual_value'];
            $profitAmount = $actualValue - $totalCost;
            $profitMargin = $actualValue > 0 ? ($profitAmount / $actualValue) * 100 : 0;

            $updated = Job::reconstitute(array_merge($job->toArray(), [
                'actual_value' => $actualValue,
                'material_cost' => $data['material_cost'] ?? 0,
                'labor_cost' => $data['labor_cost'] ?? 0,
                'overhead_cost' => $data['overhead_cost'] ?? 0,
                'total_cost' => $totalCost,
                'profit_amount' => $profitAmount,
                'profit_margin' => $profitMargin,
                'status' => 'completed',
                'completed_at' => now()->format('Y-m-d H:i:s'),
            ]));
            $this->jobRepo->save($updated);

            $this->auditTrail->log($job->companyId, $completedBy, 'job', $job->id, 'updated', $oldValues, $updated->toArray());
            event(new JobCompleted($job->id, $job->companyId, $job->customerId, $job->jobNumber, $actualValue, $totalCost, $profitAmount, $completedBy));

            return $this->jobRepo->findById($job->id);
        });
    }

    public function lockJob(Job $job, int $lockedBy): Job
    {
        if (!$job->isCompleted()) throw new \DomainException('Only completed jobs can be locked');

        $updated = Job::reconstitute(array_merge($job->toArray(), [
            'is_locked' => true,
            'locked_at' => now()->format('Y-m-d H:i:s'),
            'locked_by' => $lockedBy,
        ]));
        $this->jobRepo->save($updated);

        $this->auditTrail->log($job->companyId, $lockedBy, 'job', $job->id, 'locked', null, ['is_locked' => true]);
        return $this->jobRepo->findById($job->id);
    }

    public function unlockJob(Job $job, int $unlockedBy): Job
    {
        $updated = Job::reconstitute(array_merge($job->toArray(), [
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
        ]));
        $this->jobRepo->save($updated);

        $this->auditTrail->log($job->companyId, $unlockedBy, 'job', $job->id, 'unlocked', null, ['is_locked' => false]);
        return $this->jobRepo->findById($job->id);
    }

    public function updateJobStatus(Job $job, string $newStatus, int $changedBy, ?string $notes = null): Job
    {
        if ($job->isLocked()) throw new \DomainException('Cannot update status of a locked job');
        if ($job->status === $newStatus) return $job;

        $updated = Job::reconstitute(array_merge($job->toArray(), ['status' => $newStatus]));
        $this->jobRepo->save($updated);

        $this->auditTrail->log($job->companyId, $changedBy, 'job', $job->id, 'status_changed', ['status' => $job->status], ['status' => $newStatus]);
        return $this->jobRepo->findById($job->id);
    }
}
