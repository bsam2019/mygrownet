<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Transfer;
use App\Domain\StockFlow\Entities\TransferItem;
use App\Domain\StockFlow\Repositories\TransferRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\TransferId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\WarehouseId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaTransferModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaTransferItemModel;
use DateTimeImmutable;

class EloquentTransferRepository implements TransferRepositoryInterface
{
    public function findById(TransferId $id): ?Transfer
    {
        $model = SaTransferModel::with('items.item', 'fromWarehouse', 'toWarehouse')->find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId, int $perPage = 20): array
    {
        return SaTransferModel::where('sa_company_id', $companyId->toInt())
            ->with('items.item', 'fromWarehouse', 'toWarehouse')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByStatus(CompanyId $companyId, string $status): array
    {
        return SaTransferModel::where('sa_company_id', $companyId->toInt())
            ->where('status', $status)
            ->with('items.item', 'fromWarehouse', 'toWarehouse')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(Transfer $transfer): Transfer
    {
        $data = [
            'sa_company_id' => $transfer->getCompanyId()->toInt(),
            'transfer_number' => $transfer->getTransferNumber(),
            'from_warehouse_id' => $transfer->getFromWarehouseId()->toInt(),
            'to_warehouse_id' => $transfer->getToWarehouseId()->toInt(),
            'status' => $transfer->getStatus(),
            'transferred_by' => $transfer->getTransferredBy(),
            'received_by' => $transfer->getReceivedBy(),
            'notes' => $transfer->getNotes(),
        ];

        if ($transfer->id() === 0) {
            $model = SaTransferModel::create($data);
        } else {
            $model = SaTransferModel::find($transfer->id());
            $model->update($data);
        }

        if ($transfer->id() === 0) {
            SaTransferItemModel::where('sa_transfer_id', $model->id)->delete();
        }
        foreach ($transfer->getItems() as $item) {
            SaTransferItemModel::create([
                'sa_transfer_id' => $model->id,
                'sa_item_id' => $item->getItemId()->toInt(),
                'quantity' => $item->getQuantity(),
                'unit_cost' => $item->getUnitCost()?->toFloat(),
                'created_at' => now(),
            ]);
        }

        return $this->toDomainEntity($model->fresh()->load('items.item', 'fromWarehouse', 'toWarehouse'));
    }

    public function nextTransferNumber(): string
    {
        $maxId = (int) SaTransferModel::max('id');
        return 'TRF-' . str_pad($maxId + 1, 6, '0', STR_PAD_LEFT);
    }

    private function toDomainEntity(SaTransferModel $model): Transfer
    {
        $transfer = Transfer::reconstitute(
            id: TransferId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            transferNumber: $model->transfer_number,
            fromWarehouseId: WarehouseId::fromInt($model->from_warehouse_id),
            toWarehouseId: WarehouseId::fromInt($model->to_warehouse_id),
            status: $model->status,
            transferredBy: $model->transferred_by,
            receivedBy: $model->received_by,
            notes: $model->notes,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );

        if ($model->relationLoaded('items')) {
            foreach ($model->items as $itemModel) {
                $transfer->addItem(TransferItem::reconstitute(
                    id: $itemModel->id,
                    itemId: ItemId::fromInt($itemModel->sa_item_id),
                    quantity: (float) $itemModel->quantity,
                    unitCost: $itemModel->unit_cost ? Money::fromFloat((float) $itemModel->unit_cost) : null,
                    createdAt: new DateTimeImmutable($itemModel->created_at->format('Y-m-d H:i:s')),
                    itemName: $itemModel->item?->name,
                ));
            }
        }

        return $transfer;
    }
}
