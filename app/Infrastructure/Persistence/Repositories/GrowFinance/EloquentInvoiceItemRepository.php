<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\InvoiceItem;
use App\Domain\GrowFinance\Repositories\InvoiceItemRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceInvoiceItemModel;

class EloquentInvoiceItemRepository implements InvoiceItemRepositoryInterface
{
    public function findById(int $id): ?InvoiceItem
    {
        $model = GrowFinanceInvoiceItemModel::find($id);
        return $model ? InvoiceItem::reconstitute($model->toArray()) : null;
    }

    public function save(InvoiceItem $entity): InvoiceItem
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceInvoiceItemModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceInvoiceItemModel::create($data);
        return InvoiceItem::reconstitute($model->toArray());
    }

    public function findByInvoice(int $invoiceId): array
    {
        return GrowFinanceInvoiceItemModel::where('invoice_id', $invoiceId)->get()->map(fn($m) => InvoiceItem::reconstitute($m->toArray()))->toArray();
    }

}
