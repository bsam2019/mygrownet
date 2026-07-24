<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\FollowUpReminder;

interface FollowUpReminderRepositoryInterface
{
    public function findById(int $id): ?FollowUpReminder;

    public function findByBusiness(int $businessId, array $filters = []): array;

    public function save(FollowUpReminder $entity): FollowUpReminder;

    public function delete(int $id): void;
}