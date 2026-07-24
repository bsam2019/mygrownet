<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Equipment;

interface EquipmentRepositoryInterface
{
    public function findById(int $id): ?Equipment;

    public function save(Equipment $equipment): Equipment;

    public function findByCompany(int $companyId): array;

    public function findActive(int $companyId): array;
}
