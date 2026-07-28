<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\BankConnection;

interface BankConnectionRepositoryInterface
{
    public function findById(int $id): ?BankConnection;

    public function save(BankConnection $connection): BankConnection;

    public function findByBusiness(int $businessId): array;

    public function findActiveByBusiness(int $businessId): array;

    public function delete(int $id): void;
}
