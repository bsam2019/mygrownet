<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorPayout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface CreatorPayoutRepositoryInterface
{
    public function findById(int $id): ?CreatorPayout;

    public function forCreator(int $creatorId, array $relations = []): Collection;

    public function paginateForCreator(int $creatorId, int $perPage = 20, array $relations = []): LengthAwarePaginator;

    public function create(array $data): CreatorPayout;

    public function update(CreatorPayout $payout, array $data): CreatorPayout;

    public function totalPaidForCreator(int $creatorId): float;

    public function query(): Builder;
}
