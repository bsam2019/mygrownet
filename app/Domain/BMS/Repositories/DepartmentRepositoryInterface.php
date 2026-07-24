<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Department;

interface DepartmentRepositoryInterface
{
    public function findById(int $id): ?Department;

    public function save(Department $department): Department;

    public function findByCompany(int $companyId): array;

    public function findActive(int $companyId): array;
}
