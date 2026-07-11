<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Bin;
use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface BinRepositoryInterface
{
    public function findById(BinId $id): ?Bin;
    public function findByCompanyId(CompanyId $companyId): array;
    public function save(Bin $bin): Bin;
    public function delete(BinId $id): void;
}
