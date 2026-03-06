<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BudgetComparisonService;
use App\Services\PdfFinancialReportService;
use App\Models\Module;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function __construct(
        private BudgetComparisonService $budgetService,
        private PdfFinancialReportService $pdfService
    ) {}
    
    /**
     * Display budget dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $moduleId = $request->get('module_id');
        
        $comparison = $this->budgetService->compareActualVsBudget($period, null, null, $moduleId);
        $metrics = $this->budgetService->getBudgetPerformanceMetrics($period, null, null, $moduleId);
        
        // Get modules list
        $modules = Module::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->toArray();
        
        return Inertia::render('Admin/Financial/BudgetDashboard', [
            'comparison' => $comparison,
            'metrics' => $metrics,
            'selectedPeriod' => $period,
            'modules' => $modules,
            'selectedModuleId' => $moduleId,
        ]);
    }
    
    /**
     * Get budget comparison data (API)
     */
    public function getComparison(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $moduleId = $request->get('module_id');
        
        return response()->json([
            'comparison' => $this->budgetService->compareActualVsBudget($period, $startDate, $endDate, $moduleId),
            'metrics' => $this->budgetService->getBudgetPerformanceMetrics($period, $startDate, $endDate, $moduleId),
        ]);
    }
    
    /**
     * Get budget trends (API)
     */
    public function getTrends(Request $request)
    {
        $period = $request->get('period', 'month');
        $periods = $request->get('periods', 6);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $moduleId = $request->get('module_id');
        
        return response()->json([
            'trends' => $this->budgetService->getBudgetTrends($period, $periods, $startDate, $endDate, $moduleId),
        ]);
    }
    
    /**
     * Get budget performance metrics (API)
     */
    public function getMetrics(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $moduleId = $request->get('module_id');
        
        return response()->json([
            'metrics' => $this->budgetService->getBudgetPerformanceMetrics($period, $startDate, $endDate, $moduleId),
        ]);
    }
    
    /**
     * Export budget comparison report as PDF
     */
    public function exportPdf(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $moduleId = $request->get('module_id');
        
        try {
            $pdf = $this->pdfService->generateBudgetComparisonReport($period, $startDate, $endDate, $moduleId);
            
            $filename = 'Budget_Comparison_' . $period . '_' . now()->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 400);
        }
    }
}
