<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\AnomalyDetectionService;
use App\Domain\GrowFinance\Services\AutomatedRecommendationService;
use App\Domain\GrowFinance\Services\CashFlowForecastService;
use App\Domain\GrowFinance\Services\DashboardWidgetService;
use App\Domain\GrowFinance\Services\FinancialRatioService;
use App\Domain\GrowFinance\Services\RevenuePredictionService;
use App\Http\Controllers\Controller;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnalyticsDashboardController extends Controller
{
    public function __construct(
        private DashboardWidgetService $widgetService,
        private FinancialRatioService $ratioService,
        private AnomalyDetectionService $anomalyService,
        private CashFlowForecastService $forecastService,
        private RevenuePredictionService $predictionService,
        private AutomatedRecommendationService $recommendationService,
    ) {}

    public function index(Request $request)
    {
        $businessId = $request->user()->id;
        $now = new DateTimeImmutable('now');
        $yearStart = new DateTimeImmutable('first day of January this year');

        $widgets = $this->widgetService->getAll($businessId, $now);
        $ratios = $this->ratioService->getAllRatios($businessId, $yearStart, $now);
        $anomalies = $this->anomalyService->runAll($businessId, new DateTimeImmutable('-30 days'), $now);
        $forecast = $this->forecastService->forecast($businessId, 6);
        $predictions = $this->predictionService->predictPnl($businessId, 6);
        $recommendations = $this->recommendationService->generateAll($businessId);

        return Inertia::render('GrowFinance/Dashboard/Analytics', [
            'widgets' => $widgets,
            'ratios' => $ratios,
            'anomalies' => $anomalies['summary'],
            'forecast' => $forecast,
            'predictions' => $predictions,
            'recommendations' => $recommendations,
        ]);
    }
}
