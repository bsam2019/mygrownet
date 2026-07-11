<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\AuditId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\AuditStatus;
use DateTimeImmutable;

class Audit
{
    private function __construct(
        private AuditId $id,
        private CompanyId $companyId,
        private string $title,
        private ?string $reportReference,
        private DateTimeImmutable $auditDate,
        private AuditStatus $status,
        private Money $totalSystemValue,
        private Money $totalPhysicalValue,
        private Money $totalVariance,
        private Money $unaccountedValue,
        private Money $totalRecordedSales,
        private ?string $executiveSummary,
        private ?string $recommendations,
        private ?string $conclusion,
        private ?string $preparedFor,
        private ?string $preparedBy,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        private array $items = [],
    ) {}

    public static function create(
        CompanyId $companyId,
        string $title,
        DateTimeImmutable $auditDate,
        Money $totalSystemValue,
        Money $totalPhysicalValue,
        ?string $preparedFor = null,
        ?string $preparedBy = null,
        ?string $reportReference = null,
    ): self {
        $variance = $totalSystemValue->subtract($totalPhysicalValue);
        return new self(
            AuditId::generate(), $companyId, $title, $reportReference, $auditDate,
            AuditStatus::draft(), $totalSystemValue, $totalPhysicalValue, $variance,
            $variance, Money::zero(), null, null, null, $preparedFor, $preparedBy,
            new DateTimeImmutable(), new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        AuditId $id, CompanyId $companyId, string $title, ?string $reportReference,
        DateTimeImmutable $auditDate, AuditStatus $status,
        Money $totalSystemValue, Money $totalPhysicalValue, Money $totalVariance,
        Money $unaccountedValue, Money $totalRecordedSales,
        ?string $executiveSummary, ?string $recommendations, ?string $conclusion,
        ?string $preparedFor, ?string $preparedBy,
        DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id, $companyId, $title, $reportReference, $auditDate, $status,
            $totalSystemValue, $totalPhysicalValue, $totalVariance,
            $unaccountedValue, $totalRecordedSales, $executiveSummary,
            $recommendations, $conclusion, $preparedFor, $preparedBy,
            $createdAt, $updatedAt,
        );
    }

    public function addItem(AuditItem $item): void { $this->items[] = $item; }
    public function getItems(): array { return $this->items; }

    public function finalize(Money $totalRecordedSales, ?string $executiveSummary = null, ?string $recommendations = null, ?string $conclusion = null): void
    {
        $this->status = AuditStatus::finalized();
        $this->totalRecordedSales = $totalRecordedSales;
        $this->unaccountedValue = $this->totalVariance->subtract($totalRecordedSales);
        $this->executiveSummary = $executiveSummary;
        $this->recommendations = $recommendations;
        $this->conclusion = $conclusion;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): AuditId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getTitle(): string { return $this->title; }
    public function getReportReference(): ?string { return $this->reportReference; }
    public function getAuditDate(): DateTimeImmutable { return $this->auditDate; }
    public function getStatus(): AuditStatus { return $this->status; }
    public function getTotalSystemValue(): Money { return $this->totalSystemValue; }
    public function getTotalPhysicalValue(): Money { return $this->totalPhysicalValue; }
    public function getTotalVariance(): Money { return $this->totalVariance; }
    public function getUnaccountedValue(): Money { return $this->unaccountedValue; }
    public function getTotalRecordedSales(): Money { return $this->totalRecordedSales; }
    public function getExecutiveSummary(): ?string { return $this->executiveSummary; }
    public function getRecommendations(): ?string { return $this->recommendations; }
    public function getConclusion(): ?string { return $this->conclusion; }
    public function getPreparedFor(): ?string { return $this->preparedFor; }
    public function getPreparedBy(): ?string { return $this->preparedBy; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'title' => $this->title,
            'report_reference' => $this->reportReference,
            'audit_date' => $this->auditDate->format('Y-m-d'),
            'status' => $this->status->value(),
            'total_system_value' => $this->totalSystemValue->toFloat(),
            'total_physical_value' => $this->totalPhysicalValue->toFloat(),
            'total_variance' => $this->totalVariance->toFloat(),
            'unaccounted_value' => $this->unaccountedValue->toFloat(),
            'total_recorded_sales' => $this->totalRecordedSales->toFloat(),
            'executive_summary' => $this->executiveSummary,
            'recommendations' => $this->recommendations,
            'conclusion' => $this->conclusion,
            'prepared_for' => $this->preparedFor,
            'prepared_by' => $this->preparedBy,
            'items' => array_map(fn($i) => $i instanceof AuditItem ? $i->toArray() : $i, $this->items),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
