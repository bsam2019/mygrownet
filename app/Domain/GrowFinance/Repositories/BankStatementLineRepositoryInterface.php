<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\BankStatementLine;

interface BankStatementLineRepositoryInterface
{
    public function findById(int $id): ?BankStatementLine;

    public function save(BankStatementLine $bankStatementLine): BankStatementLine;

    public function findByStatement(int $statementId): array;
}
