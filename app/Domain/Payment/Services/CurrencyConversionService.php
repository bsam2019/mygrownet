<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyConversionService
{
    private ?string $apiKey;
    private string $baseUrl = 'https://v6.exchangerate-api.com/v6';

    public function __construct()
    {
        $this->apiKey = config('services.exchangerate.api_key');
    }

    public function convert(float $amount, string $from, string $to): ?float
    {
        if ($from === $to) {
            return $amount;
        }

        $rate = $this->getDailyRate($from, $to);

        if ($rate === null) {
            return null;
        }

        return round($amount * $rate, 2);
    }

    public function getExchangeRate(string $from, string $to): ?float
    {
        return $this->getDailyRate($from, $to);
    }

    public function getDailyRate(string $from, string $to): ?float
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return 1.0;
        }

        $today = now()->format('Y-m-d');
        $cacheKey = "daily_rate_{$from}_{$to}_{$today}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $rate = $this->fetchExchangeRate($from, $to);

        if ($rate !== null) {
            $endOfDay = now()->endOfDay()->diffInSeconds(now());
            Cache::put($cacheKey, $rate, $endOfDay);

            Log::info('Daily exchange rate captured', [
                'from' => $from,
                'to' => $to,
                'rate' => $rate,
                'date' => $today,
                'valid_until' => now()->endOfDay()->toDateTimeString(),
            ]);

            return $rate;
        }

        $yesterdayKey = "daily_rate_{$from}_{$to}_" . now()->subDay()->format('Y-m-d');
        if (Cache::has($yesterdayKey)) {
            $yesterdayRate = Cache::get($yesterdayKey);
            Log::warning('Using yesterday exchange rate as fallback', [
                'from' => $from,
                'to' => $to,
                'rate' => $yesterdayRate,
                'date' => $today,
            ]);
            return $yesterdayRate;
        }

        Log::error('Failed to fetch exchange rate and no cached rate available', [
            'from' => $from,
            'to' => $to,
        ]);

        return null;
    }

    public function getAllRates(string $baseCurrency): ?array
    {
        $baseCurrency = strtoupper($baseCurrency);

        $today = now()->format('Y-m-d');
        $cacheKey = "daily_rates_{$baseCurrency}_{$today}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $rates = $this->fetchAllRates($baseCurrency);

        if ($rates !== null) {
            $endOfDay = now()->endOfDay()->diffInSeconds(now());
            Cache::put($cacheKey, $rates, $endOfDay);

            Log::info('Daily exchange rates captured', [
                'base' => $baseCurrency,
                'date' => $today,
                'currencies_count' => count($rates),
                'valid_until' => now()->endOfDay()->toDateTimeString(),
            ]);
        }

        return $rates;
    }

    public function refreshDailyRate(string $from, string $to): ?float
    {
        $today = now()->format('Y-m-d');
        $cacheKey = "daily_rate_{$from}_{$to}_{$today}";

        Cache::forget($cacheKey);

        $rate = $this->fetchExchangeRate($from, $to);
        if ($rate !== null) {
            $endOfDay = now()->endOfDay()->diffInSeconds(now());
            Cache::put($cacheKey, $rate, $endOfDay);
        }

        return $rate;
    }

    public function refreshAllDailyRates(string $baseCurrency = 'USD'): bool
    {
        $today = now()->format('Y-m-d');
        $cacheKey = "daily_rates_{$baseCurrency}_{$today}";

        Cache::forget($cacheKey);

        $rates = $this->fetchAllRates($baseCurrency);
        if ($rates !== null) {
            $endOfDay = now()->endOfDay()->diffInSeconds(now());
            Cache::put($cacheKey, $rates, $endOfDay);

            foreach ($rates as $currency => $rate) {
                $pairKey = "daily_rate_{$baseCurrency}_{$currency}_{$today}";
                Cache::put($pairKey, $rate, $endOfDay);
                $reverseKey = "daily_rate_{$currency}_{$baseCurrency}_{$today}";
                $reverseRate = $rate > 0 ? round(1 / $rate, 6) : 0;
                Cache::put($reverseKey, $reverseRate, $endOfDay);
            }

            Log::info('All daily exchange rates refreshed', [
                'base' => $baseCurrency,
                'date' => $today,
                'currencies_count' => count($rates),
            ]);

            return true;
        }

        return false;
    }

    public function convertBatch(array $conversions): array
    {
        $results = [];

        foreach ($conversions as $index => $conversion) {
            $amount = $conversion['amount'] ?? 0;
            $from = $conversion['from'] ?? 'USD';
            $to = $conversion['to'] ?? 'USD';

            $converted = $this->convert($amount, $from, $to);

            $results[$index] = [
                'original_amount' => $amount,
                'from_currency' => $from,
                'to_currency' => $to,
                'converted_amount' => $converted,
                'success' => $converted !== null,
            ];
        }

        return $results;
    }

    public function getSupportedCurrencies(): array
    {
        $cacheKey = 'supported_currencies';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            if (empty($this->apiKey)) {
                return $this->getDefaultCurrencies();
            }

            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/{$this->apiKey}/codes");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['supported_codes'])) {
                    $currencies = array_column($data['supported_codes'], 0);
                    Cache::put($cacheKey, $currencies, 86400);
                    return $currencies;
                }
            }

            return $this->getDefaultCurrencies();
        } catch (\Exception $e) {
            Log::error('Failed to fetch supported currencies', [
                'error' => $e->getMessage(),
            ]);

            return $this->getDefaultCurrencies();
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function getTodayDate(): string
    {
        return now()->format('Y-m-d');
    }

    public function getDailyRateStatus(string $from, string $to): array
    {
        $from = strtoupper($from);
        $to = strtoupper($to);
        $today = now()->format('Y-m-d');
        $cacheKey = "daily_rate_{$from}_{$to}_{$today}";

        $cached = Cache::has($cacheKey);
        $rate = $cached ? Cache::get($cacheKey) : null;

        return [
            'from' => $from,
            'to' => $to,
            'date' => $today,
            'rate' => $rate,
            'cached' => $cached,
            'api_configured' => $this->isConfigured(),
            'valid_until' => now()->endOfDay()->toDateTimeString(),
        ];
    }

    private function fetchExchangeRate(string $from, string $to): ?float
    {
        try {
            if (empty($this->apiKey)) {
                Log::warning('ExchangeRate API key not configured');
                return null;
            }

            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/{$this->apiKey}/pair/{$from}/{$to}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['conversion_rate'])) {
                    return (float) $data['conversion_rate'];
                }
            }

            Log::error('Failed to fetch exchange rate', [
                'from' => $from,
                'to' => $to,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exchange rate API exception', [
                'from' => $from,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function fetchAllRates(string $baseCurrency): ?array
    {
        try {
            if (empty($this->apiKey)) {
                return null;
            }

            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/{$this->apiKey}/latest/{$baseCurrency}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['conversion_rates'])) {
                    return $data['conversion_rates'];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to fetch all exchange rates', [
                'base' => $baseCurrency,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function getDefaultCurrencies(): array
    {
        return [
            'USD', 'EUR', 'GBP', 'ZMW', 'ZAR', 'KES', 'NGN', 'GHS',
            'JPY', 'CNY', 'INR', 'AUD', 'CAD', 'CHF', 'SEK', 'NZD',
        ];
    }

    public function clearCache(): void
    {
        Log::info('Exchange rate cache cleared');
    }

    public function getCacheStats(): array
    {
        return [
            'cache_duration' => 'end_of_day',
            'cache_enabled' => true,
            'api_configured' => $this->isConfigured(),
        ];
    }
}
