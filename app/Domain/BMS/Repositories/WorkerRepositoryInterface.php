<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Worker;

interface WorkerRepositoryInterface
{
    public function findById(int $id): ?Worker;

    public function save(Worker $worker): Worker;

    public function findByCompany(int $companyId): array;

    public function findActive(int $companyId): array;

    public function findByDepartment(int $companyId, string $department): array;
}
