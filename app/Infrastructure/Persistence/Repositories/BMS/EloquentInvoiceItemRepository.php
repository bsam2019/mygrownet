<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\InvoiceItem;
use App\Domain\BMS\Repositories\InvoiceItemRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\InvoiceItemModel;

class EloquentInvoiceItemRepository implements InvoiceItemRepositoryInterface
{
    public function findById(int $id): ?InvoiceItem
    {
        $model = InvoiceItemModel::find($id);
        return $model ? InvoiceItem::reconstitute($model->toArray()) : null;
    }

    public function save(InvoiceItem $entity): InvoiceItem
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            InvoiceItemModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = InvoiceItemModel::create($data);
        return InvoiceItem::reconstitute($model->toArray());
    }

    public function findByInvoice(int $invoiceId): array
    {
        return InvoiceItemModel::where('invoice_id', $invoiceId)->get()
            ->map(fn($m) => InvoiceItem::reconstitute($m->toArray()))
            ->toArray();
    }

    public function deleteByInvoice(int $invoiceId): void
    {
        InvoiceItemModel::where('invoice_id', $invoiceId)->delete();
    }
}
