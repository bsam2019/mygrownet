<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoTag;
use Illuminate\Database\Eloquent\Collection;

interface VideoTagRepositoryInterface
{
    public function findById(int $id): ?VideoTag;

    public function findByVideo(int $videoId): Collection;

    /** @return Collection<int, VideoTag> */
    public function findByTag(string $tag): Collection;

    public function addTag(int $videoId, string $tag): VideoTag;

    public function removeTag(int $videoId, string $tag): bool;

    public function getPopularTags(int $limit = 20): array;

    public function findOrCreate(string $tag): VideoTag;

    public function deleteByVideo(int $videoId): void;

    public function query(): \Illuminate\Database\Eloquent\Builder;
}
