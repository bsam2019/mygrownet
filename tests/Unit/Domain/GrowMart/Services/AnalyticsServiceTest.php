<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowMart\Services;

use App\Domain\GrowMart\Repositories\OrderRepositoryInterface;
use App\Domain\GrowMart\Repositories\ProductRepositoryInterface;
use App\Domain\GrowMart\Repositories\ReviewRepositoryInterface;
use App\Domain\GrowMart\Services\AnalyticsService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AnalyticsServiceTest extends TestCase
{
    private OrderRepositoryInterface $orderRepo;
    private ProductRepositoryInterface $productRepo;
    private ReviewRepositoryInterface $reviewRepo;
    private AnalyticsService $service;

    protected function setUp(): void
    {
        $this->orderRepo = $this->createStub(OrderRepositoryInterface::class);
        $this->productRepo = $this->createStub(ProductRepositoryInterface::class);
        $this->reviewRepo = $this->createStub(ReviewRepositoryInterface::class);
        $this->service = new AnalyticsService(
            $this->orderRepo,
            $this->productRepo,
            $this->reviewRepo,
        );
    }

    #[Test]
    public function revenue_over_time_delegates_to_order_repository(): void
    {
        $expected = [['date' => '2026-01-01', 'revenue' => 1000]];
        $this->orderRepo = $this->createMock(OrderRepositoryInterface::class);
        $this->orderRepo->expects($this->once())
            ->method('revenueOverTime')
            ->with('daily', 30)
            ->willReturn($expected);
        $this->service = new AnalyticsService($this->orderRepo, $this->productRepo, $this->reviewRepo);

        $this->assertSame($expected, $this->service->revenueOverTime('daily', 30));
    }

    #[Test]
    public function revenue_over_time_passes_period_and_days(): void
    {
        $expected = [['date' => '2026-01-01', 'revenue' => 500]];
        $this->orderRepo = $this->createMock(OrderRepositoryInterface::class);
        $this->orderRepo->expects($this->once())
            ->method('revenueOverTime')
            ->with('monthly', 90)
            ->willReturn($expected);
        $this->service = new AnalyticsService($this->orderRepo, $this->productRepo, $this->reviewRepo);

        $this->assertSame($expected, $this->service->revenueOverTime('monthly', 90));
    }

    #[Test]
    public function order_status_breakdown_delegates(): void
    {
        $expected = [['status' => 'pending', 'count' => 5]];
        $this->orderRepo = $this->createMock(OrderRepositoryInterface::class);
        $this->orderRepo->expects($this->once())
            ->method('orderStatusBreakdown')
            ->willReturn($expected);
        $this->service = new AnalyticsService($this->orderRepo, $this->productRepo, $this->reviewRepo);

        $this->assertSame($expected, $this->service->orderStatusBreakdown());
    }

    #[Test]
    public function top_selling_products_maps_data_correctly(): void
    {
        $raw = [
            ['id' => 1, 'name' => 'Tomatoes', 'total_sold' => '50', 'total_revenue' => '15000'],
            ['id' => 2, 'name' => 'Onions', 'total_sold' => '30', 'total_revenue' => '9000'],
        ];
        $this->productRepo = $this->createMock(ProductRepositoryInterface::class);
        $this->productRepo->expects($this->once())
            ->method('findTopSelling')
            ->with(5)
            ->willReturn($raw);
        $this->service = new AnalyticsService($this->orderRepo, $this->productRepo, $this->reviewRepo);

        $result = $this->service->topSellingProducts(5);

        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('Tomatoes', $result[0]['name']);
        $this->assertEquals(50, $result[0]['total_sold']);
        $this->assertEquals(15000, $result[0]['total_revenue']);
        $this->assertEquals('K150.00', $result[0]['total_revenue_formatted']);
        $this->assertEquals(30, $result[1]['total_sold']);
    }

    #[Test]
    public function top_selling_products_handles_empty_data(): void
    {
        $this->productRepo = $this->createMock(ProductRepositoryInterface::class);
        $this->productRepo->expects($this->once())
            ->method('findTopSelling')
            ->willReturn([]);
        $this->service = new AnalyticsService($this->orderRepo, $this->productRepo, $this->reviewRepo);

        $this->assertEquals([], $this->service->topSellingProducts());
    }

    #[Test]
    public function top_selling_products_handles_missing_keys(): void
    {
        $raw = [['id' => 1, 'name' => 'Milk']];
        $this->productRepo = $this->createMock(ProductRepositoryInterface::class);
        $this->productRepo->expects($this->once())
            ->method('findTopSelling')
            ->willReturn($raw);
        $this->service = new AnalyticsService($this->orderRepo, $this->productRepo, $this->reviewRepo);

        $result = $this->service->topSellingProducts();
        $this->assertEquals(0, $result[0]['total_sold']);
        $this->assertEquals(0, $result[0]['total_revenue']);
        $this->assertEquals('K0.00', $result[0]['total_revenue_formatted']);
    }

    #[Test]
    public function average_order_value_delegates(): void
    {
        $expected = ['average' => 2500, 'count' => 100];
        $this->orderRepo = $this->createMock(OrderRepositoryInterface::class);
        $this->orderRepo->expects($this->once())
            ->method('averageOrderValue')
            ->willReturn($expected);
        $this->service = new AnalyticsService($this->orderRepo, $this->productRepo, $this->reviewRepo);

        $this->assertSame($expected, $this->service->averageOrderValue());
    }

    #[Test]
    public function pending_reviews_count_delegates(): void
    {
        $this->reviewRepo = $this->createMock(ReviewRepositoryInterface::class);
        $this->reviewRepo->expects($this->once())
            ->method('countPending')
            ->willReturn(7);
        $this->service = new AnalyticsService($this->orderRepo, $this->productRepo, $this->reviewRepo);

        $this->assertEquals(7, $this->service->pendingReviewsCount());
    }

    #[Test]
    public function low_stock_count_filters_positive_quantities(): void
    {
        $this->productRepo = $this->createMock(ProductRepositoryInterface::class);
        $this->productRepo->expects($this->once())
            ->method('findWithLowStock')
            ->willReturn([
                ['quantity' => 5],
                ['quantity' => 0],
                ['quantity' => 3],
                ['quantity' => -1],
            ]);
        $this->service = new AnalyticsService($this->orderRepo, $this->productRepo, $this->reviewRepo);

        $this->assertEquals(2, $this->service->lowStockCount());
    }

    #[Test]
    public function low_stock_count_returns_zero_when_all_zero(): void
    {
        $this->productRepo = $this->createMock(ProductRepositoryInterface::class);
        $this->productRepo->expects($this->once())
            ->method('findWithLowStock')
            ->willReturn([
                ['quantity' => 0],
                ['quantity' => -5],
            ]);
        $this->service = new AnalyticsService($this->orderRepo, $this->productRepo, $this->reviewRepo);

        $this->assertEquals(0, $this->service->lowStockCount());
    }

    #[Test]
    public function out_of_stock_count_filters_zero_and_negative(): void
    {
        $this->productRepo = $this->createMock(ProductRepositoryInterface::class);
        $this->productRepo->expects($this->once())
            ->method('findWithLowStock')
            ->willReturn([
                ['quantity' => 5],
                ['quantity' => 0],
                ['quantity' => 3],
                ['quantity' => -1],
            ]);
        $this->service = new AnalyticsService($this->orderRepo, $this->productRepo, $this->reviewRepo);

        $this->assertEquals(2, $this->service->outOfStockCount());
    }

    #[Test]
    public function out_of_stock_count_returns_zero_when_all_positive(): void
    {
        $this->productRepo = $this->createMock(ProductRepositoryInterface::class);
        $this->productRepo->expects($this->once())
            ->method('findWithLowStock')
            ->willReturn([
                ['quantity' => 5],
                ['quantity' => 10],
            ]);
        $this->service = new AnalyticsService($this->orderRepo, $this->productRepo, $this->reviewRepo);

        $this->assertEquals(0, $this->service->outOfStockCount());
    }

    #[Test]
    public function low_stock_and_out_of_stock_use_same_repo_call(): void
    {
        $data = [['quantity' => 5], ['quantity' => 0]];
        $this->productRepo = $this->createMock(ProductRepositoryInterface::class);
        $this->productRepo->expects($this->exactly(2))
            ->method('findWithLowStock')
            ->willReturn($data);
        $this->service = new AnalyticsService($this->orderRepo, $this->productRepo, $this->reviewRepo);

        $this->assertEquals(1, $this->service->lowStockCount());
        $this->assertEquals(1, $this->service->outOfStockCount());
    }

    #[Test]
    public function top_selling_uses_default_limit(): void
    {
        $this->productRepo = $this->createMock(ProductRepositoryInterface::class);
        $this->productRepo->expects($this->once())
            ->method('findTopSelling')
            ->with(10)
            ->willReturn([]);
        $this->service = new AnalyticsService($this->orderRepo, $this->productRepo, $this->reviewRepo);

        $this->assertEquals([], $this->service->topSellingProducts());
    }
}
