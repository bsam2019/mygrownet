<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\ControlledMedicine;
use App\Domain\StockFlow\ValueObjects\ControlledMedicineId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;

interface ControlledMedicineRepositoryInterface
{
    public function findById(ControlledMedicineId $id): ?ControlledMedicine;
    public function findByCompanyId(CompanyId $companyId): array;
    public function findByItem(CompanyId $companyId, ItemId $itemId): array;
    public function save(ControlledMedicine $entry): ControlledMedicine;
}
