<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Branch;

interface BranchRepositoryInterface
{
    public function findById(int $id): ?Branch;

    public function save(Branch $branch): Branch;

    public function findByCompany(int $companyId): array;

    public function findActive(int $companyId): array;
}
