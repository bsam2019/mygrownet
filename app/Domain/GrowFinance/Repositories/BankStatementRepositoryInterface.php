<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\BankStatement;

interface BankStatementRepositoryInterface
{
    public function findById(int $id): ?BankStatement;

    public function save(BankStatement $bankStatement): BankStatement;

    public function findByBankAccount(int $bankAccountId): array;

    public function findByBusiness(int $businessId): array;
}
