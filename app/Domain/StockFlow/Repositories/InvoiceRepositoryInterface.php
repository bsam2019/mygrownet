<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Invoice;
use App\Domain\StockFlow\ValueObjects\InvoiceId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;

interface InvoiceRepositoryInterface
{
    public function findById(InvoiceId $id): ?Invoice;
    public function findByCompanyId(CompanyId $companyId, int $perPage = 50): array;
    public function findByCompanyIdAndDateBetween(CompanyId $companyId, DateTimeImmutable $from, DateTimeImmutable $to): array;
    public function findByStatus(CompanyId $companyId, string $status): array;
    public function findOverdue(CompanyId $companyId): array;
    public function save(Invoice $invoice): Invoice;
    public function nextInvoiceNumber(): string;
    public function getMaxId(): int;
}
