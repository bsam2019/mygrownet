<?php

namespace App\Http\Controllers\CMS;

use App\Domain\CMS\Core\Services\CurrencyService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CurrencyController extends Controller
{
    public function __construct(
        private CurrencyService $currencyService
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        
        $currencies = $this->currencyService->getAllCurrencies();
        $baseCurrency = $this->currencyService->getCompanyBaseCurrency($companyId);
        $multiCurrencyEnabled = $this->currencyService->isMultiCurrencyEnabled($companyId);
        $exchangeRates = $this->currencyService->getAllExchangeRates($companyId);

        return Inertia::render('CMS/Settings/Currency', [
            'currencies' => $currencies,
            'baseCurrency' => $baseCurrency,
            'multiCurrencyEnabled' => $multiCurrencyEnabled,
            'exchangeRates' => $exchangeRates,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'base_currency' => 'required|string|size:3',
            'multi_currency_enabled' => 'required|boolean',
        ]);

        $companyId = $request->user()->cmsUser->company_id;

        $this->currencyService->setBaseCurrency($companyId, $validated['base_currency']);
        
        if ($validated['multi_currency_enabled']) {
            $this->currencyService->enableMultiCurrency($companyId);
        } else {
            $this->currencyService->disableMultiCurrency($companyId);
        }

        return back()->with('success', 'Currency settings updated successfully');
    }

    public function setExchangeRate(Request $request)
    {
        $validated = $request->validate([
            'from_currency' => 'required|string|size:3',
            'to_currency' => 'required|string|size:3',
            'rate' => 'required|numeric|min:0',
            'effective_date' => 'nullable|date',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $date = $validated['effective_date'] ? Carbon::parse($validated['effective_date']) : null;

        $this->currencyService->setExchangeRate(
            $companyId,
            $validated['from_currency'],
            $validated['to_currency'],
            $validated['rate'],
            $date,
            'manual'
        );

        return back()->with('success', 'Exchange rate updated successfully');
    }

    public function getExchangeRate(Request $request)
    {
        $validated = $request->validate([
            'from_currency' => 'required|string|size:3',
            'to_currency' => 'required|string|size:3',
            'date' => 'nullable|date',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $date = $validated['date'] ? Carbon::parse($validated['date']) : null;

        $rate = $this->currencyService->getExchangeRate(
            $companyId,
            $validated['from_currency'],
            $validated['to_currency'],
            $date
        );

        return response()->json([
            'rate' => $rate,
            'from_currency' => $validated['from_currency'],
            'to_currency' => $validated['to_currency'],
            'date' => $date?->toDateString() ?? Carbon::today()->toDateString(),
        ]);
    }

    public function convert(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'from_currency' => 'required|string|size:3',
            'to_currency' => 'required|string|size:3',
            'date' => 'nullable|date',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $date = $validated['date'] ? Carbon::parse($validated['date']) : null;

        $convertedAmount = $this->currencyService->convert(
            $validated['amount'],
            $validated['from_currency'],
            $validated['to_currency'],
            $companyId,
            $date
        );

        return response()->json([
            'original_amount' => $validated['amount'],
            'converted_amount' => $convertedAmount,
            'from_currency' => $validated['from_currency'],
            'to_currency' => $validated['to_currency'],
            'rate' => $convertedAmount ? $convertedAmount / $validated['amount'] : null,
        ]);
    }

    public function history(Request $request)
    {
        $validated = $request->validate([
            'from_currency' => 'required|string|size:3',
            'to_currency' => 'required|string|size:3',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $startDate = $validated['start_date'] ? Carbon::parse($validated['start_date']) : null;
        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : null;

        $history = $this->currencyService->getExchangeRateHistory(
            $companyId,
            $validated['from_currency'],
            $validated['to_currency'],
            $startDate,
            $endDate
        );

        return response()->json($history);
    }

    public function fetchLiveRates(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        
        // This would require an API key from a currency service
        // For now, return a message
        return back()->with('info', 'Live rate fetching requires API configuration. Please set rates manually.');
    }
}
