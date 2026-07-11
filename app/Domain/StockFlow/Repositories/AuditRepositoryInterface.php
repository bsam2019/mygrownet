<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Audit;
use App\Domain\StockFlow\Entities\AuditItem;
use App\Domain\StockFlow\ValueObjects\AuditId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface AuditRepositoryInterface
{
    public function findById(AuditId $id): ?Audit;
    public function findByCompanyId(CompanyId $companyId): array;
    public function save(Audit $audit): Audit;
    public function createAuditItems(int $auditId, array $items): void;
    public function createReconciliation(int $auditId, array $data): void;
    public function getAuditItemData(AuditId $id): array;
    public function nextReference(): string;
}
