<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Audit;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\AuditRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\AuditId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Throwable;

class AuditService
{
    public function __construct(
        private AuditRepositoryInterface $auditRepository,
    ) {}

    public function getAuditById(int $auditId, int $companyId): ?Audit
    {
        $audit = $this->auditRepository->findById(AuditId::fromInt($auditId));
        if ($audit && $audit->getCompanyId()->toInt() !== $companyId) {
            return null;
        }
        return $audit;
    }

    public function getAuditsForCompany(int $companyId): array
    {
        return $this->auditRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }

    /**
     * Derive recorded sales from the ledger for the period up to the audit date.
     */
    public function deriveRecordedSales(int $companyId, string $auditDate): float
    {
        return (float) DB::table('sa_stock_movements')
            ->where('sa_company_id', $companyId)
            ->where('type', 'sale_out')
            ->whereDate('created_at', '<=', $auditDate)
            ->sum(DB::raw('ABS(quantity) * unit_price'));
    }

    public function finalizeAudit(int $auditId, array $data): Audit
    {
        try {
            $audit = $this->auditRepository->findById(AuditId::fromInt($auditId));
            if (!$audit) {
                throw new OperationFailedException('finalize audit', 'Audit not found');
            }

            // If no recorded sales provided, derive from ledger
            $recordedSales = isset($data['total_recorded_sales'])
                ? (float) $data['total_recorded_sales']
                : $this->deriveRecordedSales(
                    $audit->getCompanyId()->toInt(),
                    $audit->getAuditDate()->format('Y-m-d')
                );

            $audit->finalize(
                totalRecordedSales: Money::fromFloat($recordedSales),
                executiveSummary: $data['executive_summary'] ?? $audit->getExecutiveSummary(),
                recommendations: $data['recommendations'] ?? $audit->getRecommendations(),
                conclusion: $data['conclusion'] ?? null,
            );

            return $this->auditRepository->save($audit);
        } catch (Throwable $e) {
            throw new OperationFailedException('finalize audit', $e->getMessage());
        }
    }

    public function getAuditItemData(int $auditId): array
    {
        return $this->auditRepository->getAuditItemData(AuditId::fromInt($auditId));
    }
}
