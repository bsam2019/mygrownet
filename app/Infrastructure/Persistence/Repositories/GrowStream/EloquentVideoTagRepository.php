<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoTag;
use App\Domain\GrowStream\Repositories\VideoTagRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class EloquentVideoTagRepository implements VideoTagRepositoryInterface
{
    public function findById(int $id): ?VideoTag
    {
        return VideoTag::find($id);
    }

    public function findByVideo(int $videoId): Collection
    {
        return Video::find($videoId)?->tags()->get() ?? new Collection;
    }

    public function findByTag(string $tag): Collection
    {
        return VideoTag::where('name', $tag)->get();
    }

    public function addTag(int $videoId, string $tag): VideoTag
    {
        $video = Video::findOrFail($videoId);
        $tagModel = $this->findOrCreate($tag);

        $video->tags()->syncWithoutDetaching([$tagModel->id]);
        $tagModel->incrementUsage();

        return $tagModel;
    }

    public function removeTag(int $videoId, string $tag): bool
    {
        $video = Video::find($videoId);
        if ($video === null) {
            return false;
        }

        $tagModel = VideoTag::where('name', $tag)->first();
        if ($tagModel === null) {
            return false;
        }

        $detached = $video->tags()->detach($tagModel->id);
        if ($detached > 0) {
            $tagModel->decrementUsage();
        }

        return $detached > 0;
    }

    public function getPopularTags(int $limit = 20): array
    {
        return VideoTag::orderByDesc('usage_count')
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'usage_count'])
            ->toArray();
    }

    public function findOrCreate(string $tag): VideoTag
    {
        return VideoTag::firstOrCreate(
            ['name' => $tag],
            ['slug' => Str::slug($tag), 'usage_count' => 0]
        );
    }

    public function deleteByVideo(int $videoId): void
    {
        $video = Video::find($videoId);
        if ($video === null) {
            return;
        }

        $tags = $video->tags()->get();
        $video->tags()->detach();

        foreach ($tags as $tag) {
            $tag->decrementUsage();
        }
    }

    public function query(): Builder
    {
        return VideoTag::query();
    }
}
