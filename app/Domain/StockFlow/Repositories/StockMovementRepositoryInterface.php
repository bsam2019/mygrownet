<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\StockMovement;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;

interface StockMovementRepositoryInterface
{
    /** @return StockMovement[] */
    public function findByCompanyId(CompanyId $companyId, int $perPage = 50): array;

    /** @return StockMovement[] */
    public function findByItemId(ItemId $itemId): array;

    public function save(StockMovement $movement): StockMovement;
}
