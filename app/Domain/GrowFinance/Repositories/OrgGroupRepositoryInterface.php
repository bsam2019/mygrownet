<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\OrgGroup;

interface OrgGroupRepositoryInterface
{
    public function findById(int $id): ?OrgGroup;

    /** @return OrgGroup[] */
    public function findByParent(int $parentOrgId): array;

    public function findByChild(int $childOrgId): ?OrgGroup;

    /** @return OrgGroup[] */
    public function findSubsidiaries(int $parentOrgId): array;

    public function save(OrgGroup $orgGroup): OrgGroup;

    public function delete(int $id): void;
}
