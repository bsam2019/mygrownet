<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\TaxRate;
use App\Domain\StockFlow\ValueObjects\TaxRateId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface TaxRateRepositoryInterface
{
    public function findById(TaxRateId $id): ?TaxRate;
    public function findByCompanyId(CompanyId $companyId): array;
    public function findDefault(CompanyId $companyId): ?TaxRate;
    public function save(TaxRate $taxRate): TaxRate;
    public function delete(TaxRateId $id): void;
}
