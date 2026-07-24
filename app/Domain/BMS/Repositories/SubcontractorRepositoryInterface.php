<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Subcontractor;

interface SubcontractorRepositoryInterface
{
    public function findById(int $id): ?Subcontractor;

    public function save(Subcontractor $subcontractor): Subcontractor;

    public function findByCompany(int $companyId): array;

    public function findActive(int $companyId): array;
}
