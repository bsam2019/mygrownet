<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Company;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface CompanyRepositoryInterface
{
    public function findById(CompanyId $id): ?Company;
    public function findBySubdomain(string $subdomain): ?Company;
    public function findActive(): array;
    public function findAll(): array;
    public function save(Company $company): Company;
}
