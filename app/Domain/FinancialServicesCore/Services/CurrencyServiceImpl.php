<?php

namespace App\Domain\FinancialServicesCore\Services;

use App\Domain\FinancialServicesCore\Contracts\CurrencyService;
use App\Domain\FinancialServicesCore\Exceptions\FinancialServicesCoreException;
use App\Domain\FinancialServicesCore\Repositories\CurrencyRepositoryInterface;
use App\Domain\FinancialServicesCore\Repositories\ExchangeRateRepositoryInterface;

class CurrencyServiceImpl implements CurrencyService
{
    public function __construct(
        private readonly CurrencyRepositoryInterface $currencies,
        private readonly ExchangeRateRepositoryInterface $rates,
    ) {}

    public function capability(): string
    {
        return 'currency_conversion';
    }

    public function convert(float $amount, string $from, string $to, ?\DateTimeImmutable $date = null): float
    {
        if (strtoupper($from) === strtoupper($to)) {
            return $amount;
        }

        $rate = $this->resolveRate($from, $to, $date);

        return round($amount * $rate, 2);
    }

    public function getRate(string $from, string $to, ?\DateTimeImmutable $date = null): float
    {
        return $this->resolveRate($from, $to, $date);
    }

    public function supportedCurrencies(): array
    {
        return array_map(
            fn($c) => ['code' => $c->code(), 'name' => $c->name(), 'symbol' => $c->symbol()],
            $this->currencies->findActive(),
        );
    }

    private function resolveRate(string $from, string $to, ?\DateTimeImmutable $date = null): float
    {
        $codeFrom = strtoupper($from);
        $codeTo = strtoupper($to);

        $currencyFrom = $this->currencies->findByCode($codeFrom);
        $currencyTo = $this->currencies->findByCode($codeTo);

        if (!$currencyFrom || !$currencyFrom->isActive()) {
            throw FinancialServicesCoreException::unsupportedCurrency($codeFrom);
        }
        if (!$currencyTo || !$currencyTo->isActive()) {
            throw FinancialServicesCoreException::unsupportedCurrency($codeTo);
        }

        if ($date) {
            $rate = $this->rates->findRate($codeFrom, $codeTo, $date);
        } else {
            $rate = $this->rates->findLatestRate($codeFrom, $codeTo);
        }

        if (!$rate) {
            throw FinancialServicesCoreException::rateNotFound($codeFrom, $codeTo, $date);
        }

        return $rate->rate();
    }
}
