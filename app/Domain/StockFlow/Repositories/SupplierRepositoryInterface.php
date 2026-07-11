<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Supplier;
use App\Domain\StockFlow\ValueObjects\SupplierId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface SupplierRepositoryInterface
{
    public function findById(SupplierId $id): ?Supplier;
    public function findByCompanyId(CompanyId $companyId): array;
    public function save(Supplier $supplier): Supplier;
    public function delete(SupplierId $id): void;
}
