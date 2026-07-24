<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CreatorProfileRepositoryInterface
{
    public function findById(int $id): ?CreatorProfile;

    public function findByUserId(int $userId): ?CreatorProfile;

    public function findAll(array $relations = []): Collection;

    public function paginate(int $perPage = 20, array $relations = []): LengthAwarePaginator;

    public function save(array $data): CreatorProfile;

    public function update(CreatorProfile $creator, array $data): CreatorProfile;

    public function delete(CreatorProfile $creator): bool;

    public function verified(array $relations = []): Collection;

    public function active(array $relations = []): Collection;

    public function search(string $term, array $relations = []): LengthAwarePaginator;

    public function findWithVideoCounts(array $relations = []): Collection;

    public function query(): \Illuminate\Database\Eloquent\Builder;
}
