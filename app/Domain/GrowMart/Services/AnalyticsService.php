<?php

namespace App\Domain\GrowMart\Services;

use App\Domain\GrowMart\Repositories\OrderRepositoryInterface;
use App\Domain\GrowMart\Repositories\ProductRepositoryInterface;
use App\Domain\GrowMart\Repositories\ReviewRepositoryInterface;

class AnalyticsService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ReviewRepositoryInterface $reviewRepository,
    ) {}

    public function revenueOverTime(string $period = 'daily', int $days = 30): array
    {
        return $this->orderRepository->revenueOverTime($period, $days);
    }

    public function orderStatusBreakdown(): array
    {
        return $this->orderRepository->orderStatusBreakdown();
    }

    public function topSellingProducts(int $limit = 10): array
    {
        $products = $this->productRepository->findTopSelling($limit);
        return array_map(fn($p) => [
            'id' => $p['id'],
            'name' => $p['name'],
            'total_sold' => (int) ($p['total_sold'] ?? 0),
            'total_revenue' => (int) ($p['total_revenue'] ?? 0),
            'total_revenue_formatted' => 'K' . number_format(($p['total_revenue'] ?? 0) / 100, 2),
        ], $products);
    }

    public function averageOrderValue(): array
    {
        return $this->orderRepository->averageOrderValue();
    }

    public function pendingReviewsCount(): int
    {
        return $this->reviewRepository->countPending();
    }

    public function lowStockCount(): int
    {
        $products = $this->productRepository->findWithLowStock();
        return count(array_filter($products, fn($p) => ($p['quantity'] ?? 0) > 0));
    }

    public function outOfStockCount(): int
    {
        $products = $this->productRepository->findWithLowStock();
        return count(array_filter($products, fn($p) => ($p['quantity'] ?? 0) <= 0));
    }
}
