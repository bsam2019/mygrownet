<?php

namespace App\Http\Controllers\GrowBiz;

use App\Http\Controllers\Controller;
use App\Domain\GrowBiz\Services\AnalyticsService;
use App\Domain\GrowBiz\Services\TaskManagementService;
use App\Domain\GrowBiz\Services\EmployeeManagementService;
use App\Domain\GrowBiz\Services\SummaryService;
use App\Domain\GrowBiz\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class ReportsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService,
        private TaskManagementService $taskService,
        private EmployeeManagementService $employeeService,
        private SummaryService $summaryService,
        private ExportService $exportService
    ) {}

    /**
     * Analytics Dashboard
     */
    public function analytics(Request $request)
    {
        $user = Auth::user();

        try {
            $taskAnalytics = $this->analyticsService->getTaskAnalytics($user->id);
            $employeePerformance = $this->analyticsService->getEmployeePerformance($user->id);
            $workloadDistribution = $this->analyticsService->getWorkloadDistribution($user->id);
            $productivityTrends = $this->analyticsService->getProductivityTrends($user->id, 14);
            
            // Period summaries
            $weekSummary = $this->analyticsService->getCompletionSummary($user->id, 'week');
            $monthSummary = $this->analyticsService->getCompletionSummary($user->id, 'month');

            return Inertia::render('GrowBiz/Reports/Analytics', [
                'taskAnalytics' => $taskAnalytics,
                'employeePerformance' => $employeePerformance,
                'workloadDistribution' => $workloadDistribution,
                'productivityTrends' => $productivityTrends,
                'weekSummary' => $weekSummary,
                'monthSummary' => $monthSummary,
            ]);
        } catch (Throwable $e) {
            Log::error('Analytics page failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return Inertia::render('GrowBiz/Reports/Analytics', [
                'taskAnalytics' => null,
                'employeePerformance' => [],
                'workloadDistribution' => [],
                'productivityTrends' => [],
                'weekSummary' => null,
                'monthSummary' => null,
                'error' => 'Unable to load analytics. Please try again.',
            ]);
        }
    }

    /**
     * Performance Reports
     */
    public function performance(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', 'week');

        try {
            $employeePerformance = $this->analyticsService->getEmployeePerformance($user->id);
            $summary = $this->analyticsService->getCompletionSummary($user->id, $period);
            $taskStats = $this->taskService->getTaskStatistics($user->id);
            $employeeStats = $this->employeeService->getEmployeeStatistics($user->id);

            return Inertia::render('GrowBiz/Reports/Performance', [
                'employeePerformance' => $employeePerformance,
                'summary' => $summary,
                'taskStats' => $taskStats,
                'employeeStats' => $employeeStats,
                'period' => $period,
            ]);
        } catch (Throwable $e) {
            Log::error('Performance report failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return Inertia::render('GrowBiz/Reports/Performance', [
                'employeePerformance' => [],
                'summary' => null,
                'taskStats' => null,
                'employeeStats' => null,
                'period' => $period,
                'error' => 'Unable to load performance data. Please try again.',
            ]);
        }
    }

    /**
     * Summaries Page
     */
    public function summaries(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type', 'daily');
        $date = $request->get('date');

        try {
            $dailySummary = $this->summaryService->getDailySummary($user->id, $date);
            $weeklySummary = $this->summaryService->getWeeklySummary($user->id);

            return Inertia::render('GrowBiz/Reports/Summaries', [
                'dailySummary' => $dailySummary,
                'weeklySummary' => $weeklySummary,
                'type' => $type,
                'selectedDate' => $date ?? date('Y-m-d'),
            ]);
        } catch (Throwable $e) {
            Log::error('Summaries page failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return Inertia::render('GrowBiz/Reports/Summaries', [
                'dailySummary' => null,
                'weeklySummary' => null,
                'type' => $type,
                'selectedDate' => $date ?? date('Y-m-d'),
                'error' => 'Unable to load summaries. Please try again.',
            ]);
        }
    }

    /**
     * Get daily summary (API)
     */
    public function dailySummary(Request $request)
    {
        $user = Auth::user();
        $date = $request->get('date');

        try {
            $summary = $this->summaryService->getDailySummary($user->id, $date);
            return response()->json($summary);
        } catch (Throwable $e) {
            Log::error('Daily summary API failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to load summary'], 500);
        }
    }

    /**
     * Get weekly summary (API)
     */
    public function weeklySummary(Request $request)
    {
        $user = Auth::user();
        $weekStart = $request->get('week_start');

        try {
            $summary = $this->summaryService->getWeeklySummary($user->id, $weekStart);
            return response()->json($summary);
        } catch (Throwable $e) {
            Log::error('Weekly summary API failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to load summary'], 500);
        }
    }

    /**
     * Export tasks to CSV
     */
    public function exportTasks(Request $request)
    {
        $user = Auth::user();
        $filters = $request->only(['status', 'priority', 'assigned_to', 'search']);

        try {
            $export = $this->exportService->exportTasksToCsv($user->id, $filters);
            $content = $this->exportService->generateCsvContent($export['headers'], $export['rows']);

            return response($content, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $export['filename'] . '"',
            ]);
        } catch (Throwable $e) {
            Log::error('Export tasks failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to export tasks.');
        }
    }

    /**
     * Export employees to CSV
     */
    public function exportEmployees(Request $request)
    {
        $user = Auth::user();
        $filters = $request->only(['status', 'department', 'search']);

        try {
            $export = $this->exportService->exportEmployeesToCsv($user->id, $filters);
            $content = $this->exportService->generateCsvContent($export['headers'], $export['rows']);

            return response($content, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $export['filename'] . '"',
            ]);
        } catch (Throwable $e) {
            Log::error('Export employees failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to export employees.');
        }
    }

    /**
     * Export weekly summary to CSV
     */
    public function exportWeeklySummary(Request $request)
    {
        $user = Auth::user();
        $weekStart = $request->get('week_start');

        try {
            $export = $this->exportService->exportWeeklySummary($user->id, $weekStart);
            $content = $this->exportService->generateCsvContent($export['headers'], $export['rows']);

            return response($content, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $export['filename'] . '"',
            ]);
        } catch (Throwable $e) {
            Log::error('Export weekly summary failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to export summary.');
        }
    }

    /**
     * Export performance report to CSV
     */
    public function exportPerformance(Request $request)
    {
        $user = Auth::user();

        try {
            $export = $this->exportService->exportPerformanceReport($user->id);
            $content = $this->exportService->generateCsvContent($export['headers'], $export['rows']);

            return response($content, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $export['filename'] . '"',
            ]);
        } catch (Throwable $e) {
            Log::error('Export performance failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to export performance report.');
        }
    }
}
