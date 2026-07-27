<?php

namespace App\Domain\FinancialServicesCore\Infrastructure;

use App\Domain\FinancialServicesCore\Entities\Currency;
use App\Domain\FinancialServicesCore\Repositories\CurrencyRepositoryInterface;

class EloquentCurrencyRepository implements CurrencyRepositoryInterface
{
    public function findByCode(string $code): ?Currency
    {
        $model = CurrencyModel::where('code', strtoupper($code))->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function findActive(): array
    {
        return CurrencyModel::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(fn(CurrencyModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findAll(): array
    {
        return CurrencyModel::orderBy('code')
            ->get()
            ->map(fn(CurrencyModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(Currency $currency): Currency
    {
        $model = $currency->id()
            ? CurrencyModel::findOrFail($currency->id())
            : new CurrencyModel();

        $model->code = $currency->code();
        $model->name = $currency->name();
        $model->symbol = $currency->symbol();
        $model->decimal_places = $currency->decimalPlaces();
        $model->is_active = $currency->isActive();
        $model->save();

        return $this->toDomain($model);
    }

    private function toDomain(CurrencyModel $model): Currency
    {
        return Currency::reconstitute(
            id: $model->id,
            code: $model->code,
            name: $model->name,
            symbol: $model->symbol,
            decimalPlaces: $model->decimal_places,
            isActive: $model->is_active,
            createdAt: new \DateTimeImmutable($model->created_at),
            updatedAt: $model->updated_at ? new \DateTimeImmutable($model->updated_at) : null,
        );
    }
}
