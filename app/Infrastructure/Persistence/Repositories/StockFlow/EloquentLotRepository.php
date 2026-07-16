<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Lot;
use App\Domain\StockFlow\Repositories\LotRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\LotId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaLotModel;
use DateTimeImmutable;

class EloquentLotRepository implements LotRepositoryInterface
{
    public function findById(LotId $id): ?Lot
    {
        $model = SaLotModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaLotModel::where('sa_company_id', $companyId->toInt())->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findByItemId(CompanyId $companyId, ItemId $itemId): array
    {
        return SaLotModel::where('sa_company_id', $companyId->toInt())->where('sa_item_id', $itemId->toInt())->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findByLotNumber(CompanyId $companyId, string $lotNumber): ?Lot
    {
        $model = SaLotModel::where('sa_company_id', $companyId->toInt())->where('lot_number', $lotNumber)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(Lot $lot): Lot
    {
        $data = $lot->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        if ($lot->id() === 0) {
            $model = SaLotModel::create($data);
        } else {
            $model = SaLotModel::find($lot->id());
            $model->update($data);
        }
        return $this->toDomainEntity($model->fresh());
    }

    public function delete(LotId $id): void
    {
        SaLotModel::destroy($id->toInt());
    }

    private function toDomainEntity(SaLotModel $model): Lot
    {
        return Lot::reconstitute(
            id: LotId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            itemId: ItemId::fromInt($model->sa_item_id),
            lotNumber: $model->lot_number,
            manufacturingDate: $model->manufacturing_date ? new DateTimeImmutable($model->manufacturing_date->format('Y-m-d')) : null,
            expiryDate: $model->expiry_date ? new DateTimeImmutable($model->expiry_date->format('Y-m-d')) : null,
            receivedDate: $model->received_date ? new DateTimeImmutable($model->received_date->format('Y-m-d')) : null,
            initialQuantity: (float) $model->initial_quantity,
            currentQuantity: (float) $model->current_quantity,
            status: $model->status,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
