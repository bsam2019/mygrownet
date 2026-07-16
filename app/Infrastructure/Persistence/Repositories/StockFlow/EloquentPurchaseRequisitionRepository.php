<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\PurchaseRequisition;
use App\Domain\StockFlow\Repositories\PurchaseRequisitionRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\PurchaseRequisitionId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaPurchaseRequisitionModel;
use DateTimeImmutable;

class EloquentPurchaseRequisitionRepository implements PurchaseRequisitionRepositoryInterface
{
    public function findById(PurchaseRequisitionId $id): ?PurchaseRequisition
    {
        $model = SaPurchaseRequisitionModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaPurchaseRequisitionModel::where('sa_company_id', $companyId->toInt())->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findByStatus(CompanyId $companyId, string $status): array
    {
        return SaPurchaseRequisitionModel::where('sa_company_id', $companyId->toInt())->where('status', $status)->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function save(PurchaseRequisition $requisition): PurchaseRequisition
    {
        $data = $requisition->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        if ($requisition->id() === 0) {
            $model = SaPurchaseRequisitionModel::create($data);
        } else {
            $model = SaPurchaseRequisitionModel::find($requisition->id());
            $model->update($data);
        }
        return $this->toDomainEntity($model->fresh());
    }

    public function delete(PurchaseRequisitionId $id): void
    {
        SaPurchaseRequisitionModel::destroy($id->toInt());
    }

    private function toDomainEntity(SaPurchaseRequisitionModel $model): PurchaseRequisition
    {
        return PurchaseRequisition::reconstitute(
            id: PurchaseRequisitionId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            requisitionNumber: $model->requisition_number,
            requestedBy: \App\Domain\StockFlow\ValueObjects\UserId::fromInt($model->requested_by),
            approvedBy: $model->approved_by ? \App\Domain\StockFlow\ValueObjects\UserId::fromInt($model->approved_by) : null,
            dateRequired: $model->date_required ? new DateTimeImmutable($model->date_required->format('Y-m-d')) : null,
            status: $model->status,
            notes: $model->notes,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
