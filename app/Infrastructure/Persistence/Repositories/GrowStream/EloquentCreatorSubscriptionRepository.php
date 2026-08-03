<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorSubscription;
use App\Domain\GrowStream\Repositories\CreatorSubscriptionRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentCreatorSubscriptionRepository implements CreatorSubscriptionRepositoryInterface
{
    public function findById(int $id): ?CreatorSubscription
    {
        return CreatorSubscription::find($id);
    }

    public function activeForUserAndCreator(int $userId, int $creatorId): ?CreatorSubscription
    {
        return CreatorSubscription::where('user_id', $userId)
            ->where('creator_id', $creatorId)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->first();
    }

    public function isSubscribed(int $userId, int $creatorId): bool
    {
        return $this->activeForUserAndCreator($userId, $creatorId) !== null;
    }

    public function forCreator(int $creatorId, array $relations = []): Collection
    {
        return CreatorSubscription::with($relations)
            ->where('creator_id', $creatorId)
            ->orderBy('started_at', 'desc')
            ->get();
    }

    public function create(array $data): CreatorSubscription
    {
        return CreatorSubscription::create($data);
    }

    public function update(CreatorSubscription $subscription, array $data): CreatorSubscription
    {
        $subscription->update($data);

        return $subscription->fresh();
    }

    public function subscriberCount(int $creatorId): int
    {
        return CreatorSubscription::where('creator_id', $creatorId)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->count();
    }

    public function query(): Builder
    {
        return CreatorSubscription::query();
    }
}
