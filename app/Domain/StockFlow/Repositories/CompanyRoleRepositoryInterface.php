<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\CompanyRole;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\CompanyRoleId;

interface CompanyRoleRepositoryInterface
{
    public function findById(CompanyRoleId $id): ?CompanyRole;
    public function findByCompanyId(CompanyId $companyId): array;
    public function findByCompanyIdAndSlug(CompanyId $companyId, string $slug): ?CompanyRole;
    public function save(CompanyRole $role): CompanyRole;
    public function delete(CompanyRoleId $id): void;
}