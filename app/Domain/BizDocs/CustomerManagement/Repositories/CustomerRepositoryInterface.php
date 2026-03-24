<?php

namespace App\Domain\BizDocs\CustomerManagement\Repositories;

use App\Domain\BizDocs\CustomerManagement\Entities\Customer;

interface CustomerRepositoryInterface
{
    public function save(Customer $customer): Customer;

    public function findById(int $id): ?Customer;

    public function findByBusiness(int $businessId, int $page = 1, int $perPage = 20): array;

    public function findByPhone(int $businessId, string $phone): ?Customer;

    public function findByEmail(int $businessId, string $email): ?Customer;

    public function search(int $businessId, string $query, int $page = 1, int $perPage = 20): array;

    public function delete(int $id): bool;

    public function countByBusiness(int $businessId): int;
}
