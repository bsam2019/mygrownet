<?php
declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\WorkflowInstanceRepositoryInterface;
use App\Domain\GrowFinance\Repositories\WorkflowTemplateRepositoryInterface;
use DateTimeImmutable;

class WorkflowEscalationService
{
    public function __construct(
        private WorkflowInstanceRepositoryInterface $instanceRepo,
        private WorkflowTemplateRepositoryInterface $templateRepo,
    ) {}

    /**
     * Check all active workflow instances for SLA breaches and escalate.
     * Returns list of escalated instances.
     */
    public function checkAndEscalate(int $businessId): array
    {
        $instances = $this->instanceRepo->findPending($businessId);
        $escalated = [];

        foreach ($instances as $instance) {
            $template = $this->templateRepo->findById($instance->workflowTemplateId);
            if (!$template) continue;

            $slaHours = $template->slaHours ?? 0;
            if ($slaHours <= 0) continue;

            $createdAt = new DateTimeImmutable($instance->createdAt->format('Y-m-d H:i:s'));
            $now = new DateTimeImmutable('now');
            $elapsedHours = ($now->getTimestamp() - $createdAt->getTimestamp()) / 3600;

            if ($elapsedHours > $slaHours) {
                $instance = $this->instanceRepo->save($instance->escalate());
                $escalated[] = [
                    'instance_id' => $instance->id,
                    'template_name' => $template->name,
                    'entity_type' => $instance->entityType,
                    'elapsed_hours' => round($elapsedHours, 1),
                    'sla_hours' => $slaHours,
                ];
            }
        }

        return $escalated;
    }
}
