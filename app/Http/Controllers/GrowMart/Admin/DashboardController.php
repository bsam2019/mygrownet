<?php

namespace App\Http\Controllers\GrowMart\Admin;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Services\AnalyticsService;
use App\Models\GrowMart\GrowMartOrder;
use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartCategory;
use App\Models\GrowMart\GrowMartWarehouse;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analytics,
    ) {}

    public function index()
    {
        $pendingOrders = GrowMartOrder::where('status', 'pending')->count();
        $processingOrders = GrowMartOrder::whereIn('status', ['confirmed', 'processing', 'out_for_delivery'])->count();
        $todayOrders = GrowMartOrder::whereDate('created_at', today())->count();
        $totalRevenue = GrowMartOrder::where('status', 'delivered')->sum('total');

        $revenueOverTime = $this->analytics->revenueOverTime('daily', 14);
        $orderStatusBreakdown = $this->analytics->orderStatusBreakdown();
        $topProducts = $this->analytics->topSellingProducts(5);
        $aov = $this->analytics->averageOrderValue();

        return Inertia::render('GrowMart/Admin/Dashboard', [
            'stats' => [
                'total_products' => GrowMartProduct::count(),
                'active_products' => GrowMartProduct::where('status', 'active')->count(),
                'total_categories' => GrowMartCategory::count(),
                'total_warehouses' => GrowMartWarehouse::count(),
                'pending_orders' => $pendingOrders,
                'processing_orders' => $processingOrders,
                'today_orders' => $todayOrders,
                'total_revenue' => $totalRevenue,
                'total_revenue_formatted' => 'K' . number_format($totalRevenue / 100, 2),
                'recent_orders' => GrowMartOrder::with('user')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn ($o) => [
                        'id' => $o->id,
                        'order_number' => $o->order_number,
                        'customer' => $o->user?->name ?? 'Unknown',
                        'total_formatted' => 'K' . number_format($o->total / 100, 2),
                        'status' => $o->status,
                        'created_at' => $o->created_at->diffForHumans(),
                    ]),
                'recent_products' => GrowMartProduct::with('category')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn ($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'category' => $p->category?->name,
                        'price' => $p->formatted_price,
                        'status' => $p->status,
                        'created_at' => $p->created_at->diffForHumans(),
                    ]),
                'revenue_chart' => $revenueOverTime,
                'order_status_breakdown' => $orderStatusBreakdown,
                'top_products' => $topProducts,
                'aov' => $aov,
                'pending_reviews' => $this->analytics->pendingReviewsCount(),
                'low_stock_count' => $this->analytics->lowStockCount(),
                'out_of_stock_count' => $this->analytics->outOfStockCount(),
            ],
        ]);
    }
}
