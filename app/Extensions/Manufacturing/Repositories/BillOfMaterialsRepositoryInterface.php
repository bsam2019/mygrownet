<?php

declare(strict_types=1);

namespace App\Extensions\Manufacturing\Repositories;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Extensions\Manufacturing\Entities\BillOfMaterials;
use App\Extensions\Manufacturing\ValueObjects\BillOfMaterialsId;

interface BillOfMaterialsRepositoryInterface
{
    public function findById(BillOfMaterialsId $id): ?BillOfMaterials;
    public function findByCompany(CompanyId $companyId): array;
    public function findByItem(CompanyId $companyId, int $itemId): array;
    public function save(BillOfMaterials $bom): BillOfMaterials;
    public function delete(BillOfMaterialsId $id): void;
}
