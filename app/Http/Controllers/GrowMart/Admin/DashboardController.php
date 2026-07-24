<?php

namespace App\Http\Controllers\GrowMart\Admin;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Services\AnalyticsService;
use App\Domain\GrowMart\Repositories\OrderRepositoryInterface;
use App\Domain\GrowMart\Repositories\ProductRepositoryInterface;
use App\Domain\GrowMart\Repositories\CategoryRepositoryInterface;
use App\Domain\GrowMart\Repositories\WarehouseRepositoryInterface;
use App\Models\GrowMart\GrowMartOrder;
use App\Models\GrowMart\GrowMartProduct;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analytics,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly WarehouseRepositoryInterface $warehouseRepository,
    ) {}

    public function index()
    {
        $pendingOrders = $this->orderRepository->countByStatus('pending');
        $processingOrders = $this->orderRepository->countByStatus('confirmed')
            + $this->orderRepository->countByStatus('processing')
            + $this->orderRepository->countByStatus('out_for_delivery');
        $todayOrders = $this->orderRepository->countToday();
        $totalRevenue = $this->orderRepository->sumByStatus('delivered', 'total');

        $revenueOverTime = $this->analytics->revenueOverTime('daily', 14);
        $orderStatusBreakdown = $this->analytics->orderStatusBreakdown();
        $topProducts = $this->analytics->topSellingProducts(5);
        $aov = $this->analytics->averageOrderValue();

        $recentOrders = GrowMartOrder::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'customer' => $o->user?->name ?? 'Unknown',
                'total_formatted' => 'K' . number_format($o->total / 100, 2),
                'status' => $o->status,
                'created_at' => $o->created_at->diffForHumans(),
            ]);

        $recentProducts = GrowMartProduct::with('category')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category?->name,
                'price' => $p->formatted_price,
                'status' => $p->status,
                'created_at' => $p->created_at->diffForHumans(),
            ]);

        return Inertia::render('GrowMart/Admin/Dashboard', [
            'stats' => [
                'total_products' => $this->productRepository->countAll(),
                'active_products' => $this->productRepository->countActive(),
                'total_categories' => $this->categoryRepository->countAll(),
                'total_warehouses' => $this->warehouseRepository->countAll(),
                'pending_orders' => $pendingOrders,
                'processing_orders' => $processingOrders,
                'today_orders' => $todayOrders,
                'total_revenue' => $totalRevenue,
                'total_revenue_formatted' => 'K' . number_format($totalRevenue / 100, 2),
                'recent_orders' => $recentOrders,
                'recent_products' => $recentProducts,
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
