<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\FinancialReport;
use App\Domain\Investor\ValueObjects\ReportType;

interface FinancialReportRepositoryInterface
{
    public function findById(int $id): ?FinancialReport;
    
    public function findByType(ReportType $type): array;
    
    public function findLatestPublished(int $limit = 5): array;
    
    public function findAll(): array;
    
    public function findPublished(): array;
    
    public function save(FinancialReport $report): FinancialReport;
    
    public function delete(int $id): void;
    
    public function getTotalReports(): int;
    
    public function getLatestReport(): ?FinancialReport;
    
    public function getReportsByDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array;
    
    public function getRevenueBreakdown(int $reportId): array;
    
    public function saveRevenueBreakdown(int $reportId, array $breakdown): void;
}