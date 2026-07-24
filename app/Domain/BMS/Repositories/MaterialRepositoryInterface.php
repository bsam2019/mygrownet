<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Material;

interface MaterialRepositoryInterface
{
    public function findById(int $id): ?Material;

    public function save(Material $material): Material;

    public function findByCompany(int $companyId): array;

    public function findByCategory(int $categoryId): array;
}
