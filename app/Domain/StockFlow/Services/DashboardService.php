<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Repositories\CashRegisterRepositoryInterface;
use App\Domain\StockFlow\Repositories\CompanyRepositoryInterface;
use App\Domain\StockFlow\Repositories\ItemRepositoryInterface;
use App\Domain\StockFlow\Repositories\AuditRepositoryInterface;
use App\Domain\StockFlow\Repositories\PhysicalCountRepositoryInterface;
use App\Domain\StockFlow\Repositories\SaleRepositoryInterface;
use App\Domain\StockFlow\Repositories\PurchaseOrderRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;

class DashboardService
{
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
        private ItemRepositoryInterface $itemRepository,
        private AuditRepositoryInterface $auditRepository,
        private PhysicalCountRepositoryInterface $countRepository,
        private SaleRepositoryInterface $saleRepository,
        private PurchaseOrderRepositoryInterface $poRepository,
        private CashRegisterRepositoryInterface $cashRegisterRepository,
    ) {}

    public function getDashboardData(int $companyId): array
    {
        $company = $this->companyRepository->findById(CompanyId::fromInt($companyId));

        if (!$company) {
            return $this->emptyDashboardData();
        }

        $items = $this->itemRepository->findByCompanyId(CompanyId::fromInt($companyId));
        $audits = $this->auditRepository->findByCompanyId(CompanyId::fromInt($companyId));
        $counts = $this->countRepository->findByCompanyId(CompanyId::fromInt($companyId));
        $openRegister = $this->cashRegisterRepository->findOpenRegisterForToday(CompanyId::fromInt($companyId));
        $pendingPOs = $this->poRepository->findByCompanyIdAndStatus(CompanyId::fromInt($companyId), 'pending');
        $partialPOs = $this->poRepository->findByCompanyIdAndStatus(CompanyId::fromInt($companyId), 'partial');
        $inProgressCounts = array_filter($counts, fn($c) => $c->getStatus()->value() === 'in_progress');
        $unresolvedAudits = array_filter($audits, fn($a) => $a->getStatus()->value() !== 'finalized');

        // Low stock items (system_quantity > 0 but <= reorder_level)
        $lowStockItems = array_filter($items, function ($item) {
            $sq = $item->getSystemQuantity();
            $rl = $item->getReorderLevel();
            return $sq > 0 && $rl > 0 && $sq <= $rl;
        });

        // Out of stock items
        $outOfStockItems = array_filter($items, fn($item) => $item->getSystemQuantity() <= 0);

        // Today's sales total
        $today = new DateTimeImmutable();
        $todaysSales = $this->saleRepository->getTodayTotal(CompanyId::fromInt($companyId));

        // Recent activity (last 5 audits, counts, sales)
        $recentAudits = array_slice($audits, 0, 5);
        $recentCounts = array_slice($counts, 0, 5);

        $totalSystemValue = 0;
        foreach ($items as $item) {
            $totalSystemValue += $item->getStockValue()->toFloat();
        }

        return [
            'company' => $company->toArray(),
            'stats' => [
                'total_items' => count($items),
                'total_audits' => count($audits),
                'total_physical_counts' => count($counts),
                'total_system_value' => $totalSystemValue,
                'low_stock_count' => count($lowStockItems),
                'out_of_stock_count' => count($outOfStockItems),
                'pending_po_count' => count($pendingPOs),
                'partial_po_count' => count($partialPOs),
                'in_progress_count_count' => count($inProgressCounts),
                'unresolved_audit_count' => count($unresolvedAudits),
                'todays_sales' => $todaysSales,
                'has_open_register' => $openRegister !== null,
            ],
            'open_register' => $openRegister?->toArray(),
            'low_stock_items' => array_slice(array_map(fn($i) => $i->toArray(), $lowStockItems), 0, 5),
            'out_of_stock_items' => array_slice(array_map(fn($i) => $i->toArray(), $outOfStockItems), 0, 5),
            'pending_pos' => array_slice(array_map(fn($p) => $p->toArray(), $pendingPOs), 0, 5),
            'partial_pos' => array_slice(array_map(fn($p) => $p->toArray(), $partialPOs), 0, 5),
            'in_progress_counts' => array_slice(array_map(fn($c) => $c->toArray(), $inProgressCounts), 0, 5),
            'unresolved_audits' => array_slice(array_map(fn($a) => $a->toArray(), $unresolvedAudits), 0, 5),
            'recent_audits' => array_map(fn($a) => $a->toArray(), $recentAudits),
            'recent_counts' => array_map(fn($c) => $c->toArray(), $recentCounts),
        ];
    }

    private function emptyDashboardData(): array
    {
        return [
            'company' => null,
            'stats' => [
                'total_items' => 0,
                'total_audits' => 0,
                'total_physical_counts' => 0,
                'total_system_value' => 0,
                'low_stock_count' => 0,
                'out_of_stock_count' => 0,
                'pending_po_count' => 0,
                'partial_po_count' => 0,
                'in_progress_count_count' => 0,
                'unresolved_audit_count' => 0,
                'todays_sales' => 0,
                'has_open_register' => false,
            ],
            'open_register' => null,
            'low_stock_items' => [],
            'out_of_stock_items' => [],
            'pending_pos' => [],
            'partial_pos' => [],
            'in_progress_counts' => [],
            'unresolved_audits' => [],
            'recent_audits' => [],
            'recent_counts' => [],
        ];
    }

    public function getActiveCompanies(): array
    {
        return array_map(fn($c) => $c->toArray(), $this->companyRepository->findActive());
    }

    public function getAllCompanies(): array
    {
        return array_map(fn($c) => $c->toArray(), $this->companyRepository->findAll());
    }
}
