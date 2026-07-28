<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use DateTimeImmutable;

class WorkflowInstance
{
    public readonly ?int $id;
    public readonly int $businessId;
    public readonly int $workflowTemplateId;
    public readonly string $entityType;
    public readonly int $entityId;
    public readonly string $status;
    public readonly int $currentStep;
    public readonly ?array $approvalLog;
    public readonly int $requestedBy;
    public readonly ?DateTimeImmutable $completedAt;
    public readonly ?DateTimeImmutable $createdAt;
    public readonly ?DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        int $businessId,
        int $workflowTemplateId,
        string $entityType,
        int $entityId,
        string $status = 'pending',
        int $currentStep = 0,
        ?array $approvalLog = null,
        int $requestedBy,
        ?DateTimeImmutable $completedAt = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->id = $id;
        $this->businessId = $businessId;
        $this->workflowTemplateId = $workflowTemplateId;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->status = $status;
        $this->currentStep = $currentStep;
        $this->approvalLog = $approvalLog;
        $this->requestedBy = $requestedBy;
        $this->completedAt = $completedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: (int) $data['business_id'],
            workflowTemplateId: (int) $data['workflow_template_id'],
            entityType: $data['entity_type'],
            entityId: (int) $data['entity_id'],
            status: $data['status'] ?? 'pending',
            currentStep: (int) ($data['current_step'] ?? 0),
            approvalLog: isset($data['approval_log'])
                ? (is_string($data['approval_log']) ? json_decode($data['approval_log'], true) : $data['approval_log'])
                : null,
            requestedBy: (int) $data['requested_by'],
            completedAt: isset($data['completed_at']) ? new DateTimeImmutable($data['completed_at']) : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function advance(string $action, int $approverId, int $totalSteps, ?string $comment = null): self
    {
        if (!in_array($this->status, ['pending', 'in_progress'], true)) {
            throw new \DomainException('Cannot advance a workflow that is not in progress');
        }

        $log = $this->approvalLog ?? [];
        $log[] = [
            'step' => $this->currentStep,
            'approver_id' => $approverId,
            'action' => $action,
            'comment' => $comment,
            'timestamp' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];

        $newStep = $this->currentStep + 1;
        $isLast = $newStep >= $totalSteps;
        $now = new DateTimeImmutable();

        return new self(
            id: $this->id ?? null,
            businessId: $this->businessId,
            workflowTemplateId: $this->workflowTemplateId,
            entityType: $this->entityType,
            entityId: $this->entityId,
            status: $isLast ? 'approved' : 'in_progress',
            currentStep: $newStep,
            approvalLog: $log,
            requestedBy: $this->requestedBy,
            completedAt: $isLast ? $now : null,
            createdAt: $this->createdAt,
            updatedAt: $now,
        );
    }

    public function reject(int $approverId, string $comment): self
    {
        if (in_array($this->status, ['approved', 'rejected', 'cancelled'], true)) {
            throw new \DomainException('Cannot reject a workflow that is already completed');
        }

        $log = $this->approvalLog ?? [];
        $log[] = [
            'step' => $this->currentStep,
            'approver_id' => $approverId,
            'action' => 'reject',
            'comment' => $comment,
            'timestamp' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];

        return new self(
            id: $this->id ?? null,
            businessId: $this->businessId,
            workflowTemplateId: $this->workflowTemplateId,
            entityType: $this->entityType,
            entityId: $this->entityId,
            status: 'rejected',
            currentStep: $this->currentStep,
            approvalLog: $log,
            requestedBy: $this->requestedBy,
            completedAt: new DateTimeImmutable(),
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function escalate(): self
    {
        if ($this->status !== 'in_progress' && $this->status !== 'pending') {
            throw new \DomainException('Can only escalate an active workflow');
        }

        return new self(
            id: $this->id ?? null,
            businessId: $this->businessId,
            workflowTemplateId: $this->workflowTemplateId,
            entityType: $this->entityType,
            entityId: $this->entityId,
            status: 'escalated',
            currentStep: $this->currentStep,
            approvalLog: $this->approvalLog,
            requestedBy: $this->requestedBy,
            completedAt: $this->completedAt,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function cancel(): self
    {
        if (in_array($this->status, ['approved', 'rejected', 'cancelled'], true)) {
            throw new \DomainException('Workflow is already completed');
        }

        return new self(
            id: $this->id ?? null,
            businessId: $this->businessId,
            workflowTemplateId: $this->workflowTemplateId,
            entityType: $this->entityType,
            entityId: $this->entityId,
            status: 'cancelled',
            currentStep: $this->currentStep,
            approvalLog: $this->approvalLog,
            requestedBy: $this->requestedBy,
            completedAt: new DateTimeImmutable(),
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'workflow_template_id' => $this->workflowTemplateId,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'status' => $this->status,
            'current_step' => $this->currentStep,
            'approval_log' => $this->approvalLog ? json_encode($this->approvalLog) : null,
            'requested_by' => $this->requestedBy,
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
