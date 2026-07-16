<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\ExchangeRate;
use App\Domain\StockFlow\Repositories\ExchangeRateRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\ExchangeRateId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaExchangeRateModel;
use DateTimeImmutable;

class EloquentExchangeRateRepository implements ExchangeRateRepositoryInterface
{
    public function findById(ExchangeRateId $id): ?ExchangeRate
    {
        $model = SaExchangeRateModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaExchangeRateModel::where('sa_company_id', $companyId->toInt())->orderByDesc('effective_date')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findRate(CompanyId $companyId, string $from, string $to): ?ExchangeRate
    {
        $model = SaExchangeRateModel::where('sa_company_id', $companyId->toInt())
            ->where('from_currency', $from)->where('to_currency', $to)
            ->orderByDesc('effective_date')->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(ExchangeRate $rate): ExchangeRate
    {
        $data = $rate->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        if ($rate->id() === 0) {
            $model = SaExchangeRateModel::create($data);
        } else {
            $model = SaExchangeRateModel::find($rate->id());
            $model->update($data);
        }
        return $this->toDomainEntity($model->fresh());
    }

    public function delete(ExchangeRateId $id): void
    {
        SaExchangeRateModel::destroy($id->toInt());
    }

    private function toDomainEntity(SaExchangeRateModel $model): ExchangeRate
    {
        return ExchangeRate::reconstitute(
            id: ExchangeRateId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            fromCurrency: $model->from_currency,
            toCurrency: $model->to_currency,
            rate: (float) $model->rate,
            effectiveDate: new DateTimeImmutable($model->effective_date->format('Y-m-d')),
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
