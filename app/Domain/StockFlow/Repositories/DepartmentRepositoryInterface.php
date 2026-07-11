<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Department;
use App\Domain\StockFlow\ValueObjects\DepartmentId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface DepartmentRepositoryInterface
{
    public function findById(DepartmentId $id): ?Department;
    public function findByCompanyId(CompanyId $companyId): array;
    public function save(Department $department): Department;
    public function delete(DepartmentId $id): void;
}
