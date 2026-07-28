<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\Account;

interface AccountRepositoryInterface
{
    public function findById(int $id): ?Account;

    public function save(Account $account): Account;

    public function delete(int $id): bool;

    public function findByBusiness(int $businessId): array;

    public function findActive(int $businessId): array;

    public function findByCode(int $businessId, string $code): ?Account;

    public function findOfType(int $businessId, string $type): array;

    /** @return Account[] */
    public function getChart(int $businessId): array;

    public function getChildren(int $parentId): array;

    public function getParent(int $accountId): ?Account;

    /** @return Account[] */
    public function getAccountsByStatementCategory(int $businessId, string $statementCategory): array;

    /** @return Account[] */
    public function findByCodes(int $businessId, array $codes): array;
}
