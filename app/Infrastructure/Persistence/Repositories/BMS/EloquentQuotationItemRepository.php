<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\QuotationItem;
use App\Domain\BMS\Repositories\QuotationItemRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\QuotationItemModel;

class EloquentQuotationItemRepository implements QuotationItemRepositoryInterface
{
    public function findById(int $id): ?QuotationItem
    {
        $model = QuotationItemModel::find($id);
        return $model ? QuotationItem::reconstitute($model->toArray()) : null;
    }

    public function save(QuotationItem $entity): QuotationItem
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            QuotationItemModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = QuotationItemModel::create($data);
        return QuotationItem::reconstitute($model->toArray());
    }

    public function findByQuotation(int $quotationId): array
    {
        return QuotationItemModel::where('quotation_id', $quotationId)->get()
            ->map(fn($m) => QuotationItem::reconstitute($m->toArray()))
            ->toArray();
    }

    public function deleteByQuotation(int $quotationId): void
    {
        QuotationItemModel::where('quotation_id', $quotationId)->delete();
    }
}
