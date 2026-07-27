<?php

namespace App\Domain\FinancialServicesCore\Repositories;

use App\Domain\FinancialServicesCore\Entities\ExchangeRate;

interface ExchangeRateRepositoryInterface
{
    public function findRate(string $from, string $to, \DateTimeImmutable $date): ?ExchangeRate;

    public function findRates(string $from, string $to, \DateTimeImmutable $fromDate, \DateTimeImmutable $toDate): array;

    public function findLatestRate(string $from, string $to): ?ExchangeRate;

    public function save(ExchangeRate $rate): ExchangeRate;

    public function saveMany(ExchangeRate ...$rates): void;
}
