<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Currency Conversion Service
 * 
 * Uses ExchangeRate-API for real-time currency conversion
 * API: https://www.exchangerate-api.com
 * 
 * Features:
 * - Real-time exchange rates
 * - 1,500 requests/month free tier
 * - Caching to minimize API calls
 * - Support for 160+ currencies
 */
class CurrencyConversionService
{
    private ?string $apiKey;
    private string $baseUrl = 'https://v6.exchangerate-api.com/v6';
    private int $cacheDuration = 3600; // 1 hour cache

    public function __construct()
    {
        $this->apiKey = config('services.exchangerate.api_key');
    }

    /**
     * Convert amount from one currency to another
     * 
     * @param float $amount Amount to convert
     * @param string $from Source currency code (e.g., 'ZMW')
     * @param string $to Target currency code (e.g., 'USD')
     * @return float|null Converted amount or null on failure
     */
    public function convert(float $amount, string $from, string $to): ?float
    {
        if ($from === $to) {
            return $amount;
        }

        $rate = $this->getExchangeRate($from, $to);
        
        if ($rate === null) {
            return null;
        }

        return round($amount * $rate, 2);
    }

    /**
     * Get exchange rate between two currencies
     * 
     * @param string $from Source currency
     * @param string $to Target currency
     * @return float|null Exchange rate or null on failure
     */
    public function getExchangeRate(string $from, string $to): ?float
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return 1.0;
        }

        // Check cache first
        $cacheKey = "exchange_rate_{$from}_{$to}";
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Fetch from API
        $rate = $this->fetchExchangeRate($from, $to);
        
        if ($rate !== null) {
            // Cache for 1 hour
            Cache::put($cacheKey, $rate, $this->cacheDuration);
        }

        return $rate;
    }

    /**
     * Get all exchange rates for a base currency
     * 
     * @param string $baseCurrency Base currency code
     * @return array|null Array of rates or null on failure
     */
    public function getAllRates(string $baseCurrency): ?array
    {
        $baseCurrency = strtoupper($baseCurrency);
        
        // Check cache
        $cacheKey = "exchange_rates_{$baseCurrency}";
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Fetch from API
        $rates = $this->fetchAllRates($baseCurrency);
        
        if ($rates !== null) {
            // Cache for 1 hour
            Cache::put($cacheKey, $rates, $this->cacheDuration);
        }

        return $rates;
    }

    /**
     * Convert multiple amounts at once
     * 
     * @param array $conversions Array of ['amount' => float, 'from' => string, 'to' => string]
     * @return array Results with converted amounts
     */
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

    /**
     * Get supported currencies
     * 
     * @return array List of supported currency codes
     */
    public function getSupportedCurrencies(): array
    {
        // Check cache
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
                    
                    // Cache for 24 hours (currencies don't change often)
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

    /**
     * Check if API is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Fetch exchange rate from API
     */
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
                    Log::info('Exchange rate fetched', [
                        'from' => $from,
                        'to' => $to,
                        'rate' => $data['conversion_rate'],
                    ]);
                    
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

    /**
     * Fetch all exchange rates for a base currency
     */
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

    /**
     * Get default currencies when API is not available
     */
    private function getDefaultCurrencies(): array
    {
        return [
            'USD', 'EUR', 'GBP', 'ZMW', 'ZAR', 'KES', 'NGN', 'GHS',
            'JPY', 'CNY', 'INR', 'AUD', 'CAD', 'CHF', 'SEK', 'NZD',
        ];
    }

    /**
     * Clear exchange rate cache
     */
    public function clearCache(): void
    {
        Cache::flush();
        Log::info('Exchange rate cache cleared');
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        // This is a simple implementation
        // You can enhance it to track actual cache hits/misses
        return [
            'cache_duration' => $this->cacheDuration,
            'cache_enabled' => true,
        ];
    }
}
