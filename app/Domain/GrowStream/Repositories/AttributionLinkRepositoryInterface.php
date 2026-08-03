<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\AttributionLink;
use Illuminate\Database\Eloquent\Builder;

interface AttributionLinkRepositoryInterface
{
    public function findById(int $id): ?AttributionLink;

    public function findBySession(string $visitorSessionId): ?AttributionLink;

    public function save(array $data): AttributionLink;

    public function update(AttributionLink $link, array $data): AttributionLink;

    public function bindConversion(string $visitorSessionId, int $userId): void;

    public function accumulateWatchMinutes(string $visitorSessionId, int $minutes): void;

    public function forCreator(int $creatorId, array $relations = []): \Illuminate\Database\Eloquent\Collection;

    public function query(): Builder;
}
