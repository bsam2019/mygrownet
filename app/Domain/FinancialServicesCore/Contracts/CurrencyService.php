<?php

namespace App\Domain\FinancialServicesCore\Contracts;

use App\Domain\Core\Contracts\ProviderContract;

interface CurrencyService extends ProviderContract
{
    public function convert(float $amount, string $from, string $to, ?\DateTimeImmutable $date = null): float;

    public function getRate(string $from, string $to, ?\DateTimeImmutable $date = null): float;

    public function supportedCurrencies(): array;
}
