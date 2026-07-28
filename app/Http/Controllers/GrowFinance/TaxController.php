<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\TaxEngine;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceExpenseModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceVendorModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaxController extends Controller
{
    public function __construct(
        private TaxEngine $taxEngine,
    ) {}

    public function vatReturn(Request $request): Response
    {
        $businessId = $request->user()->id;
        $periodStart = $request->get('period_start', now()->startOfMonth()->format('Y-m-d'));
        $periodEnd = $request->get('period_end', now()->endOfMonth()->format('Y-m-d'));

        $this->taxEngine->seedDefaultRates($businessId);
        $data = $this->taxEngine->getVatReturn($businessId, $periodStart, $periodEnd);

        return Inertia::render('GrowFinance/Reports/VatReturn', [
            'data' => $data,
            'saved_returns' => $this->taxEngine->getSavedReturns($businessId, 'vat'),
        ]);
    }

    public function withholdingSchedule(Request $request): Response
    {
        $businessId = $request->user()->id;
        $periodStart = $request->get('period_start', now()->startOfMonth()->format('Y-m-d'));
        $periodEnd = $request->get('period_end', now()->endOfMonth()->format('Y-m-d'));

        $this->taxEngine->seedDefaultRates($businessId);
        $data = $this->taxEngine->getWithholdingSchedule($businessId, $periodStart, $periodEnd);

        // Enrich with vendor names
        $vendorIds = array_unique(array_filter(
            GrowFinanceExpenseModel::forBusiness($businessId)
                ->whereBetween('expense_date', [$periodStart, $periodEnd])
                ->pluck('vendor_id')
                ->toArray()
        ));

        $vendors = GrowFinanceVendorModel::whereIn('id', $vendorIds)->pluck('name', 'id');

        foreach ($data['items'] as &$item) {
            $expense = GrowFinanceExpenseModel::forBusiness($businessId)
                ->where('expense_date', $item['date'])
                ->where('description', $item['description'])
                ->first();
            if ($expense && $expense->vendor_id && isset($vendors[$expense->vendor_id])) {
                $item['vendor_name'] = $vendors[$expense->vendor_id];
            }
        }

        return Inertia::render('GrowFinance/Reports/WithholdingSchedule', [
            'data' => $data,
            'saved_returns' => $this->taxEngine->getSavedReturns($businessId, 'withholding'),
        ]);
    }

    public function saveReturn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'return_type' => 'required|in:vat,withholding',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $this->taxEngine->saveTaxReturn(
            $request->user()->id,
            $validated['return_type'],
            $validated['period_start'],
            $validated['period_end'],
        );

        return back()->with('success', 'Tax return saved successfully!');
    }
}
