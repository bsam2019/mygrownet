<?php

namespace App\Extensions\Pharmacy\Repositories;

use App\Extensions\Pharmacy\Entities\ControlledMedicine;
use App\Extensions\Pharmacy\ValueObjects\ControlledMedicineId;
use App\Extensions\Pharmacy\Models\SaControlledMedicineModel;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\LotId;
use App\Domain\StockFlow\ValueObjects\UserId;
use DateTimeImmutable;

class EloquentControlledMedicineRepository implements ControlledMedicineRepositoryInterface
{
    public function findById(ControlledMedicineId $id): ?ControlledMedicine
    {
        $model = SaControlledMedicineModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaControlledMedicineModel::where('sa_company_id', $companyId->toInt())->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findByItem(CompanyId $companyId, ItemId $itemId): array
    {
        return SaControlledMedicineModel::where('sa_company_id', $companyId->toInt())->where('sa_item_id', $itemId->toInt())->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function save(ControlledMedicine $entry): ControlledMedicine
    {
        $data = $entry->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        if ($entry->id() === 0) {
            $model = SaControlledMedicineModel::create($data);
        } else {
            $model = SaControlledMedicineModel::find($entry->id());
            $model->update($data);
        }
        return $this->toDomainEntity($model->fresh());
    }

    private function toDomainEntity(SaControlledMedicineModel $model): ControlledMedicine
    {
        return ControlledMedicine::reconstitute(
            id: ControlledMedicineId::fromInt($model->id), companyId: CompanyId::fromInt($model->sa_company_id),
            itemId: ItemId::fromInt($model->sa_item_id), lotId: $model->sa_lot_id ? LotId::fromInt($model->sa_lot_id) : null,
            transactionType: $model->transaction_type, quantity: (float) $model->quantity, balanceAfter: (float) $model->balance_after,
            patientName: $model->patient_name, patientIdNumber: $model->patient_id_number,
            prescriptionNumber: $model->prescription_number, notes: $model->notes,
            staffUserId: UserId::fromInt($model->staff_user_id),
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
