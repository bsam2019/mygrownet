<?php

namespace App\Extensions\Restaurant\Repositories;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Extensions\Restaurant\Entities\WastageRecord;
use App\Extensions\Restaurant\Models\SaWastageRecordModel;
use App\Extensions\Restaurant\ValueObjects\WastageRecordId;
use DateTimeImmutable;

class EloquentWastageRepository implements WastageRepositoryInterface
{
    public function findById(WastageRecordId $id): ?WastageRecord
    {
        $model = SaWastageRecordModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompany(CompanyId $companyId): array
    {
        return SaWastageRecordModel::where('sa_company_id', $companyId->toInt())->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function save(WastageRecord $record): WastageRecord
    {
        $data = $record->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($record->id() === 0) {
            $model = SaWastageRecordModel::create($data);
        } else {
            $model = SaWastageRecordModel::find($record->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(WastageRecordId $id): void
    {
        SaWastageRecordModel::find($id->toInt())?->delete();
    }

    private function toDomainEntity(SaWastageRecordModel $model): WastageRecord
    {
        return WastageRecord::reconstitute(
            id: WastageRecordId::fromInt($model->id), companyId: CompanyId::fromInt($model->sa_company_id),
            itemId: ItemId::fromInt($model->sa_item_id), quantity: (float) $model->quantity,
            unitCost: (float) $model->unit_cost, reason: $model->reason,
            referenceType: $model->reference_type, referenceId: $model->reference_id,
            notes: $model->notes, occurredAt: $model->occurred_at->format('Y-m-d'),
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
