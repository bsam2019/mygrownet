<?php

namespace App\Http\Controllers\LifePlus;

use App\Http\Controllers\Controller;
use App\Domain\LifePlus\Services\AnalyticsService;
use App\Domain\LifePlus\Services\ExportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService,
        protected ExportService $exportService
    ) {}

    public function index(Request $request)
    {
        $period = $request->get('period', 'month');

        return Inertia::render('LifePlus/Analytics/Index', [
            'analytics' => $this->analyticsService->getUserAnalytics(auth()->id(), $period),
            'period' => $period,
        ]);
    }

    public function expenses(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);

        return response()->json(
            $this->analyticsService->getExpenseAnalytics(auth()->id(), $startDate)
        );
    }

    public function tasks(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);

        return response()->json(
            $this->analyticsService->getTaskAnalytics(auth()->id(), $startDate)
        );
    }

    public function habits(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);

        return response()->json(
            $this->analyticsService->getHabitAnalytics(auth()->id(), $startDate)
        );
    }

    // Export endpoints
    public function exportAll()
    {
        $result = $this->exportService->exportToJson(auth()->id());
        return response()->json($result);
    }

    public function exportExpenses(Request $request)
    {
        $result = $this->exportService->exportExpensesToCsv(
            auth()->id(),
            $request->get('start_date'),
            $request->get('end_date')
        );
        return response()->json($result);
    }

    public function exportTasks(Request $request)
    {
        $result = $this->exportService->exportTasksToCsv(
            auth()->id(),
            $request->boolean('include_completed', true)
        );
        return response()->json($result);
    }

    public function exportNotes()
    {
        $result = $this->exportService->exportNotesToText(auth()->id());
        return response()->json($result);
    }

    private function getStartDate(string $period): \Carbon\Carbon
    {
        return match ($period) {
            'week' => \Carbon\Carbon::now()->subWeek(),
            'month' => \Carbon\Carbon::now()->subMonth(),
            'quarter' => \Carbon\Carbon::now()->subQuarter(),
            'year' => \Carbon\Carbon::now()->subYear(),
            default => \Carbon\Carbon::now()->subMonth(),
        };
    }
}
