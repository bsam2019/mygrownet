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
use App\Domain\StockFlow\Repositories\LotRepositoryInterface;
use App\Domain\StockFlow\Repositories\BranchRepositoryInterface;
use App\Domain\StockFlow\Entities\Company;
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
        private LotRepositoryInterface $lotRepository,
        private BranchRepositoryInterface $branchRepository,
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

        // Branch count
        $branches = $this->branchRepository->findByCompanyId(CompanyId::fromInt($companyId));

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

        // Expiry KPIs from lots
        $now = new DateTimeImmutable();
        $expiring30 = $expiring60 = $expiring90 = 0;
        $lots = $this->lotRepository->findByCompanyId(CompanyId::fromInt($companyId));
        foreach ($lots as $lot) {
            $lotArr = $lot->toArray();
            if ($lotArr['expiry_date'] && $lot->getCurrentQuantity() > 0) {
                $expDate = new DateTimeImmutable($lotArr['expiry_date']);
                $days = (int) $now->diff($expDate)->format('%a');
                if ($days <= 30) $expiring30++;
                elseif ($days <= 60) $expiring60++;
                elseif ($days <= 90) $expiring90++;
            }
        }
        // Also check items with direct expiry dates
        foreach ($items as $item) {
            $expiry = $item->getExpiryDate();
            if ($expiry && $item->getSystemQuantity() > 0) {
                $days = (int) $now->diff($expiry)->format('%a');
                if ($days <= 30) $expiring30++;
                elseif ($days <= 60) $expiring60++;
                elseif ($days <= 90) $expiring90++;
            }
        }

        return [
            'company' => $company->toArray(),
            'stats' => [
                'total_items' => count($items),
                'total_branches' => count($branches),
                'total_audits' => count($audits),
                'total_physical_counts' => count($counts),
                'total_system_value' => $totalSystemValue,
                'low_stock_count' => count($lowStockItems),
                'out_of_stock_count' => count($outOfStockItems),
                'pending_po_count' => count($pendingPOs),
                'partial_po_count' => count($partialPOs),
                'in_progress_count' => count($inProgressCounts),
                'unresolved_audit_count' => count($unresolvedAudits),
                'todays_sales' => $todaysSales,
                'has_open_register' => $openRegister !== null,
                'expiring_in_30_days' => $expiring30,
                'expiring_in_60_days' => $expiring60,
                'expiring_in_90_days' => $expiring90,
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
                'total_branches' => 0,
                'total_audits' => 0,
                'total_physical_counts' => 0,
                'total_system_value' => 0,
                'low_stock_count' => 0,
                'out_of_stock_count' => 0,
                'pending_po_count' => 0,
                'partial_po_count' => 0,
                'in_progress_count' => 0,
                'unresolved_audit_count' => 0,
                'todays_sales' => 0,
                'has_open_register' => false,
                'expiring_in_30_days' => 0,
                'expiring_in_60_days' => 0,
                'expiring_in_90_days' => 0,
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

    public function updateCompany(int $companyId, array $data): array
    {
        $companyIdVO = CompanyId::fromInt($companyId);
        $company = $this->companyRepository->findById($companyIdVO);

        if (!$company) {
            throw new \RuntimeException('Company not found');
        }

        $updated = Company::reconstitute(
            id: $companyIdVO,
            name: $data['name'] ?? $company->getName(),
            subdomain: $company->getSubdomain(),
            email: $data['email'] ?? $company->getEmail(),
            phone: $data['phone'] ?? $company->getPhone(),
            address: $data['address'] ?? $company->getAddress(),
            city: $data['city'] ?? $company->getCity(),
            country: $data['country'] ?? $company->getCountry(),
            contactPerson: $data['contact_person'] ?? $company->getContactPerson(),
            currency: $data['currency'] ?? $company->getCurrency(),
            status: $company->getStatus(),
            logoPath: $data['logo_path'] ?? $company->getLogoPath(),
            tagline: $data['tagline'] ?? $company->getTagline(),
            brandColor: $data['brand_color'] ?? $company->getBrandColor(),
            settings: $data['settings'] ?? $company->getSettings(),
            createdAt: $company->getCreatedAt(),
            updatedAt: new DateTimeImmutable(),
        );

        $this->companyRepository->save($updated);

        return $updated->toArray();
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
