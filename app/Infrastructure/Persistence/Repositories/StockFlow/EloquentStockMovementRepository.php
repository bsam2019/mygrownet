<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\StockMovement;
use App\Domain\StockFlow\Repositories\StockMovementRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\StockMovementId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\MovementType;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaStockMovementModel;
use DateTimeImmutable;

class EloquentStockMovementRepository implements StockMovementRepositoryInterface
{
    public function findByCompanyId(CompanyId $companyId, int $perPage = 50): array
    {
        return SaStockMovementModel::where('sa_company_id', $companyId->toInt())
            ->with('item.bin')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByItemId(ItemId $itemId): array
    {
        return SaStockMovementModel::where('sa_item_id', $itemId->toInt())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(StockMovement $movement): StockMovement
    {
        $data = [
            'sa_company_id' => $movement->getCompanyId()->toInt(),
            'sa_item_id' => $movement->getItemId()->toInt(),
            'sa_bin_id' => $movement->getBinId()?->toInt(),
            'type' => $movement->getType()->value(),
            'quantity' => $movement->getQuantity(),
            'unit_price' => $movement->getUnitPrice()->toFloat(),
            'total_value' => $movement->getTotalValue()->toFloat(),
            'quantity_before' => $movement->getQuantityBefore(),
            'quantity_after' => $movement->getQuantityAfter(),
            'reason' => $movement->getReason(),
            'reference_type' => $movement->getReferenceType(),
            'reference_id' => $movement->getReferenceId(),
            'created_by' => $movement->getCreatedBy(),
        ];

        $model = SaStockMovementModel::create($data);

        return $this->toDomainEntity($model->fresh());
    }

    private function toDomainEntity(SaStockMovementModel $model): StockMovement
    {
        $binId = $model->sa_bin_id ? BinId::fromInt($model->sa_bin_id) : null;

        return StockMovement::reconstitute(
            id: StockMovementId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            itemId: ItemId::fromInt($model->sa_item_id),
            binId: $binId,
            type: MovementType::fromString($model->type),
            quantity: (float) $model->quantity,
            unitPrice: Money::fromFloat((float) ($model->unit_price ?? 0)),
            totalValue: Money::fromFloat((float) ($model->total_value ?? 0)),
            quantityBefore: (float) $model->quantity_before,
            quantityAfter: (float) $model->quantity_after,
            reason: $model->reason,
            referenceType: $model->reference_type,
            referenceId: $model->reference_id,
            createdBy: $model->created_by ?? 0,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            itemName: $model->item?->name,
        );
    }
}
