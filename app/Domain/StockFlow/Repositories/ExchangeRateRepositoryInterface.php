<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\ExchangeRate;
use App\Domain\StockFlow\ValueObjects\ExchangeRateId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface ExchangeRateRepositoryInterface
{
    public function findById(ExchangeRateId $id): ?ExchangeRate;
    public function findByCompanyId(CompanyId $companyId): array;
    public function findRate(CompanyId $companyId, string $from, string $to): ?\App\Domain\StockFlow\Entities\ExchangeRate;
    public function save(ExchangeRate $rate): ExchangeRate;
    public function delete(ExchangeRateId $id): void;
}
