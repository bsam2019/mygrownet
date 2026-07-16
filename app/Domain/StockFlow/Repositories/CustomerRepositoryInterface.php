<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Customer;
use App\Domain\StockFlow\ValueObjects\CustomerId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface CustomerRepositoryInterface
{
    public function findById(CustomerId $id): ?Customer;
    public function findByCompanyId(CompanyId $companyId): array;
    public function findByName(CompanyId $companyId, string $name): array;
    public function save(Customer $customer): Customer;
    public function delete(CustomerId $id): void;
    public function countByCompanyId(CompanyId $companyId): int;
}
