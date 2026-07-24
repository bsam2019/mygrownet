<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\MaterialCategory;

interface MaterialCategoryRepositoryInterface
{
    public function findById(int $id): ?MaterialCategory;

    public function save(MaterialCategory $category): MaterialCategory;

    public function findByCompany(int $companyId): array;

    public function findActive(int $companyId): array;
}
