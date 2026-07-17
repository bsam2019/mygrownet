<?php

namespace App\Extensions\Manufacturing\Repositories;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Extensions\Manufacturing\Entities\WorkOrder;
use App\Extensions\Manufacturing\Models\SaWorkOrderModel;
use App\Extensions\Manufacturing\ValueObjects\WorkOrderId;
use DateTimeImmutable;

class EloquentWorkOrderRepository implements WorkOrderRepositoryInterface
{
    public function findById(WorkOrderId $id): ?WorkOrder
    {
        $model = SaWorkOrderModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompany(CompanyId $companyId): array
    {
        return SaWorkOrderModel::where('sa_company_id', $companyId->toInt())->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function save(WorkOrder $workOrder): WorkOrder
    {
        $data = $workOrder->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($workOrder->id() === 0) {
            $model = SaWorkOrderModel::create($data);
        } else {
            $model = SaWorkOrderModel::find($workOrder->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(WorkOrderId $id): void
    {
        SaWorkOrderModel::find($id->toInt())?->delete();
    }

    private function toDomainEntity(SaWorkOrderModel $model): WorkOrder
    {
        return WorkOrder::reconstitute(
            id: WorkOrderId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            bomId: $model->sa_bom_id, itemId: ItemId::fromInt($model->sa_item_id),
            orderNumber: $model->order_number, quantity: (float) $model->quantity,
            completedQuantity: (float) $model->completed_quantity, scrappedQuantity: (float) $model->scrapped_quantity,
            status: $model->status, dueDate: $model->due_date?->format('Y-m-d'),
            startedAt: $model->started_at?->format('Y-m-d'), completedAt: $model->completed_at?->format('Y-m-d'),
            notes: $model->notes,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
