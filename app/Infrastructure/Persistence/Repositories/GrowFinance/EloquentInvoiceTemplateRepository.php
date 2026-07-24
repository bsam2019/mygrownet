<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\InvoiceTemplate;
use App\Domain\GrowFinance\Repositories\InvoiceTemplateRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceInvoiceTemplateModel;

class EloquentInvoiceTemplateRepository implements InvoiceTemplateRepositoryInterface
{
    public function findById(int $id): ?InvoiceTemplate
    {
        $model = GrowFinanceInvoiceTemplateModel::find($id);
        return $model ? InvoiceTemplate::reconstitute($model->toArray()) : null;
    }

    public function save(InvoiceTemplate $entity): InvoiceTemplate
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceInvoiceTemplateModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceInvoiceTemplateModel::create($data);
        return InvoiceTemplate::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceInvoiceTemplateModel::forBusiness($businessId)->get()->map(fn($m) => InvoiceTemplate::reconstitute($m->toArray()))->toArray();
    }

    public function findActive(int $businessId): array
    {
        return GrowFinanceInvoiceTemplateModel::forBusiness($businessId)->active()->get()->map(fn($m) => InvoiceTemplate::reconstitute($m->toArray()))->toArray();
    }

    public function findDefault(int $businessId): ?InvoiceTemplate
    {
        $_ = GrowFinanceInvoiceTemplateModel::forBusiness($businessId)->where('is_default', true)->where('is_active', true)->first(); return $_ ? InvoiceTemplate::reconstitute($_->toArray()) : null;
    }

}
