<?php

namespace App\Domain\GrowMart\Services;

use App\Models\GrowMart\GrowMartOrder;
use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartReview;
use App\Models\GrowMart\GrowMartInventory;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function revenueOverTime(string $period = 'daily', int $days = 30): array
    {
        $format = match ($period) {
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $data = GrowMartOrder::where('status', 'delivered')
            ->where('created_at', '>=', now()->subDays($days))
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$format}') as period"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'labels' => $data->pluck('period'),
            'revenue' => $data->pluck('revenue')->map(fn($v) => (int) $v),
            'counts' => $data->pluck('count'),
        ];
    }

    public function orderStatusBreakdown(): array
    {
        $statuses = ['pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered', 'cancelled'];
        $data = [];
        foreach ($statuses as $s) {
            $data[] = [
                'status' => str_replace('_', ' ', ucfirst($s)),
                'count' => GrowMartOrder::where('status', $s)->count(),
            ];
        }
        return $data;
    }

    public function topSellingProducts(int $limit = 10): array
    {
        return GrowMartProduct::select('growmart_products.id', 'growmart_products.name', 'growmart_products.price')
            ->selectSub(function ($q) {
                $q->from('growmart_order_items')
                    ->whereColumn('product_id', 'growmart_products.id')
                    ->select(DB::raw('COALESCE(SUM(quantity), 0)'));
            }, 'total_sold')
            ->selectSub(function ($q) {
                $q->from('growmart_order_items')
                    ->join('growmart_orders', 'growmart_order_items.order_id', '=', 'growmart_orders.id')
                    ->whereColumn('product_id', 'growmart_products.id')
                    ->where('growmart_orders.status', 'delivered')
                    ->select(DB::raw('COALESCE(SUM(growmart_order_items.subtotal), 0)'));
            }, 'total_revenue')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'total_sold' => (int) $p->total_sold,
                'total_revenue' => (int) $p->total_revenue,
                'total_revenue_formatted' => 'K' . number_format($p->total_revenue / 100, 2),
            ]);
    }

    public function averageOrderValue(): array
    {
        $avg = GrowMartOrder::where('status', 'delivered')
            ->select(DB::raw('AVG(total) as aov'), DB::raw('COUNT(*) as total_orders'))
            ->first();

        return [
            'aov' => (int) ($avg?->aov ?? 0),
            'aov_formatted' => 'K' . number_format(($avg?->aov ?? 0) / 100, 2),
            'total_orders' => (int) ($avg?->total_orders ?? 0),
        ];
    }

    public function pendingReviewsCount(): int
    {
        return GrowMartReview::where('is_approved', false)->count();
    }

    public function lowStockCount(): int
    {
        return GrowMartInventory::whereColumn('quantity', '<=', 'low_stock_threshold')
            ->where('quantity', '>', 0)
            ->count();
    }

    public function outOfStockCount(): int
    {
        return GrowMartInventory::where('quantity', '<=', 0)->count();
    }
}
