<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\PayrollRun;

interface PayrollRunRepositoryInterface
{
    public function findById(int $id): ?PayrollRun;

    public function save(PayrollRun $payrollRun): PayrollRun;

    public function findByCompany(int $companyId): array;

    public function findLatest(int $companyId): ?PayrollRun;
}
