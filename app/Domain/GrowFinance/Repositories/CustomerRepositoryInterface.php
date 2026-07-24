<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\Customer;

interface CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer;

    public function save(Customer $customer): Customer;

    public function findByBusiness(int $businessId): array;

    public function findActive(int $businessId): array;

    public function findWithOutstanding(int $businessId): array;
}
