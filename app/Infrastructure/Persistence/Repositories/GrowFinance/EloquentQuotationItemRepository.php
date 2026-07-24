<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\QuotationItem;
use App\Domain\GrowFinance\Repositories\QuotationItemRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceQuotationItemModel;

class EloquentQuotationItemRepository implements QuotationItemRepositoryInterface
{
    public function findById(int $id): ?QuotationItem
    {
        $model = GrowFinanceQuotationItemModel::find($id);
        return $model ? QuotationItem::reconstitute($model->toArray()) : null;
    }

    public function save(QuotationItem $entity): QuotationItem
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceQuotationItemModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceQuotationItemModel::create($data);
        return QuotationItem::reconstitute($model->toArray());
    }

    public function findByQuotation(int $quotationId): array
    {
        return GrowFinanceQuotationItemModel::where('quotation_id', $quotationId)->get()->map(fn($m) => QuotationItem::reconstitute($m->toArray()))->toArray();
    }

}
