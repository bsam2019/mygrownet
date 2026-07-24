<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Services\DashboardService;
use App\Domain\BizBoost\Repositories\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function __construct(
        private BusinessService $businessService,
        private DashboardService $dashboardService,
        private ProductRepositoryInterface $productRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $period = $request->input('period', '30');

        $startDate = now()->subDays((int) $period)->startOfDay()->toDateString();
        $endDate = now()->endOfDay()->toDateString();

        $sales = DB::table('bizboost_sales')
            ->where('business_id', $business->id)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->orderBy('sale_date')
            ->get();

        $events = DB::table('bizboost_analytics_events')
            ->where('business_id', $business->id)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->orderBy('recorded_at')
            ->get();

        return Inertia::render('BizBoost/Analytics/Index', [
            'period' => $period,
            'stats' => $this->dashboardService->getStats($business->id),
            'dailySales' => $sales,
            'events' => $events,
            'topProducts' => $this->productRepo->findActiveByBusiness($business->id),
            'topCustomers' => [],
        ]);
    }

    public function sales(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $period = $request->input('period', '30');
        $groupBy = $request->input('group_by', 'day');

        return Inertia::render('BizBoost/Analytics/Sales', [
            'period' => $period,
            'groupBy' => $groupBy,
            'chartData' => $this->dashboardService->getSparklineData($business->id),
        ]);
    }

    public function products(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        return Inertia::render('BizBoost/Analytics/Products', [
            'topProducts' => $this->productRepo->findActiveByBusiness($business->id),
        ]);
    }
}