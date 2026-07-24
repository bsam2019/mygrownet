<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\TeamMember;

interface TeamMemberRepositoryInterface
{
    public function findById(int $id): ?TeamMember;

    public function findByBusiness(int $businessId): array;

    public function findByEmail(int $businessId, string $email): ?TeamMember;

    public function save(TeamMember $entity): TeamMember;

    public function delete(int $id): void;

    public function countActive(int $businessId): int;
}