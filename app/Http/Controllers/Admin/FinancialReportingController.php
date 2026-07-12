<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FinancialReportingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;

class FinancialReportingController extends Controller
{
    public function __construct(
        protected FinancialReportingService $financialReportingService
    ) {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display the financial reporting dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        
        return Inertia::render('Admin/Financial/Dashboard', [
            'overview' => $this->financialReportingService->getFinancialOverview($period),
            'complianceMetrics' => $this->financialReportingService->getComplianceMetrics(),
            'sustainabilityMetrics' => $this->financialReportingService->getSustainabilityMetrics(),
            'commissionCapTracking' => $this->financialReportingService->getCommissionCapTracking(),
            'revenueAnalysis' => $this->financialReportingService->getRevenueAnalysis($period),
            'period' => $period
        ]);
    }

    /**
     * Display comprehensive financial reports
     */
    public function reports(Request $request)
    {
        $reportType = $request->get('type', 'comprehensive');
        $period = $request->get('period', 'month');
        $filters = $request->only(['date_from', 'date_to', 'category', 'tier']);
        
        return Inertia::render('Admin/Financial/Reports', [
            'reportData' => $this->financialReportingService->generateReport($reportType, $period, $filters),
            'reportType' => $reportType,
            'period' => $period,
            'filters' => $filters,
            'availableReports' => $this->financialReportingService->getAvailableReports(),
        ]);
    }

    /**
     * Display sustainability metrics monitoring
     */
    public function sustainability(Request $request)
    {
        $period = $request->get('period', 'month');
        
        return Inertia::render('Admin/Financial/Sustainability', [
            'sustainabilityData' => $this->financialReportingService->getSustainabilityAnalysis($period),
            'commissionCapData' => $this->financialReportingService->getCommissionCapAnalysis($period),
            'riskAssessment' => $this->financialReportingService->getRiskAssessment(),
            'projections' => $this->financialReportingService->getFinancialProjections($period),
        ]);
    }

    /**
     * Display regulatory compliance dashboard
     */
    public function compliance(Request $request)
    {
        $period = $request->get('period', 'month');
        
        return Inertia::render('Admin/Financial/Compliance', [
            'complianceData' => $this->financialReportingService->getComplianceAnalysis($period),
            'regulatoryMetrics' => $this->financialReportingService->getRegulatoryMetrics(),
            'auditTrail' => $this->financialReportingService->getAuditTrail($period),
            'complianceAlerts' => $this->financialReportingService->getComplianceAlerts(),
        ]);
    }

    /**
     * Generate custom financial report
     */
    public function generateCustomReport(Request $request): JsonResponse
    {
        $request->validate([
            'report_type' => 'required|string',
            'period' => 'required|in:week,month,quarter,year,custom',
            'date_from' => 'required_if:period,custom|date',
            'date_to' => 'required_if:period,custom|date|after_or_equal:date_from',
            'include_metrics' => 'array',
            'format' => 'required|in:json,csv,pdf',
        ]);

        try {
            $report = $this->financialReportingService->generateCustomReport(
                $request->report_type,
                $request->period,
                $request->date_from,
                $request->date_to,
                $request->include_metrics ?? [],
                $request->format
            );

            if ($request->format === 'json') {
                return response()->json([
                    'success' => true,
                    'data' => $report
                ]);
            } else {
                // For CSV/PDF, return download response
                return response()->json([
                    'success' => true,
                    'message' => 'Report generated successfully',
                    'download_url' => $report['download_url']
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get commission cap enforcement data
     */
    public function getCommissionCapData(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        
        try {
            $data = $this->financialReportingService->getCommissionCapEnforcement($period);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get commission cap data: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update commission cap settings
     */
    public function updateCommissionCap(Request $request): JsonResponse
    {
        $request->validate([
            'cap_percentage' => 'required|numeric|min:0|max:100',
            'enforcement_level' => 'required|in:strict,moderate,flexible',
            'alert_threshold' => 'required|numeric|min:0|max:100',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $result = $this->financialReportingService->updateCommissionCap(
                $request->cap_percentage,
                $request->enforcement_level,
                $request->alert_threshold,
                $request->reason,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Commission cap updated successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update commission cap: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get financial projections
     */
    public function getProjections(Request $request): JsonResponse
    {
        $request->validate([
            'projection_type' => 'required|in:revenue,commission,growth,sustainability',
            'time_horizon' => 'required|in:3_months,6_months,1_year,2_years',
            'scenario' => 'required|in:conservative,realistic,optimistic',
        ]);

        try {
            $projections = $this->financialReportingService->generateProjections(
                $request->projection_type,
                $request->time_horizon,
                $request->scenario
            );

            return response()->json([
                'success' => true,
                'data' => $projections
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate projections: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Export financial report
     */
    public function exportReport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string',
            'period' => 'required|string',
            'format' => 'required|in:csv,pdf,excel',
        ]);

        try {
            return $this->financialReportingService->exportReport(
                $request->report_type,
                $request->period,
                $request->format,
                $request->only(['date_from', 'date_to', 'filters'])
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export report: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get real-time financial metrics
     */
    public function getRealTimeMetrics(Request $request): JsonResponse
    {
        try {
            $metrics = $this->financialReportingService->getRealTimeMetrics();

            return response()->json([
                'success' => true,
                'data' => $metrics,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get real-time metrics: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Generate regulatory compliance report
     */
    public function generateComplianceReport(Request $request): JsonResponse
    {
        $request->validate([
            'report_period' => 'required|in:month,quarter,year',
            'compliance_areas' => 'required|array',
            'compliance_areas.*' => 'in:commission_caps,payout_timing,volume_legitimacy,financial_transparency',
        ]);

        try {
            $report = $this->financialReportingService->generateComplianceReport(
                $request->report_period,
                $request->compliance_areas
            );

            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate compliance report: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get financial health score
     */
    public function getFinancialHealthScore(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        
        try {
            $healthScore = $this->financialReportingService->calculateFinancialHealthScore($period);

            return response()->json([
                'success' => true,
                'data' => $healthScore
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate financial health score: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get revenue breakdown analysis
     */
    public function getRevenueBreakdown(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $breakdown_type = $request->get('breakdown_type', 'source');
        
        try {
            $breakdown = $this->financialReportingService->getRevenueBreakdown($period, $breakdown_type);

            return response()->json([
                'success' => true,
                'data' => $breakdown
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get revenue breakdown: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Schedule automated report
     */
    public function scheduleReport(Request $request): JsonResponse
    {
        $request->validate([
            'report_type' => 'required|string',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly',
            'recipients' => 'required|array',
            'recipients.*' => 'email',
            'format' => 'required|in:pdf,csv,excel',
            'include_metrics' => 'array',
        ]);

        try {
            $schedule = $this->financialReportingService->scheduleAutomatedReport(
                $request->report_type,
                $request->frequency,
                $request->recipients,
                $request->format,
                $request->include_metrics ?? [],
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Report scheduled successfully',
                'data' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule report: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get commission distribution analysis
     */
    public function getCommissionDistribution(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $analysis_type = $request->get('analysis_type', 'by_level');
        
        try {
            $distribution = $this->financialReportingService->getCommissionDistribution($period, $analysis_type);

            return response()->json([
                'success' => true,
                'data' => $distribution
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get commission distribution: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get cost analysis
     */
    public function getCostAnalysis(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        
        try {
            $analysis = $this->financialReportingService->getCostAnalysis($period);

            return response()->json([
                'success' => true,
                'data' => $analysis
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cost analysis: ' . $e->getMessage()
            ], 400);
        }
    }
}