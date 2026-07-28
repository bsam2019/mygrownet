<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\WorkflowTemplate;
use App\Domain\GrowFinance\Repositories\WorkflowTemplateRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceWorkflowTemplateModel;

class EloquentWorkflowTemplateRepository implements WorkflowTemplateRepositoryInterface
{
    public function findById(int $id): ?WorkflowTemplate
    {
        $model = GrowFinanceWorkflowTemplateModel::find($id);
        return $model ? WorkflowTemplate::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceWorkflowTemplateModel::forBusiness($businessId)
            ->orderBy('name')
            ->get()
            ->map(fn($m) => WorkflowTemplate::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByEntityType(int $businessId, string $entityType): array
    {
        return GrowFinanceWorkflowTemplateModel::forBusiness($businessId)
            ->ofEntityType($entityType)
            ->orderBy('name')
            ->get()
            ->map(fn($m) => WorkflowTemplate::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActiveByEntityType(int $businessId, string $entityType): ?WorkflowTemplate
    {
        $model = GrowFinanceWorkflowTemplateModel::forBusiness($businessId)
            ->active()
            ->ofEntityType($entityType)
            ->first();
        return $model ? WorkflowTemplate::reconstitute($model->toArray()) : null;
    }

    public function save(WorkflowTemplate $entity): WorkflowTemplate
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceWorkflowTemplateModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceWorkflowTemplateModel::create($data);
        return WorkflowTemplate::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        GrowFinanceWorkflowTemplateModel::where('id', $id)->delete();
    }
}
