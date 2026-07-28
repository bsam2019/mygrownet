<?php

declare(strict_types=1);

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\CashFlowForecastService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashFlowForecastController extends Controller
{
    public function __construct(
        private CashFlowForecastService $forecastService,
    ) {}

    public function index(Request $request)
    {
        $businessId = $request->user()->id;
        $monthsAhead = (int)($request->input('months', 6));
        $forecast = $this->forecastService->forecast($businessId, $monthsAhead);

        return Inertia::render('GrowFinance/Reports/CashFlowForecast', [
            'forecast' => $forecast,
            'monthsAhead' => $monthsAhead,
        ]);
    }
}
