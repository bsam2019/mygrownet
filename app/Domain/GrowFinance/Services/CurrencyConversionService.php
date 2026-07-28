<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\Core\Services\IntegrationRegistry;
use App\Domain\FinancialServicesCore\Contracts\CurrencyService;
use DateTimeImmutable;

class CurrencyConversionService
{
    private ?CurrencyService $currencyService = null;

    public function __construct(
        private IntegrationRegistry $registry,
    ) {}

    private function resolveService(): ?CurrencyService
    {
        if ($this->currencyService !== null) {
            return $this->currencyService;
        }

        try {
            $resolved = $this->registry->resolveFor('currency_conversion');
            if ($resolved instanceof CurrencyService) {
                $this->currencyService = $resolved;
            }
        } catch (\Throwable) {
            return null;
        }

        return $this->currencyService;
    }

    public function convert(float $amount, string $from, string $to, ?DateTimeImmutable $date = null): float
    {
        $service = $this->resolveService();
        if ($service === null) {
            return $amount;
        }

        if (strtoupper($from) === strtoupper($to)) {
            return $amount;
        }

        try {
            return $service->convert($amount, $from, $to, $date);
        } catch (\Throwable) {
            return $amount;
        }
    }

    public function getRate(string $from, string $to, ?DateTimeImmutable $date = null): float
    {
        if (strtoupper($from) === strtoupper($to)) {
            return 1.0;
        }

        $service = $this->resolveService();
        if ($service === null) {
            return 1.0;
        }

        try {
            return $service->getRate($from, $to, $date);
        } catch (\Throwable) {
            return 1.0;
        }
    }

    public function toFunctional(float $amount, string $fromCurrency, float $rate): float
    {
        if (strtoupper($fromCurrency) === 'ZMW') {
            return $amount;
        }
        return round($amount * $rate, 2);
    }

    public function computeFunctionalAmounts(
        float $debitAmount,
        float $creditAmount,
        string $currencyCode,
        float $exchangeRate,
    ): array {
        if (strtoupper($currencyCode) === 'ZMW' || abs($exchangeRate - 1.0) < 0.0001) {
            return [
                'functional_debit' => $debitAmount,
                'functional_credit' => $creditAmount,
            ];
        }
        return [
            'functional_debit' => round($debitAmount * $exchangeRate, 2),
            'functional_credit' => round($creditAmount * $exchangeRate, 2),
        ];
    }
}
