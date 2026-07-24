<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Services;

use App\Domain\BizBoost\Repositories\BusinessRepositoryInterface;
use App\Domain\BizBoost\Repositories\PostRepositoryInterface;
use App\Domain\BizBoost\Repositories\SaleRepositoryInterface;
use App\Domain\BizBoost\Repositories\CustomerRepositoryInterface;
use App\Domain\BizBoost\Repositories\ProductRepositoryInterface;
use App\Domain\BizBoost\Repositories\IntegrationRepositoryInterface;
use App\Domain\BizBoost\Repositories\AiUsageLogRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        private BusinessRepositoryInterface $businessRepo,
        private PostRepositoryInterface $postRepo,
        private SaleRepositoryInterface $saleRepo,
        private CustomerRepositoryInterface $customerRepo,
        private ProductRepositoryInterface $productRepo,
        private IntegrationRepositoryInterface $integrationRepo,
        private AiUsageLogRepositoryInterface $aiUsageRepo,
    ) {}

    public function getStats(int $businessId): array
    {
        $startOfMonth = now()->startOfMonth()->toDateTimeString();
        $endOfMonth = now()->endOfMonth()->toDateTimeString();
        $startOfLastMonth = now()->subMonth()->startOfMonth()->toDateTimeString();
        $endOfLastMonth = now()->subMonth()->endOfMonth()->toDateTimeString();

        $postsThisMonth = $this->postRepo->countByBusinessAndMonth($businessId, $startOfMonth, $endOfMonth);
        $postsLastMonth = $this->postRepo->countByBusinessAndMonth($businessId, $startOfLastMonth, $endOfLastMonth);

        $salesThisMonth = $this->saleRepo->sumByBusiness($businessId, ['date_from' => $startOfMonth, 'date_to' => $endOfMonth]);
        $salesLastMonth = $this->saleRepo->sumByBusiness($businessId, ['date_from' => $startOfLastMonth, 'date_to' => $endOfLastMonth]);

        $totalCustomers = $this->customerRepo->countByBusiness($businessId);
        $totalProducts = $this->productRepo->countByBusiness($businessId);

        $aiCreditsUsed = $this->aiUsageRepo->sumCreditsByBusinessAndMonth($businessId, $startOfMonth, $endOfMonth);

        return [
            'products' => $totalProducts,
            'customers' => $totalCustomers,
            'posts_this_month' => $postsThisMonth,
            'ai_credits_used' => $aiCreditsUsed,
            'sales_detail' => [
                'current' => $salesThisMonth,
                'previous' => $salesLastMonth,
                'change' => $salesLastMonth > 0 ? round((($salesThisMonth - $salesLastMonth) / $salesLastMonth) * 100, 1) : 0,
            ],
            'posts_detail' => [
                'current' => $postsThisMonth,
                'previous' => $postsLastMonth,
                'change' => $postsLastMonth > 0 ? round((($postsThisMonth - $postsLastMonth) / $postsLastMonth) * 100, 1) : 0,
            ],
        ];
    }

    public function getSparklineData(int $businessId): array
    {
        $days = 7;
        $dates = collect(range(0, $days - 1))->map(fn($i) => now()->subDays($days - 1 - $i)->format('Y-m-d'));

        $salesByDay = DB::table('bizboost_sales')
            ->where('business_id', $businessId)
            ->where('sale_date', '>=', now()->subDays($days)->toDateString())
            ->selectRaw('DATE(sale_date) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $customersByDay = DB::table('bizboost_customers')
            ->where('business_id', $businessId)
            ->where('created_at', '>=', now()->subDays($days)->toDateTimeString())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $postsByDay = DB::table('bizboost_posts')
            ->where('business_id', $businessId)
            ->where('created_at', '>=', now()->subDays($days)->toDateTimeString())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $productsByDay = DB::table('bizboost_products')
            ->where('business_id', $businessId)
            ->where('created_at', '>=', now()->subDays($days)->toDateTimeString())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        return [
            'sales' => $dates->map(fn($date) => (int) ($salesByDay[$date] ?? 0))->values()->toArray(),
            'customers' => $dates->map(fn($date) => (int) ($customersByDay[$date] ?? 0))->values()->toArray(),
            'posts' => $dates->map(fn($date) => (int) ($postsByDay[$date] ?? 0))->values()->toArray(),
            'products' => $dates->map(fn($date) => (int) ($productsByDay[$date] ?? 0))->values()->toArray(),
        ];
    }

    public function getEngagement(int $businessId, string $startDate): float
    {
        return (float) DB::table('bizboost_analytics_daily')
            ->where('business_id', $businessId)
            ->where('date', '>=', $startDate)
            ->sum('post_engagements');
    }

    public function hasActiveIntegrations(int $businessId): bool
    {
        return !empty($this->integrationRepo->findActiveByBusiness($businessId));
    }
}