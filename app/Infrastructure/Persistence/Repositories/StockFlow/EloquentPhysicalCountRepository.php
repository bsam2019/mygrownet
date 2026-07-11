<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\PhysicalCount;
use App\Domain\StockFlow\Entities\CountItem;
use App\Domain\StockFlow\Repositories\PhysicalCountRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\PhysicalCountId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaPhysicalCountModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCountItemModel;
use DateTimeImmutable;

class EloquentPhysicalCountRepository implements PhysicalCountRepositoryInterface
{
    public function findById(PhysicalCountId $id): ?PhysicalCount
    {
        $model = SaPhysicalCountModel::with('items.item.bin.department')->find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaPhysicalCountModel::where('sa_company_id', $companyId->toInt())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(PhysicalCount $count): PhysicalCount
    {
        $data = [
            'sa_company_id' => $count->getCompanyId()->toInt(),
            'title' => $count->getTitle(),
            'count_date' => $count->getCountDate()->format('Y-m-d'),
            'status' => $count->getStatus(),
            'counted_by' => $count->getCountedBy(),
            'verified_by' => $count->getVerifiedBy(),
            'notes' => $count->getNotes(),
        ];

        if ($count->id() === 0) {
            $model = SaPhysicalCountModel::create($data);
        } else {
            $model = SaPhysicalCountModel::find($count->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function saveCountItems(int $physicalCountId, array $items): void
    {
        SaCountItemModel::insert($items);
    }

    public function getCountItems(PhysicalCountId $id): array
    {
        return SaCountItemModel::where('sa_physical_count_id', $id->toInt())
            ->with('item')
            ->get()
            ->map(fn($m) => CountItem::reconstitute(
                id: \App\Domain\StockFlow\ValueObjects\CountItemId::fromInt($m->id),
                physicalCountId: $id,
                itemId: ItemId::fromInt($m->sa_item_id),
                binId: $m->sa_bin_id ? BinId::fromInt($m->sa_bin_id) : null,
                systemQuantity: (float) $m->system_quantity,
                physicalQuantity: (float) $m->physical_quantity,
                variance: (float) $m->variance,
                unitPrice: Money::fromFloat((float) $m->unit_price),
                varianceValue: Money::fromFloat((float) ($m->variance_value ?? 0)),
                itemName: $m->item?->name,
                createdAt: new DateTimeImmutable($m->created_at->format('Y-m-d H:i:s')),
                updatedAt: new DateTimeImmutable($m->updated_at->format('Y-m-d H:i:s')),
            ))
            ->all();
    }

    public function updateCountItem(int $countItemId, float $physicalQuantity, float $variance, float $varianceValue): void
    {
        SaCountItemModel::where('id', $countItemId)->update([
            'physical_quantity' => $physicalQuantity,
            'variance' => $variance,
            'variance_value' => $varianceValue,
            'updated_at' => now(),
        ]);
    }

    public function getCountItemData(PhysicalCountId $id): array
    {
        return SaCountItemModel::where('sa_physical_count_id', $id->toInt())
            ->get()
            ->map(fn($m) => [
                'system_quantity' => (float) $m->system_quantity,
                'physical_quantity' => (float) $m->physical_quantity,
                'unit_price' => (float) $m->unit_price,
                'sa_bin_id' => $m->sa_bin_id,
                'variance' => (float) $m->variance,
                'variance_value' => (float) ($m->variance_value ?? 0),
                'sa_item_id' => $m->sa_item_id,
            ])
            ->all();
    }

    private function toDomainEntity(SaPhysicalCountModel $model): PhysicalCount
    {
        $count = PhysicalCount::reconstitute(
            id: PhysicalCountId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            title: $model->title,
            countDate: new DateTimeImmutable($model->count_date->format('Y-m-d')),
            status: $model->status,
            countedBy: $model->counted_by,
            verifiedBy: $model->verified_by,
            notes: $model->notes,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );

        if ($model->relationLoaded('items')) {
            foreach ($model->items as $itemModel) {
                $countItem = CountItem::reconstitute(
                    id: \App\Domain\StockFlow\ValueObjects\CountItemId::fromInt($itemModel->id),
                    physicalCountId: PhysicalCountId::fromInt($itemModel->sa_physical_count_id),
                    itemId: ItemId::fromInt($itemModel->sa_item_id),
                    binId: $itemModel->sa_bin_id ? BinId::fromInt($itemModel->sa_bin_id) : null,
                    systemQuantity: (float) $itemModel->system_quantity,
                    physicalQuantity: (float) $itemModel->physical_quantity,
                    variance: (float) $itemModel->variance,
                    unitPrice: Money::fromFloat((float) $itemModel->unit_price),
                    varianceValue: Money::fromFloat((float) ($itemModel->variance_value ?? 0)),
                    itemName: $itemModel->item?->name,
                    createdAt: new DateTimeImmutable($itemModel->created_at->format('Y-m-d H:i:s')),
                    updatedAt: new DateTimeImmutable($itemModel->updated_at->format('Y-m-d H:i:s')),
                );
                $count->addItem($countItem);
            }
        }

        return $count;
    }
}
