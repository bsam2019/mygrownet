<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Item;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface ItemRepositoryInterface
{
    public function findById(ItemId $id): ?Item;
    public function findByCompanyId(CompanyId $companyId): array;
    public function findByCompanyIdWithFilters(CompanyId $companyId, array $filters = []): array;
    public function findInStock(CompanyId $companyId): array;
    public function findLowStock(CompanyId $companyId): array;
    public function findOutOfStock(CompanyId $companyId): array;
    public function save(Item $item): Item;
    public function delete(ItemId $id): void;
    public function countByCompanyId(CompanyId $companyId): int;
}
