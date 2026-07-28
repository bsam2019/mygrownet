<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\WorkflowInstance;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\WorkflowInstanceRepositoryInterface;
use App\Domain\GrowFinance\Repositories\WorkflowTemplateRepositoryInterface;
use DomainException;

class WorkflowEngine
{
    public function __construct(
        private WorkflowTemplateRepositoryInterface $templateRepo,
        private WorkflowInstanceRepositoryInterface $instanceRepo,
        private PostingEngine $postingEngine,
        private JournalEntryRepositoryInterface $journalEntryRepo,
    ) {}

    public function startWorkflow(int $businessId, string $entityType, int $entityId, int $requestedBy): WorkflowInstance
    {
        $template = $this->templateRepo->findActiveByEntityType($businessId, $entityType);
        if (!$template) {
            throw new DomainException("No active workflow template found for entity type: {$entityType}");
        }

        $existing = $this->instanceRepo->findByEntity($businessId, $entityType, $entityId);
        $hasPending = array_filter($existing, fn(WorkflowInstance $i) => in_array($i->status, ['pending', 'in_progress'], true));
        if (!empty($hasPending)) {
            throw new DomainException('A pending workflow instance already exists for this entity');
        }

        $instance = new WorkflowInstance(
            id: null,
            businessId: $businessId,
            workflowTemplateId: $template->id,
            entityType: $entityType,
            entityId: $entityId,
            status: 'in_progress',
            currentStep: 0,
            approvalLog: null,
            requestedBy: $requestedBy,
        );

        return $this->instanceRepo->save($instance);
    }

    public function approve(int $instanceId, int $approverId, ?string $comment = null): WorkflowInstance
    {
        $instance = $this->instanceRepo->findById($instanceId);
        if (!$instance) {
            throw new DomainException('Workflow instance not found');
        }

        if (!in_array($instance->status, ['pending', 'in_progress'], true)) {
            throw new DomainException('Workflow is not in an approvable state');
        }

        $template = $this->templateRepo->findById($instance->workflowTemplateId);
        if (!$template) {
            throw new DomainException('Workflow template not found');
        }

        $totalSteps = count($template->steps);
        $steps = $template->steps;

        $advanced = $instance->advance('approve', $approverId, $totalSteps, $comment);
        $saved = $this->instanceRepo->save($advanced);

        if ($saved->status === 'approved') {
            $this->finalizeApproval($saved, $template->entityType);
        }

        return $saved;
    }

    public function reject(int $instanceId, int $approverId, string $comment): WorkflowInstance
    {
        $instance = $this->instanceRepo->findById($instanceId);
        if (!$instance) {
            throw new DomainException('Workflow instance not found');
        }

        $rejected = $instance->reject($approverId, $comment);
        return $this->instanceRepo->save($rejected);
    }

    public function cancel(int $instanceId): WorkflowInstance
    {
        $instance = $this->instanceRepo->findById($instanceId);
        if (!$instance) {
            throw new DomainException('Workflow instance not found');
        }

        $cancelled = $instance->cancel();
        return $this->instanceRepo->save($cancelled);
    }

    public function getPendingApprovals(int $businessId, int $userId): array
    {
        return $this->instanceRepo->findByApprover($businessId, $userId);
    }

    public function getTemplates(int $businessId): array
    {
        return $this->templateRepo->findByBusiness($businessId);
    }

    public function getInstances(int $businessId): array
    {
        return $this->instanceRepo->findByStatus($businessId, 'in_progress');
    }

    public function findPendingByApprover(int $businessId, int $userId): array
    {
        return $this->instanceRepo->findByApprover($businessId, $userId);
    }

    public function escalate(int $instanceId): WorkflowInstance
    {
        $instance = $this->instanceRepo->findById($instanceId);
        if (!$instance) {
            throw new \DomainException('Workflow instance not found');
        }

        $escalated = $instance->escalate();
        return $this->instanceRepo->save($escalated);
    }

    public function setSla(int $templateId, ?int $slaHours, bool $allowEscalation = false): void
    {
        $template = $this->templateRepo->findById($templateId);
        if (!$template) {
            throw new \DomainException('Workflow template not found');
        }

        // Create new template instance with updated SLA fields
        $ref = new \ReflectionClass($template);
        $updated = new \App\Domain\GrowFinance\Entities\WorkflowTemplate(
            id: $template->id,
            businessId: $template->businessId,
            name: $template->name,
            description: $template->description,
            entityType: $template->entityType,
            steps: $template->steps,
            isActive: $template->isActive,
            createdAt: $template->createdAt,
            updatedAt: $template->updatedAt,
            slaHours: $slaHours,
            allowEscalation: $allowEscalation,
        );

        $this->templateRepo->save($updated);
    }

    private function finalizeApproval(WorkflowInstance $instance, string $entityType): void
    {
        if ($entityType !== 'journal') {
            return;
        }

        $journal = $this->journalEntryRepo->findById($instance->entityId);
        if (!$journal) {
            throw new DomainException('Referenced journal entry not found');
        }

        $this->postingEngine->post($journal->id);
    }
}
