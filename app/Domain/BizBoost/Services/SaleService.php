<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Services;

use App\Domain\BizBoost\Entities\Sale;
use App\Domain\BizBoost\Repositories\SaleRepositoryInterface;
use App\Domain\BizBoost\Repositories\CustomerRepositoryInterface;

class SaleService
{
    public function __construct(
        private SaleRepositoryInterface $saleRepo,
        private CustomerRepositoryInterface $customerRepo,
    ) {}

    public function getSales(int $businessId, array $filters = []): array
    {
        return $this->saleRepo->findByBusiness($businessId, $filters);
    }

    public function findSale(int $id): ?Sale
    {
        return $this->saleRepo->findById($id);
    }

    public function createSale(array $data): Sale
    {
        $sale = $this->saleRepo->save(Sale::create($data));

        if ($sale->customerId) {
            $this->customerRepo->updatePurchaseStats($sale->customerId);
        }

        return $sale;
    }

    public function deleteSale(int $id): void
    {
        $this->saleRepo->delete($id);
    }

    public function getStats(int $businessId, string $today, string $startOfWeek, string $startOfMonth, string $endOfMonth, string $startOfLastMonth, string $endOfLastMonth): array
    {
        $thisMonthTotal = $this->saleRepo->sumByBusiness($businessId, ['date_from' => $startOfMonth, 'date_to' => $endOfMonth]);
        $lastMonthTotal = $this->saleRepo->sumByBusiness($businessId, ['date_from' => $startOfLastMonth, 'date_to' => $endOfLastMonth]);

        return [
            'today' => $this->saleRepo->sumByBusiness($businessId, ['date_from' => $today, 'date_to' => $today]),
            'this_week' => $this->saleRepo->sumByBusiness($businessId, ['date_from' => $startOfWeek]),
            'this_month' => $thisMonthTotal,
            'last_month' => $lastMonthTotal,
            'month_change' => $lastMonthTotal > 0
                ? round((($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1)
                : 0,
        ];
    }

    public function getSalesReport(int $businessId, string $startDate, string $endDate): array
    {
        return $this->saleRepo->getSalesReport($businessId, $startDate, $endDate);
    }
}