<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\PhysicalCount;
use App\Domain\StockFlow\Entities\CountItem;
use App\Domain\StockFlow\ValueObjects\PhysicalCountId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;

interface PhysicalCountRepositoryInterface
{
    public function findById(PhysicalCountId $id): ?PhysicalCount;
    public function findByCompanyId(CompanyId $companyId): array;
    public function save(PhysicalCount $count): PhysicalCount;
    public function saveCountItems(int $physicalCountId, array $items): void;

    /** @return CountItem[] */
    public function getCountItems(PhysicalCountId $id): array;

    public function updateCountItem(int $countItemId, float $physicalQuantity, float $variance, float $varianceValue): void;

    /** @return array [system_quantity, physical_quantity, unit_price, sa_bin_id, variance, variance_value] */
    public function getCountItemData(PhysicalCountId $id): array;
}
