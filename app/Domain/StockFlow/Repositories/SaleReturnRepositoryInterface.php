<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\SaleReturn;
use App\Domain\StockFlow\ValueObjects\SaleReturnId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface SaleReturnRepositoryInterface
{
    public function findById(SaleReturnId $id): ?SaleReturn;
    public function findByCompanyId(CompanyId $companyId): array;
    public function save(SaleReturn $return): SaleReturn;
    public function delete(SaleReturnId $id): void;
}
