<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\SponsorshipGrant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface SponsorshipGrantRepositoryInterface
{
    public function findById(int $id): ?SponsorshipGrant;

    public function forCreator(int $creatorId, array $relations = []): Collection;

    public function paginate(array $filters = [], int $perPage = 20, array $relations = []): LengthAwarePaginator;

    public function create(array $data): SponsorshipGrant;

    public function update(SponsorshipGrant $grant, array $data): SponsorshipGrant;

    public function totalApproved(): float;

    public function totalDisbursed(): float;

    public function query(): Builder;
}
