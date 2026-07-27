<?php

namespace App\Domain\FinancialServicesCore\Contracts;

use App\Domain\Core\Contracts\ProviderContract;

interface ExchangeRateProvider extends ProviderContract
{
    public function fetchRates(string $baseCurrency): array;

    public function historicalRates(string $from, string $to, \DateTimeImmutable $fromDate, \DateTimeImmutable $toDate): array;
}
