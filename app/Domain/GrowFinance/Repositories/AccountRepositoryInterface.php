<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\Account;

interface AccountRepositoryInterface
{
    public function findById(int $id): ?Account;

    public function save(Account $account): Account;

    public function findByBusiness(int $businessId): array;

    public function findActive(int $businessId): array;

    public function findByCode(int $businessId, string $code): ?Account;

    public function findOfType(int $businessId, string $type): array;
}
