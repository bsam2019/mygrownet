<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoRental;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface VideoRentalRepositoryInterface
{
    public function findById(int $id): ?VideoRental;

    public function forUser(int $userId, array $relations = []): Collection;

    public function activeRental(int $userId, int $videoId): ?VideoRental;

    public function hasActiveRental(int $userId, int $videoId): bool;

    public function create(array $data): VideoRental;

    public function update(VideoRental $rental, array $data): VideoRental;

    public function query(): Builder;
}
