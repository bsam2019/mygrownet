<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PdfFinancialReportService
{
    public function __construct(
        private ProfitLossTrackingService $plService,
        private BudgetComparisonService $budgetService
    ) {}
    
    /**
     * Generate Profit & Loss PDF report
     */
    public function generateProfitLossReport(
        string $period = 'month',
        ?string $customStartDate = null,
        ?string $customEndDate = null,
        ?int $moduleId = null
    ) {
        $statement = $this->plService->getProfitLossStatement($period, $customStartDate, $customEndDate, $moduleId);
        $commissionEfficiency = $this->plService->getCommissionEfficiency($period, $customStartDate, $customEndDate, $moduleId);
        $cashFlow = $this->plService->getCashFlowAnalysis($period, $customStartDate, $customEndDate, $moduleId);
        
        $data = [
            'statement' => $statement,
            'commissionEfficiency' => $commissionEfficiency,
            'cashFlow' => $cashFlow,
            'period' => $period,
            'generatedAt' => Carbon::now()->format('F d, Y h:i A'),
        ];
        
        return Pdf::loadView('pdf.financial.profit-loss', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-right', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10);
    }
    
    /**
     * Generate Budget Comparison PDF report
     */
    public function generateBudgetComparisonReport(
        string $period = 'month',
        ?string $customStartDate = null,
        ?string $customEndDate = null,
        ?int $moduleId = null
    ) {
        $comparison = $this->budgetService->compareActualVsBudget($period, $customStartDate, $customEndDate, $moduleId);
        $metrics = $this->budgetService->getBudgetPerformanceMetrics($period, $customStartDate, $customEndDate, $moduleId);
        
        $data = [
            'comparison' => $comparison,
            'metrics' => $metrics,
            'period' => $period,
            'generatedAt' => Carbon::now()->format('F d, Y h:i A'),
        ];
        
        return Pdf::loadView('pdf.financial.budget-comparison', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-right', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10);
    }
}
