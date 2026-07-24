<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\Quotation;
use App\Domain\GrowFinance\Repositories\QuotationRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceQuotationModel;

class EloquentQuotationRepository implements QuotationRepositoryInterface
{
    public function findById(int $id): ?Quotation
    {
        $model = GrowFinanceQuotationModel::find($id);
        return $model ? Quotation::reconstitute($model->toArray()) : null;
    }

    public function save(Quotation $entity): Quotation
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceQuotationModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceQuotationModel::create($data);
        return Quotation::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceQuotationModel::forBusiness($businessId)->get()->map(fn($m) => Quotation::reconstitute($m->toArray()))->toArray();
    }

    public function findByCustomer(int $customerId): array
    {
        return GrowFinanceQuotationModel::where('customer_id', $customerId)->get()->map(fn($m) => Quotation::reconstitute($m->toArray()))->toArray();
    }

    public function findByStatus(int $businessId, string $status): array
    {
        return GrowFinanceQuotationModel::forBusiness($businessId)->where('status', $status)->get()->map(fn($m) => Quotation::reconstitute($m->toArray()))->toArray();
    }

    public function findPending(int $businessId): array
    {
        return GrowFinanceQuotationModel::forBusiness($businessId)->whereIn('status', ['sent', 'draft'])->get()->map(fn($m) => Quotation::reconstitute($m->toArray()))->toArray();
    }

    public function findExpired(int $businessId): array
    {
        return GrowFinanceQuotationModel::forBusiness($businessId)->where('valid_until', '<', now()->toDateString())->whereNotIn('status', ['accepted', 'rejected', 'converted'])->get()->map(fn($m) => Quotation::reconstitute($m->toArray()))->toArray();
    }

}
