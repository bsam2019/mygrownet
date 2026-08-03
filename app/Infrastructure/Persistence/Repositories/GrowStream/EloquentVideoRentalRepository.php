<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoRental;
use App\Domain\GrowStream\Repositories\VideoRentalRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentVideoRentalRepository implements VideoRentalRepositoryInterface
{
    public function findById(int $id): ?VideoRental
    {
        return VideoRental::find($id);
    }

    public function forUser(int $userId, array $relations = []): Collection
    {
        return VideoRental::with($relations)
            ->where('user_id', $userId)
            ->orderBy('granted_at', 'desc')
            ->get();
    }

    public function activeRental(int $userId, int $videoId): ?VideoRental
    {
        return VideoRental::where('user_id', $userId)
            ->where('video_id', $videoId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest('granted_at')
            ->first();
    }

    public function hasActiveRental(int $userId, int $videoId): bool
    {
        return $this->activeRental($userId, $videoId) !== null;
    }

    public function create(array $data): VideoRental
    {
        return VideoRental::create($data);
    }

    public function update(VideoRental $rental, array $data): VideoRental
    {
        $rental->update($data);

        return $rental->fresh();
    }

    public function query(): Builder
    {
        return VideoRental::query();
    }
}
