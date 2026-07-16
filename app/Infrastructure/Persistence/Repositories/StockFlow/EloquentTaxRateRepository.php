<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\TaxRate;
use App\Domain\StockFlow\Repositories\TaxRateRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\TaxRateId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaTaxRateModel;
use DateTimeImmutable;

class EloquentTaxRateRepository implements TaxRateRepositoryInterface
{
    public function findById(TaxRateId $id): ?TaxRate
    {
        $model = SaTaxRateModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaTaxRateModel::where('sa_company_id', $companyId->toInt())->orderBy('name')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findDefault(CompanyId $companyId): ?TaxRate
    {
        $model = SaTaxRateModel::where('sa_company_id', $companyId->toInt())->where('is_default', true)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(TaxRate $taxRate): TaxRate
    {
        $data = $taxRate->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        if ($taxRate->id() === 0) {
            $model = SaTaxRateModel::create($data);
        } else {
            $model = SaTaxRateModel::find($taxRate->id());
            $model->update($data);
        }
        return $this->toDomainEntity($model->fresh());
    }

    public function delete(TaxRateId $id): void
    {
        SaTaxRateModel::destroy($id->toInt());
    }

    private function toDomainEntity(SaTaxRateModel $model): TaxRate
    {
        return TaxRate::reconstitute(
            id: TaxRateId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            name: $model->name,
            rate: (float) $model->rate,
            type: $model->type,
            isDefault: (bool) $model->is_default,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
