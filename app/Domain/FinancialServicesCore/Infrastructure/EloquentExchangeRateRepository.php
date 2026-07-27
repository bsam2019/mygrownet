<?php

namespace App\Domain\FinancialServicesCore\Infrastructure;

use App\Domain\FinancialServicesCore\Entities\ExchangeRate;
use App\Domain\FinancialServicesCore\Repositories\ExchangeRateRepositoryInterface;

class EloquentExchangeRateRepository implements ExchangeRateRepositoryInterface
{
    public function findRate(string $from, string $to, \DateTimeImmutable $date): ?ExchangeRate
    {
        $model = ExchangeRateModel::where('from_currency', strtoupper($from))
            ->where('to_currency', strtoupper($to))
            ->where('date', $date->format('Y-m-d'))
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findRates(string $from, string $to, \DateTimeImmutable $fromDate, \DateTimeImmutable $toDate): array
    {
        return ExchangeRateModel::where('from_currency', strtoupper($from))
            ->where('to_currency', strtoupper($to))
            ->whereBetween('date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get()
            ->map(fn(ExchangeRateModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findLatestRate(string $from, string $to): ?ExchangeRate
    {
        $model = ExchangeRateModel::where('from_currency', strtoupper($from))
            ->where('to_currency', strtoupper($to))
            ->orderBy('date', 'desc')
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function save(ExchangeRate $rate): ExchangeRate
    {
        $data = $rate->toArray();

        $model = $rate->id()
            ? ExchangeRateModel::findOrFail($rate->id())
            : new ExchangeRateModel();

        $model->from_currency = $data['from_currency'];
        $model->to_currency = $data['to_currency'];
        $model->rate = $data['rate'];
        $model->date = $data['date'];
        $model->source = $data['source'];
        $model->save();

        return $this->toDomain($model);
    }

    public function saveMany(ExchangeRate ...$rates): void
    {
        foreach ($rates as $rate) {
            $this->save($rate);
        }
    }

    private function toDomain(ExchangeRateModel $model): ExchangeRate
    {
        return ExchangeRate::reconstitute(
            id: $model->id,
            fromCurrency: $model->from_currency,
            toCurrency: $model->to_currency,
            rate: (float) $model->rate,
            date: new \DateTimeImmutable($model->date),
            source: $model->source,
            createdAt: new \DateTimeImmutable($model->created_at),
            updatedAt: $model->updated_at ? new \DateTimeImmutable($model->updated_at) : null,
        );
    }
}
