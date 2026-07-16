<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Branch;
use App\Domain\StockFlow\ValueObjects\BranchId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface BranchRepositoryInterface
{
    public function findById(BranchId $id): ?Branch;
    public function findByCompanyId(CompanyId $companyId): array;
    public function findActive(CompanyId $companyId): array;
    public function findHeadOffice(CompanyId $companyId): ?Branch;
    public function findByCode(CompanyId $companyId, string $code): ?Branch;
    public function save(Branch $branch): Branch;
    public function delete(BranchId $id): void;
}
