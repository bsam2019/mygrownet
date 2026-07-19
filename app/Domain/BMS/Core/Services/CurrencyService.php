<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\CurrencyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ExchangeRateModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    public function getAllCurrencies(): array
    {
        return CurrencyModel::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->toArray();
    }

    public function getCurrency(string $code): ?CurrencyModel
    {
        return CurrencyModel::where('code', $code)->first();
    }

    public function getCompanyBaseCurrency(int $companyId): string
    {
        $company = CompanyModel::find($companyId);
        return $company->base_currency ?? 'ZMW';
    }

    public function isMultiCurrencyEnabled(int $companyId): bool
    {
        $company = CompanyModel::find($companyId);
        return $company->multi_currency_enabled ?? false;
    }

    public function enableMultiCurrency(int $companyId): void
    {
        CompanyModel::where('id', $companyId)->update([
            'multi_currency_enabled' => true,
        ]);
    }

    public function disableMultiCurrency(int $companyId): void
    {
        CompanyModel::where('id', $companyId)->update([
            'multi_currency_enabled' => false,
        ]);
    }

    public function setBaseCurrency(int $companyId, string $currencyCode): void
    {
        CompanyModel::where('id', $companyId)->update([
            'base_currency' => $currencyCode,
        ]);
    }

    public function getExchangeRate(
        int $companyId,
        string $fromCurrency,
        string $toCurrency,
        ?Carbon $date = null
    ): ?float {
        // Same currency, rate is 1
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $date = $date ?? Carbon::today();

        // Get the most recent rate on or before the specified date
        $rate = ExchangeRateModel::where('company_id', $companyId)
            ->where('from_currency', $fromCurrency)
            ->where('to_currency', $toCurrency)
            ->where('effective_date', '<=', $date)
            ->orderBy('effective_date', 'desc')
            ->first();

        return $rate ? (float)$rate->rate : null;
    }

    public function setExchangeRate(
        int $companyId,
        string $fromCurrency,
        string $toCurrency,
        float $rate,
        ?Carbon $date = null,
        string $source = 'manual'
    ): ExchangeRateModel {
        $date = $date ?? Carbon::today();

        return ExchangeRateModel::updateOrCreate(
            [
                'company_id' => $companyId,
                'from_currency' => $fromCurrency,
                'to_currency' => $toCurrency,
                'effective_date' => $date,
            ],
            [
                'rate' => $rate,
                'source' => $source,
            ]
        );
    }

    public function convert(
        float $amount,
        string $fromCurrency,
        string $toCurrency,
        int $companyId,
        ?Carbon $date = null
    ): ?float {
        $rate = $this->getExchangeRate($companyId, $fromCurrency, $toCurrency, $date);
        
        if ($rate === null) {
            return null;
        }

        return $amount * $rate;
    }

    public function convertToBaseCurrency(
        float $amount,
        string $fromCurrency,
        int $companyId,
        ?Carbon $date = null
    ): ?float {
        $baseCurrency = $this->getCompanyBaseCurrency($companyId);
        return $this->convert($amount, $fromCurrency, $baseCurrency, $companyId, $date);
    }

    public function getExchangeRateHistory(
        int $companyId,
        string $fromCurrency,
        string $toCurrency,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $query = ExchangeRateModel::where('company_id', $companyId)
            ->where('from_currency', $fromCurrency)
            ->where('to_currency', $toCurrency);

        if ($startDate) {
            $query->where('effective_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('effective_date', '<=', $endDate);
        }

        return $query->orderBy('effective_date', 'desc')
            ->get()
            ->toArray();
    }

    public function getAllExchangeRates(int $companyId): array
    {
        $baseCurrency = $this->getCompanyBaseCurrency($companyId);
        
        // Get latest rates for all currencies to base currency
        $rates = ExchangeRateModel::where('company_id', $companyId)
            ->where('to_currency', $baseCurrency)
            ->whereIn('id', function ($query) use ($companyId, $baseCurrency) {
                $query->selectRaw('MAX(id)')
                    ->from('cms_exchange_rates')
                    ->where('company_id', $companyId)
                    ->where('to_currency', $baseCurrency)
                    ->groupBy('from_currency');
            })
            ->with(['company'])
            ->get()
            ->toArray();

        return $rates;
    }

    public function formatAmount(float $amount, string $currencyCode): string
    {
        $currency = $this->getCurrency($currencyCode);
        
        if (!$currency) {
            return number_format($amount, 2);
        }

        return $currency->formatAmount($amount);
    }

    public function fetchLiveRates(int $companyId, string $apiKey = null): array
    {
        // This would integrate with a currency API like exchangerate-api.com or fixer.io
        // For now, return empty array - implement when API key is available
        
        // Example implementation:
        // $baseCurrency = $this->getCompanyBaseCurrency($companyId);
        // $response = Http::get("https://api.exchangerate-api.com/v4/latest/{$baseCurrency}");
        // $rates = $response->json()['rates'];
        // 
        // foreach ($rates as $currency => $rate) {
        //     $this->setExchangeRate($companyId, $baseCurrency, $currency, $rate, null, 'api');
        // }
        
        return [];
    }
}
