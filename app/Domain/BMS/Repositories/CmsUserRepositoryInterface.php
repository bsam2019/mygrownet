<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\CmsUser;

interface CmsUserRepositoryInterface
{
    public function findById(int $id): ?CmsUser;

    public function save(CmsUser $user): CmsUser;

    public function findByCompany(int $companyId): array;

    public function findByUser(int $userId): ?CmsUser;

    public function findActiveByCompany(int $companyId): array;
}
