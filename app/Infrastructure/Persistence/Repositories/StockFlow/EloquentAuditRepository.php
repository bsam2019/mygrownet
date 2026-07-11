<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Audit;
use App\Domain\StockFlow\Entities\AuditItem;
use App\Domain\StockFlow\Repositories\AuditRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\AuditId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\AuditStatus;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaAuditModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaAuditItemModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaAuditReconciliationModel;
use DateTimeImmutable;

class EloquentAuditRepository implements AuditRepositoryInterface
{
    public function findById(AuditId $id): ?Audit
    {
        $model = SaAuditModel::with(['items.bin', 'reconciliations.department'])->find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaAuditModel::where('sa_company_id', $companyId->toInt())
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(Audit $audit): Audit
    {
        $data = [
            'sa_company_id' => $audit->getCompanyId()->toInt(),
            'title' => $audit->getTitle(),
            'report_reference' => $audit->getReportReference(),
            'audit_date' => $audit->getAuditDate()->format('Y-m-d'),
            'status' => $audit->getStatus()->value(),
            'total_system_value' => $audit->getTotalSystemValue()->toFloat(),
            'total_physical_value' => $audit->getTotalPhysicalValue()->toFloat(),
            'total_variance' => $audit->getTotalVariance()->toFloat(),
            'unaccounted_value' => $audit->getUnaccountedValue()->toFloat(),
            'total_recorded_sales' => $audit->getTotalRecordedSales()->toFloat(),
            'executive_summary' => $audit->getExecutiveSummary(),
            'recommendations' => $audit->getRecommendations(),
            'conclusion' => $audit->getConclusion(),
            'prepared_for' => $audit->getPreparedFor(),
            'prepared_by' => $audit->getPreparedBy(),
        ];

        if ($audit->id() === 0) {
            $model = SaAuditModel::create($data);
        } else {
            $model = SaAuditModel::find($audit->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function createAuditItems(int $auditId, array $items): void
    {
        SaAuditItemModel::insert($items);
    }

    public function createReconciliation(int $auditId, array $data): void
    {
        SaAuditReconciliationModel::create(array_merge($data, ['sa_audit_id' => $auditId]));
    }

    public function getAuditItemData(AuditId $id): array
    {
        return SaAuditItemModel::where('sa_audit_id', $id->toInt())->get()->toArray();
    }

    public function nextReference(): string
    {
        $maxId = (int) SaAuditModel::max('id');
        return 'AUD-' . str_pad($maxId + 1, 5, '0', STR_PAD_LEFT);
    }

    private function toDomainEntity(SaAuditModel $model): Audit
    {
        $audit = Audit::reconstitute(
            id: AuditId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            title: $model->title,
            reportReference: $model->report_reference,
            auditDate: new DateTimeImmutable($model->audit_date->format('Y-m-d')),
            status: AuditStatus::fromString($model->status),
            totalSystemValue: Money::fromFloat((float) $model->total_system_value),
            totalPhysicalValue: Money::fromFloat((float) $model->total_physical_value),
            totalVariance: Money::fromFloat((float) $model->total_variance),
            unaccountedValue: Money::fromFloat((float) $model->unaccounted_value),
            totalRecordedSales: Money::fromFloat((float) ($model->total_recorded_sales ?? 0)),
            executiveSummary: $model->executive_summary,
            recommendations: $model->recommendations,
            conclusion: $model->conclusion,
            preparedFor: $model->prepared_for,
            preparedBy: $model->prepared_by,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );

        if ($model->relationLoaded('items')) {
            foreach ($model->items as $itemModel) {
                $auditItem = AuditItem::reconstitute(
                    id: \App\Domain\StockFlow\ValueObjects\AuditItemId::fromInt($itemModel->id),
                    auditId: AuditId::fromInt($itemModel->sa_audit_id),
                    itemId: ItemId::fromInt($itemModel->sa_item_id),
                    binId: $itemModel->sa_bin_id ? BinId::fromInt($itemModel->sa_bin_id) : null,
                    itemName: $itemModel->item_name,
                    unitPrice: Money::fromFloat((float) $itemModel->unit_price),
                    systemQty: (float) $itemModel->system_qty,
                    physicalQty: (float) $itemModel->physical_qty,
                    systemValue: Money::fromFloat((float) $itemModel->system_value),
                    physicalValue: Money::fromFloat((float) $itemModel->physical_value),
                    gapQty: (float) $itemModel->gap_qty,
                    gapValue: Money::fromFloat((float) $itemModel->gap_value),
                    createdAt: new DateTimeImmutable($itemModel->created_at->format('Y-m-d H:i:s')),
                );
                $audit->addItem($auditItem);
            }
        }

        return $audit;
    }
}
