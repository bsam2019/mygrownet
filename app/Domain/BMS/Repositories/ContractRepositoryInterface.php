<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Contract;

interface ContractRepositoryInterface
{
    public function findById(int $id): ?Contract;

    public function save(Contract $contract): Contract;

    public function findByCompany(int $companyId): array;

    public function findByStatus(int $companyId, string $status): array;

    public function findExpiring(int $companyId): array;
}
