<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Project;

interface ProjectRepositoryInterface
{
    public function findById(int $id): ?Project;

    public function save(Project $project): Project;

    public function findByCompany(int $companyId): array;

    public function findByStatus(int $companyId, string $status): array;
}
