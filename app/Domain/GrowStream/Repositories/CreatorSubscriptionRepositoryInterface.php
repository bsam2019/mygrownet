<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorSubscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface CreatorSubscriptionRepositoryInterface
{
    public function findById(int $id): ?CreatorSubscription;

    public function activeForUserAndCreator(int $userId, int $creatorId): ?CreatorSubscription;

    public function isSubscribed(int $userId, int $creatorId): bool;

    public function forCreator(int $creatorId, array $relations = []): Collection;

    public function create(array $data): CreatorSubscription;

    public function update(CreatorSubscription $subscription, array $data): CreatorSubscription;

    public function subscriberCount(int $creatorId): int;

    /**
     * @return int[] user ids with an active subscription to the creator
     */
    public function activeSubscriberIdsForCreator(int $creatorId): array;

    public function query(): Builder;
}
