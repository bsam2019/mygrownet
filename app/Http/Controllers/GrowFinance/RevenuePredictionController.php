<?php

declare(strict_types=1);

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\RevenuePredictionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RevenuePredictionController extends Controller
{
    public function __construct(
        private RevenuePredictionService $predictionService,
    ) {}

    public function index(Request $request)
    {
        $businessId = $request->user()->id;
        $monthsAhead = (int)($request->input('months', 6));
        $prediction = $this->predictionService->predictPnl($businessId, $monthsAhead);

        return Inertia::render('GrowFinance/Reports/RevenuePrediction', [
            'prediction' => $prediction,
            'monthsAhead' => $monthsAhead,
        ]);
    }
}
