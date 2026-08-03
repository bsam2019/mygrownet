<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorTip;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface CreatorTipRepositoryInterface
{
    public function findById(int $id): ?CreatorTip;

    public function forCreator(int $creatorId, array $relations = []): Collection;

    public function paginateForCreator(int $creatorId, int $perPage = 20, array $relations = []): LengthAwarePaginator;

    public function create(array $data): CreatorTip;

    public function totalForCreator(int $creatorId): float;

    public function countForCreator(int $creatorId): int;

    public function query(): Builder;
}
