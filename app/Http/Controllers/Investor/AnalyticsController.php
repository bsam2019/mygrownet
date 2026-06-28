<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Investor\Concerns\RequiresInvestorAuth;
use App\Domain\Investor\Services\AdvancedAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    use RequiresInvestorAuth;

    public function __construct(
        private AdvancedAnalyticsService $analyticsService
    ) {}

    public function index(Request $request)
    {
        $investor = $this->requireInvestorAuth();
        
        if ($investor instanceof \Illuminate\Http\RedirectResponse) {
            return $investor;
        }

        $investorId = $investor->getId();

        $valuationData = $this->analyticsService->getValuationChartData(24);
        $investorValue = $this->analyticsService->calculateInvestorShareValue($investorId);
        $riskAssessment = $this->analyticsService->getLatestRiskAssessment();
        $scenarios = $this->analyticsService->calculateScenarioForInvestor($investorId);
        $exitProjections = $this->analyticsService->calculateExitValueForInvestor($investorId);

        return Inertia::render('Investor/Analytics', [
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
            'valuationChart' => $valuationData,
            'investorValue' => $investorValue,
            'riskAssessment' => $riskAssessment,
            'scenarios' => $scenarios,
            'exitProjections' => $exitProjections,
            'investmentAmount' => $investor->getInvestmentAmount(),
            'equityPercentage' => $investor->getEquityPercentage(),
            'activePage' => 'analytics',
            'unreadMessages' => 0,
            'unreadAnnouncements' => 0,
        ]);
    }

    public function getValuationHistory(Request $request)
    {
        $months = $request->get('months', 24);
        $data = $this->analyticsService->getValuationChartData($months);
        return response()->json($data);
    }

    public function getRiskHistory()
    {
        $history = $this->analyticsService->getRiskHistory();
        return response()->json($history);
    }

    public function getScenarios(Request $request)
    {
        $investor = $this->getAuthenticatedInvestor();
        
        if (!$investor) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $scenarios = $this->analyticsService->calculateScenarioForInvestor($investor->getId());
        return response()->json($scenarios);
    }

    public function getSummary(Request $request)
    {
        $investor = $this->getAuthenticatedInvestor();
        
        if (!$investor) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $summary = $this->analyticsService->getAnalyticsSummary($investor->getId());
        return response()->json($summary);
    }
}
