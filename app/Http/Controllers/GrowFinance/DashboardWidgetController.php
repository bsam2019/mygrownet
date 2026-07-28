<?php

declare(strict_types=1);

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\DashboardWidgetService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardWidgetController extends Controller
{
    public function __construct(
        private DashboardWidgetService $widgetService,
    ) {}

    public function index(Request $request)
    {
        $businessId = $request->user()->id;
        $asOf = $request->has('as_of') ? new \DateTimeImmutable($request->input('as_of')) : null;
        $widgets = $this->widgetService->getAll($businessId, $asOf);

        if ($request->wantsJson()) {
            return response()->json($widgets);
        }

        return Inertia::render('GrowFinance/Dashboard/Widgets', [
            'widgets' => $widgets,
        ]);
    }

    public function cashPosition(Request $request): JsonResponse
    {
        return response()->json(
            $this->widgetService->getCashPosition($request->user()->id, new \DateTimeImmutable('now'))
        );
    }

    public function revenueTrend(Request $request): JsonResponse
    {
        $from = new \DateTimeImmutable('first day of January this year');
        return response()->json(
            $this->widgetService->getRevenueTrend($request->user()->id, $from, new \DateTimeImmutable('now'))
        );
    }

    public function arApSummary(Request $request): JsonResponse
    {
        return response()->json(
            $this->widgetService->getArApSummary($request->user()->id)
        );
    }
}
