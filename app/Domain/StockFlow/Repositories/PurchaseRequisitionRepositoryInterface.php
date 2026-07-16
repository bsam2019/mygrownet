<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\PurchaseRequisition;
use App\Domain\StockFlow\ValueObjects\PurchaseRequisitionId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface PurchaseRequisitionRepositoryInterface
{
    public function findById(PurchaseRequisitionId $id): ?PurchaseRequisition;
    public function findByCompanyId(CompanyId $companyId): array;
    public function findByStatus(CompanyId $companyId, string $status): array;
    public function save(PurchaseRequisition $requisition): PurchaseRequisition;
    public function delete(PurchaseRequisitionId $id): void;
}
