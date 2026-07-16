<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Warehouse;
use App\Domain\StockFlow\ValueObjects\WarehouseId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface WarehouseRepositoryInterface
{
    public function findById(WarehouseId $id): ?Warehouse;
    public function findByCompanyId(CompanyId $companyId): array;
    public function findDefault(CompanyId $companyId): ?Warehouse;
    public function save(Warehouse $warehouse): Warehouse;
    public function delete(WarehouseId $id): void;
}
