<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\SupplierReturn;
use App\Domain\StockFlow\ValueObjects\SupplierReturnId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface SupplierReturnRepositoryInterface
{
    public function findById(SupplierReturnId $id): ?SupplierReturn;
    public function findByCompanyId(CompanyId $companyId): array;
    public function save(SupplierReturn $return): SupplierReturn;
    public function delete(SupplierReturnId $id): void;
}
