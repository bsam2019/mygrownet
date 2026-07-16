<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\SaleReturn;
use App\Domain\StockFlow\Repositories\SaleReturnRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\SaleReturnId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaSaleReturnModel;
use DateTimeImmutable;

class EloquentSaleReturnRepository implements SaleReturnRepositoryInterface
{
    public function findById(SaleReturnId $id): ?SaleReturn
    {
        $model = SaSaleReturnModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaSaleReturnModel::where('sa_company_id', $companyId->toInt())->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function save(SaleReturn $return): SaleReturn
    {
        $data = $return->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        if ($return->id() === 0) {
            $model = SaSaleReturnModel::create($data);
        } else {
            $model = SaSaleReturnModel::find($return->id());
            $model->update($data);
        }
        return $this->toDomainEntity($model->fresh());
    }

    public function delete(SaleReturnId $id): void { SaSaleReturnModel::destroy($id->toInt()); }

    private function toDomainEntity(SaSaleReturnModel $model): SaleReturn
    {
        return SaleReturn::reconstitute(
            id: SaleReturnId::fromInt($model->id), companyId: CompanyId::fromInt($model->sa_company_id),
            saleId: \App\Domain\StockFlow\ValueObjects\SaleId::fromInt($model->sa_sale_id),
            returnNumber: $model->return_number, returnDate: new DateTimeImmutable($model->return_date->format('Y-m-d')),
            reason: $model->reason, totalRefund: (float) $model->total_refund,
            refundMethod: $model->refund_method, notes: $model->notes,
            createdBy: \App\Domain\StockFlow\ValueObjects\UserId::fromInt($model->created_by),
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
