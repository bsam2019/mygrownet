<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\PurchaseOrder;
use App\Domain\StockFlow\ValueObjects\PurchaseOrderId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;

interface PurchaseOrderRepositoryInterface
{
    public function findById(PurchaseOrderId $id): ?PurchaseOrder;
    public function findByCompanyId(CompanyId $companyId, int $perPage = 20): array;
    public function findByCompanyIdAndDateBetween(CompanyId $companyId, DateTimeImmutable $from, DateTimeImmutable $to): array;
    public function findByCompanyIdAndStatus(CompanyId $companyId, string $status): array;
    public function save(PurchaseOrder $order): PurchaseOrder;
    public function nextOrderNumber(): string;
    public function getMaxId(): int;
}
