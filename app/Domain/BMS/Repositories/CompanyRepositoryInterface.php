<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Company;

interface CompanyRepositoryInterface
{
    public function findById(int $id): ?Company;

    public function save(Company $company): Company;

    public function findByOrganization(int $organizationId): array;

    public function findAll(): array;

    public function findActive(): array;
}
