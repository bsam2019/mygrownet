<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\CompanyUser;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\CompanyUserId;

interface CompanyUserRepositoryInterface
{
    public function findById(CompanyUserId $id): ?CompanyUser;
    public function findByCompanyId(CompanyId $companyId, ?string $status = null): array;
    public function findByCompanyIdAndUserId(CompanyId $companyId, int $userId): ?CompanyUser;
    public function findPendingInvitations(CompanyId $companyId): array;
    public function save(CompanyUser $companyUser): CompanyUser;
    public function delete(CompanyUserId $id): void;
}