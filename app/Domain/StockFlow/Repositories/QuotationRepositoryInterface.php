<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Quotation;
use App\Domain\StockFlow\ValueObjects\QuotationId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;

interface QuotationRepositoryInterface
{
    public function findById(QuotationId $id): ?Quotation;
    public function findByCompanyId(CompanyId $companyId, int $perPage = 50): array;
    public function findByCompanyIdAndDateBetween(CompanyId $companyId, DateTimeImmutable $from, DateTimeImmutable $to): array;
    public function findByStatus(CompanyId $companyId, string $status): array;
    public function save(Quotation $quotation): Quotation;
    public function nextQuotationNumber(): string;
    public function getMaxId(): int;
}
