<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Sale;
use App\Domain\StockFlow\ValueObjects\SaleId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;

interface SaleRepositoryInterface
{
    public function findById(SaleId $id): ?Sale;
    public function findByCompanyId(CompanyId $companyId, int $perPage = 50): array;
    public function findByCompanyIdAndDateBetween(CompanyId $companyId, DateTimeImmutable $from, DateTimeImmutable $to): array;
    public function findByDate(CompanyId $companyId, DateTimeImmutable $date): array;
    public function getTodayTotal(CompanyId $companyId): float;
    public function save(Sale $sale): Sale;
    public function nextReceiptNumber(): string;
    public function getMaxId(): int;
}
