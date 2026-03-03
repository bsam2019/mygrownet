<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BalanceSheetService;
use App\Services\CashFlowStatementService;
use App\Services\ProfitLossTrackingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    public function __construct(
        private BalanceSheetService $balanceSheetService,
        private CashFlowStatementService $cashFlowService,
        private ProfitLossTrackingService $profitLossService
    ) {}

    /**
     * Show balance sheet report
     */
    public function balanceSheet(Request $request): Response
    {
        $asOfDate = $request->query('as_of_date') 
            ? Carbon::parse($request->query('as_of_date'))
            : now();
        
        $balanceSheet = $this->balanceSheetService->getBalanceSheet($asOfDate);
        $summary = $this->balanceSheetService->getSummary($asOfDate);
        
        return Inertia::render('Admin/Financial/BalanceSheet', [
            'balanceSheet' => $balanceSheet,
            'summary' => $summary,
            'asOfDate' => $asOfDate->format('Y-m-d'),
        ]);
    }

    /**
     * Show cash flow statement
     */
    public function cashFlow(Request $request): Response
    {
        $period = $request->query('period', 'month');
        $customStartDate = $request->query('start_date');
        $customEndDate = $request->query('end_date');
        
        if ($period === 'custom' && $customStartDate && $customEndDate) {
            $startDate = Carbon::parse($customStartDate);
            $endDate = Carbon::parse($customEndDate);
        } else {
            $startDate = $this->getPeriodStartDate($period);
            $endDate = now();
        }
        
        $cashFlow = $this->cashFlowService->getCashFlowStatement($startDate, $endDate);
        $summary = $this->cashFlowService->getSummary($startDate, $endDate);
        
        return Inertia::render('Admin/Financial/CashFlow', [
            'cashFlow' => $cashFlow,
            'summary' => $summary,
            'period' => $period,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }

    /**
     * Financial dashboard overview
     */
    public function dashboard(Request $request): Response
    {
        $period = $request->query('period', 'month');
        
        // Get P&L
        $profitLoss = $this->profitLossService->getProfitLossStatement($period);
        
        // Get Balance Sheet
        $balanceSheet = $this->balanceSheetService->getSummary();
        
        // Get Cash Flow
        $startDate = $this->getPeriodStartDate($period);
        $cashFlow = $this->cashFlowService->getSummary($startDate, now());
        
        return Inertia::render('Admin/Financial/Dashboard', [
            'profitLoss' => $profitLoss,
            'balanceSheet' => $balanceSheet,
            'cashFlow' => $cashFlow,
            'period' => $period,
        ]);
    }

    /**
     * Get period start date
     */
    private function getPeriodStartDate(string $period): Carbon
    {
        return match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };
    }
}
