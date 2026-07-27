<?php

namespace App\Domain\FinancialServicesCore\Services;

use App\Domain\Core\Contracts\IntegrationEventDispatcher;
use App\Domain\FinancialServicesCore\Contracts\ExchangeRateProvider;
use App\Domain\FinancialServicesCore\Entities\ExchangeRate;
use App\Domain\FinancialServicesCore\Events\FxRateUpdated;
use App\Domain\FinancialServicesCore\Exceptions\FinancialServicesCoreException;
use App\Domain\FinancialServicesCore\Repositories\ExchangeRateRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateProviderImpl implements ExchangeRateProvider
{
    private const BANK_OF_ZAMBIA_URL = 'https://www.boz.zm/api/exchange-rates.json';

    public function __construct(
        private readonly ExchangeRateRepositoryInterface $rates,
        private readonly IntegrationEventDispatcher $events,
        private readonly string $fallbackUrl = 'https://api.exchangerate.host/latest',
    ) {}

    public function capability(): string
    {
        return 'exchange_rate_fetching';
    }

    public function fetchRates(string $baseCurrency): array
    {
        $base = strtoupper($baseCurrency);

        try {
            $response = Http::timeout(10)->get(self::BANK_OF_ZAMBIA_URL);

            if ($response->successful()) {
                return $this->parseBankOfZambiaRates($response->json(), $base);
            }

            throw new \RuntimeException('Bank of Zambia API returned status: ' . $response->status());
        } catch (\Throwable $e) {
            Log::warning('Bank of Zambia rate fetch failed, using fallback', [
                'error' => $e->getMessage(),
                'base' => $base,
            ]);

            return $this->fetchFallbackRates($base);
        }
    }

    public function historicalRates(string $from, string $to, \DateTimeImmutable $fromDate, \DateTimeImmutable $toDate): array
    {
        return array_map(
            fn(ExchangeRate $r) => $r->toArray(),
            $this->rates->findRates($from, $to, $fromDate, $toDate),
        );
    }

    private function parseBankOfZambiaRates(array $data, string $baseCurrency): array
    {
        $fetched = [];
        $today = new \DateTimeImmutable();

        foreach ($data['rates'] ?? [] as $currency => $rate) {
            $rateEntity = ExchangeRate::create(
                fromCurrency: $baseCurrency,
                toCurrency: $currency,
                rate: (float) $rate,
                date: $today,
                source: 'boz',
            );

            $this->rates->save($rateEntity);
            $this->events->dispatch(new FxRateUpdated(
                fromCurrency: $baseCurrency,
                toCurrency: $currency,
                rate: (float) $rate,
                source: 'boz',
                date: $today,
            ));
            $fetched[] = $rateEntity;
        }

        return $fetched;
    }

    private function fetchFallbackRates(string $base): array
    {
        $response = Http::timeout(10)->get($this->fallbackUrl, [
            'base' => $base,
            'access_key' => config('services.exchange_rate_api_key', ''),
        ]);

        if (!$response->successful()) {
            throw FinancialServicesCoreException::rateFetchFailed($base, 'All rate sources unavailable');
        }

        $data = $response->json();
        $fetched = [];
        $today = new \DateTimeImmutable();

        foreach ($data['rates'] ?? [] as $currency => $rate) {
            $rateEntity = ExchangeRate::create(
                fromCurrency: $base,
                toCurrency: $currency,
                rate: (float) $rate,
                date: $today,
                source: 'exchangerate_host',
            );

            $this->rates->save($rateEntity);
            $this->events->dispatch(new FxRateUpdated(
                fromCurrency: $base,
                toCurrency: $currency,
                rate: (float) $rate,
                source: 'exchangerate_host',
                date: $today,
            ));
            $fetched[] = $rateEntity;
        }

        return $fetched;
    }
}
