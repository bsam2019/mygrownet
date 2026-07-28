<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\WorkflowInstance;
use App\Domain\GrowFinance\Repositories\WorkflowInstanceRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceWorkflowInstanceModel;

class EloquentWorkflowInstanceRepository implements WorkflowInstanceRepositoryInterface
{
    public function findById(int $id): ?WorkflowInstance
    {
        $model = GrowFinanceWorkflowInstanceModel::find($id);
        return $model ? WorkflowInstance::reconstitute($model->toArray()) : null;
    }

    public function findByEntity(int $businessId, string $entityType, int $entityId): array
    {
        return GrowFinanceWorkflowInstanceModel::forBusiness($businessId)
            ->ofEntity($entityType, $entityId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => WorkflowInstance::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findPending(int $businessId): array
    {
        return GrowFinanceWorkflowInstanceModel::forBusiness($businessId)
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => WorkflowInstance::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByStatus(int $businessId, string $status): array
    {
        return GrowFinanceWorkflowInstanceModel::forBusiness($businessId)
            ->ofStatus($status)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => WorkflowInstance::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(WorkflowInstance $entity): WorkflowInstance
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceWorkflowInstanceModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceWorkflowInstanceModel::create($data);
        return WorkflowInstance::reconstitute($model->toArray());
    }

    public function findByApprover(int $businessId, int $userId): array
    {
        $instances = GrowFinanceWorkflowInstanceModel::forBusiness($businessId)
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get();

        return $instances
            ->filter(function ($model) use ($userId) {
                $template = $model->template;
                if (!$template || !$template->steps) {
                    return false;
                }
                $steps = is_string($template->steps) ? json_decode($template->steps, true) : $template->steps;
                if (!is_array($steps)) {
                    return false;
                }
                $currentStep = $model->current_step;
                foreach ($steps as $step) {
                    if (($step['step_order'] ?? 0) === $currentStep) {
                        if (isset($step['approver_id']) && (int) $step['approver_id'] === $userId) {
                            return true;
                        }
                        if (($step['role'] ?? '') === 'any') {
                            return true;
                        }
                    }
                }
                return false;
            })
            ->map(fn($m) => WorkflowInstance::reconstitute($m->toArray()))
            ->values()
            ->toArray();
    }
}
