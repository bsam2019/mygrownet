<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\BankAccount;

interface BankAccountRepositoryInterface
{
    public function findById(int $id): ?BankAccount;

    public function save(BankAccount $bankAccount): BankAccount;

    public function findByBusiness(int $businessId): array;

    public function findActive(int $businessId): array;

    public function findDefault(int $businessId): ?BankAccount;
}
