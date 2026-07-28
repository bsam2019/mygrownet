<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\GroupConsolidation;

interface GroupConsolidationRepositoryInterface
{
    public function findById(int $id): ?GroupConsolidation;

    /** @return GroupConsolidation[] */
    public function findByGroup(int $groupId): array;

    /** @return GroupConsolidation[] */
    public function findByPeriod(int $businessId, string $period): array;

    public function findByBusinessAndPeriod(int $businessId, string $period): ?GroupConsolidation;

    public function save(GroupConsolidation $consolidation): GroupConsolidation;

    public function delete(int $id): void;
}
