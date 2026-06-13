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

        $rate = $this->conversionService->getDailyRate($sourceCurrency, $recipientCurrency);

        if ($rate === null) {
            Log::error('Commission conversion failed - no exchange rate available', [
                'from' => $sourceCurrency,
                'to' => $recipientCurrency,
                'amount' => $amount,
                'recipient_id' => $recipient->id,
            ]);

            return [
                'amount' => $amount,
                'currency' => $sourceCurrency,
                'original_amount' => $amount,
                'original_currency' => $sourceCurrency,
                'exchange_rate' => 1.0,
                'converted' => false,
                'error' => 'No exchange rate available',
            ];
        }

        $convertedAmount = round($amount * $rate, 2);

        Log::info('Commission converted using daily rate', [
            'from' => $sourceCurrency,
            'to' => $recipientCurrency,
            'original_amount' => $amount,
            'converted_amount' => $convertedAmount,
            'rate' => $rate,
            'date' => $this->conversionService->getTodayDate(),
        ]);

        return [
            'amount' => $convertedAmount,
            'currency' => $recipientCurrency,
            'original_amount' => $amount,
            'original_currency' => $sourceCurrency,
            'exchange_rate' => $rate,
            'converted' => true,
        ];
    }
}
