<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProfitLossTrackingService;
use App\Services\PdfFinancialReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;

/**
 * Profit & Loss Controller
 * 
 * Provides comprehensive profit and loss tracking and analysis
 */
class ProfitLossController extends Controller
{
    public function __construct(
        protected ProfitLossTrackingService $plService,
        protected PdfFinancialReportService $pdfService
    ) {
        // Middleware applied in routes
    }

    /**
     * Display P&L dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        
        // Get list of modules for filter dropdown
        $modules = \App\Models\Module::where('is_active', true)
            ->orderBy('display_order')
            ->get(['id', 'name', 'code'])
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'code' => $m->code,
            ]);
        
        return Inertia::render('Admin/Financial/ProfitLoss', [
            'initialPeriod' => $period,
            'modules' => $modules,
        ]);
    }

    /**
     * Get P&L statement
     */
    public function getStatement(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $moduleId = $request->get('module_id');
        
        try {
            $statement = $this->plService->getProfitLossStatement($period, $startDate, $endDate, $moduleId);

            return response()->json([
                'success' => true,
                'data' => $statement,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get P&L statement: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get commission efficiency
     */
    public function getCommissionEfficiency(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $moduleId = $request->get('module_id');
        
        try {
            $efficiency = $this->plService->getCommissionEfficiency($period, $startDate, $endDate, $moduleId);

            return response()->json([
                'success' => true,
                'data' => $efficiency,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get commission efficiency: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get cash flow analysis
     */
    public function getCashFlow(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $moduleId = $request->get('module_id');
        
        try {
            $cashFlow = $this->plService->getCashFlowAnalysis($period, $startDate, $endDate, $moduleId);

            return response()->json([
                'success' => true,
                'data' => $cashFlow,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cash flow: ' . $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Export P&L report as PDF
     */
    public function exportPdf(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $moduleId = $request->get('module_id');
        
        try {
            $pdf = $this->pdfService->generateProfitLossReport($period, $startDate, $endDate, $moduleId);
            
            $filename = 'PL_Statement_' . $period . '_' . now()->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 400);
        }
    }
}
