<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InvestmentMetricsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvestmentMetricsController extends Controller
{
    protected $metricsService;

    public function __construct(InvestmentMetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    public function getMetrics(Request $request)
    {
        $period = $request->get('period', 'month');
        $metrics = $this->metricsService->generateMetrics($period);
        
        if ($request->wantsJson()) {
            return response()->json($metrics);
        }

        return back()->with('metrics', $metrics);
    }

    public function show(Request $request)
    {
        return Inertia::render('Admin/Investments/Metrics', [
            'metrics' => $this->metricsService->generateMetrics($request->get('period', 'month'))
        ]);
    }
}
