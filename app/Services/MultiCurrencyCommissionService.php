<?php

namespace App\Services;

use App\Models\User;
use App\Domain\Payment\Services\CurrencyConversionService;
use Illuminate\Support\Facades\Log;

class MultiCurrencyCommissionService
{
    public function __construct(
        private CurrencyConversionService $conversionService
    ) {}

    public function convertCommission(User $recipient, float $amount, string $sourceCurrency): array
    {
        $recipientCurrency = $recipient->user_currency ?? $recipient->preferred_currency ?? 'ZMW';
        
        if ($sourceCurrency === $recipientCurrency) {
            return [
                'amount' => $amount,
                'currency' => $recipientCurrency,
                'original_amount' => $amount,
                'original_currency' => $sourceCurrency,
                'exchange_rate' => 1.0,
                'converted' => false,
            ];
        }
        
        $convertedAmount = $this->conversionService->convert($amount, $sourceCurrency, $recipientCurrency);
        $exchangeRate = $this->conversionService->getExchangeRate($sourceCurrency, $recipientCurrency);
        
        if ($convertedAmount === null || $exchangeRate === null) {
            $fallbackRate = $this->getFallbackRate($sourceCurrency, $recipientCurrency);
            $convertedAmount = round($amount * $fallbackRate, 2);
            
            Log::warning('Using fallback exchange rate for commission', [
                'from' => $sourceCurrency,
                'to' => $recipientCurrency,
                'fallback_rate' => $fallbackRate,
            ]);
            
            return [
                'amount' => $convertedAmount,
                'currency' => $recipientCurrency,
                'original_amount' => $amount,
                'original_currency' => $sourceCurrency,
                'exchange_rate' => $fallbackRate,
                'converted' => true,
                'fallback_used' => true,
            ];
        }
        
        return [
            'amount' => $convertedAmount,
            'currency' => $recipientCurrency,
            'original_amount' => $amount,
            'original_currency' => $sourceCurrency,
            'exchange_rate' => $exchangeRate,
            'converted' => true,
        ];
    }

    private function getFallbackRate(string $from, string $to): float
    {
        // Hardcoded fallback rates (update monthly)
        $rates = [
            'USD_ZMW' => 27.50, // 1 USD = 27.50 ZMW (approximate)
            'ZMW_USD' => 0.036,  // 1 ZMW = 0.036 USD
        ];
        
        $key = "{$from}_{$to}";
        return $rates[$key] ?? 1.0;
    }
}
