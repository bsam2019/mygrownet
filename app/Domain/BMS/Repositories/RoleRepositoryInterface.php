<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Role;

interface RoleRepositoryInterface
{
    public function findById(int $id): ?Role;

    public function save(Role $role): Role;

    public function findByCompany(int $companyId): array;

    public function findByName(int $companyId, string $name): ?Role;
}
