<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Customer;

interface CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer;

    public function save(Customer $customer): Customer;

    public function findByCompany(int $companyId): array;

    public function findActive(int $companyId): array;

    public function findBySearch(int $companyId, string $search): array;

    public function findByStatus(int $companyId, string $status): array;

    public function findWithOutstanding(int $companyId): array;
}
