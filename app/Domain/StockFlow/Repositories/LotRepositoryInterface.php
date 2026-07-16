<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Lot;
use App\Domain\StockFlow\ValueObjects\LotId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;

interface LotRepositoryInterface
{
    public function findById(LotId $id): ?Lot;
    public function findByCompanyId(CompanyId $companyId): array;
    public function findByItemId(CompanyId $companyId, ItemId $itemId): array;
    public function findByLotNumber(CompanyId $companyId, string $lotNumber): ?Lot;
    public function save(Lot $lot): Lot;
    public function delete(LotId $id): void;
}
