<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\TeamMember;

interface TeamMemberRepositoryInterface
{
    public function findById(int $id): ?TeamMember;

    public function save(TeamMember $teamMember): TeamMember;

    public function findByBusiness(int $businessId): array;

    public function findActive(int $businessId): array;

    public function findPending(int $businessId): array;

    public function findByUserAndBusiness(int $userId, int $businessId): ?TeamMember;
}
