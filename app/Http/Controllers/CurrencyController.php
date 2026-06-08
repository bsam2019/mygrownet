<?php

namespace App\Http\Controllers;

use App\Domain\Payment\Services\CurrencyDetectionService;
use App\Domain\Payment\Services\CurrencyConversionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    public function __construct(
        private CurrencyDetectionService $detectionService,
        private CurrencyConversionService $conversionService
    ) {}

    /**
     * Get user's detected currency
     */
    public function detect(Request $request): JsonResponse
    {
        $currency = $this->detectionService->detectCurrency(
            $request->ip(),
            $request->user()?->id
        );

        $info = $this->detectionService->getCurrencyInfo($currency);

        return response()->json([
            'currency' => $currency,
            'info' => $info,
        ]);
    }

    /**
     * Get popular currencies for selection
     */
    public function popular(): JsonResponse
    {
        $currencies = $this->detectionService->getPopularCurrencies();

        return response()->json([
            'currencies' => $currencies,
        ]);
    }

    /**
     * Set user's preferred currency
     */
    public function setCurrency(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'currency' => 'required|string|size:3',
        ]);

        $currency = strtoupper($validated['currency']);

        // Save to session
        session(['currency' => $currency]);

        // Save to user profile if logged in
        if ($request->user()) {
            $this->detectionService->saveCurrencyPreference(
                $request->user()->id,
                $currency
            );
        }

        return response()->json([
            'success' => true,
            'currency' => $currency,
            'message' => 'Currency preference saved',
        ]);
    }

    /**
     * Convert amount between currencies
     */
    public function convert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
        ]);

        $convertedAmount = $this->conversionService->convert(
            $validated['amount'],
            $validated['from'],
            $validated['to']
        );

        if ($convertedAmount === null) {
            return response()->json([
                'success' => false,
                'message' => 'Currency conversion failed',
            ], 400);
        }

        $rate = $this->conversionService->getExchangeRate(
            $validated['from'],
            $validated['to']
        );

        return response()->json([
            'success' => true,
            'original_amount' => $validated['amount'],
            'original_currency' => $validated['from'],
            'converted_amount' => $convertedAmount,
            'converted_currency' => $validated['to'],
            'exchange_rate' => $rate,
        ]);
    }

    /**
     * Get exchange rate
     */
    public function rate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
        ]);

        $rate = $this->conversionService->getExchangeRate(
            $validated['from'],
            $validated['to']
        );

        if ($rate === null) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get exchange rate',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'from' => $validated['from'],
            'to' => $validated['to'],
            'rate' => $rate,
        ]);
    }
}
