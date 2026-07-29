<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Entities\VideoSeries as VideoSeriesEntity;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoSeriesRepositoryInterface;
use App\Domain\GrowStream\ValueObjects\AccessLevel;
use App\Domain\GrowStream\ValueObjects\CreatorProfileId;
use App\Domain\GrowStream\ValueObjects\SeriesType;
use App\Domain\GrowStream\ValueObjects\SkillLevel;
use App\Domain\GrowStream\ValueObjects\StarterKitTier;

class VideoSeriesService
{
    public function __construct(
        private VideoSeriesRepositoryInterface $seriesRepo,
        private VideoRepositoryInterface $videoRepo,
    ) {}

    public function createSeries(
        int $creatorProfileId,
        string $title,
        ?string $description,
        SeriesType $seriesType,
        AccessLevel $accessLevel,
        ?SkillLevel $skillLevel = null,
        ?StarterKitTier $starterKitTier = null,
    ): array {
        $entity = VideoSeriesEntity::create(
            creatorProfileId: CreatorProfileId::fromInt($creatorProfileId),
            title: $title,
            seriesType: $seriesType,
            accessLevel: $accessLevel,
            description: $description,
            skillLevel: $skillLevel,
            starterKitTier: $starterKitTier,
        );

        $data = [
            'creator_id' => $entity->creatorProfileId()->toInt(),
            'title' => $entity->title(),
            'description' => $entity->description(),
            'series_type' => $entity->seriesType()->value,
            'access_level' => $entity->accessLevel()->value,
            'skill_level' => $entity->skillLevel()?->value,
            'starter_kit_tier' => $entity->starterKitTier()?->value,
            'is_published' => false,
        ];

        $saved = $this->seriesRepo->save($data);
        return $saved->toArray();
    }

    public function updateSeries(int $seriesId, array $data): array
    {
        $series = $this->seriesRepo->findById($seriesId);
        if (!$series) {
            throw new \RuntimeException("Series not found: {$seriesId}");
        }

        $updateData = [];
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }
        if (array_key_exists('description', $data)) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['series_type'])) {
            $updateData['series_type'] = $data['series_type'];
        }
        if (isset($data['access_level'])) {
            $updateData['access_level'] = $data['access_level'];
        }
        if (array_key_exists('skill_level', $data)) {
            $updateData['skill_level'] = $data['skill_level'];
        }

        $updated = $this->seriesRepo->update($series, $updateData);
        return $updated->toArray();
    }

    public function getSeries(int $seriesId): ?array
    {
        $series = $this->seriesRepo->findById($seriesId);
        return $series?->toArray();
    }

    public function getSeriesBySlug(string $slug): ?array
    {
        $series = $this->seriesRepo->findBySlug($slug);
        return $series?->toArray();
    }

    public function publishSeries(int $seriesId): void
    {
        $series = $this->seriesRepo->findById($seriesId);
        if (!$series) {
            throw new \RuntimeException("Series not found: {$seriesId}");
        }

        $entity = VideoSeriesEntity::reconstitute([
            'id' => $series->id,
            'creator_profile_id' => $series->creator_id,
            'title' => $series->title,
            'description' => $series->description,
            'series_type' => $series->series_type,
            'thumbnail_url' => $series->poster_url,
            'cover_url' => $series->banner_url,
            'access_level' => $series->access_level,
            'skill_level' => $series->skill_level,
            'starter_kit_tier' => $series->starter_kit_tier,
            'episode_count' => $series->total_episodes,
            'total_duration_seconds' => 0,
            'is_published' => $series->is_published,
            'published_at' => $series->published_at?->format('Y-m-d H:i:s'),
        ]);

        $entity->publish();

        $this->seriesRepo->update($series, [
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublishSeries(int $seriesId): void
    {
        $series = $this->seriesRepo->findById($seriesId);
        if (!$series) {
            throw new \RuntimeException("Series not found: {$seriesId}");
        }

        $entity = VideoSeriesEntity::reconstitute([
            'id' => $series->id,
            'creator_profile_id' => $series->creator_id,
            'title' => $series->title,
            'description' => $series->description,
            'series_type' => $series->series_type,
            'thumbnail_url' => $series->poster_url,
            'cover_url' => $series->banner_url,
            'access_level' => $series->access_level,
            'skill_level' => $series->skill_level,
            'starter_kit_tier' => $series->starter_kit_tier,
            'episode_count' => $series->total_episodes,
            'total_duration_seconds' => 0,
            'is_published' => $series->is_published,
            'published_at' => $series->published_at?->format('Y-m-d H:i:s'),
        ]);

        $entity->unpublish();

        $this->seriesRepo->update($series, [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function deleteSeries(int $seriesId): void
    {
        $series = $this->seriesRepo->findById($seriesId);
        if (!$series) {
            throw new \RuntimeException("Series not found: {$seriesId}");
        }

        $videos = $this->videoRepo->findBySeries($seriesId);
        if ($videos->isNotEmpty()) {
            throw new \RuntimeException("Cannot delete series {$seriesId}: it has {$videos->count()} videos attached");
        }

        $this->seriesRepo->delete($series);
    }

    public function reorderEpisodes(int $seriesId, array $episodes): void
    {
        $series = $this->seriesRepo->findById($seriesId);
        if (!$series) {
            throw new \RuntimeException("Series not found: {$seriesId}");
        }

        foreach ($episodes as $episode) {
            if (!isset($episode['video_id'])) {
                throw new \InvalidArgumentException('Each episode must specify a video_id');
            }

            $updateData = [];
            if (isset($episode['season_number'])) {
                $updateData['season_number'] = (int) $episode['season_number'];
            }
            if (isset($episode['episode_number'])) {
                $updateData['episode_number'] = (int) $episode['episode_number'];
            }

            if (!empty($updateData)) {
                $video = $this->videoRepo->findById((int) $episode['video_id']);
                if ($video) {
                    $this->videoRepo->update($video, $updateData);
                }
            }
        }
    }

    public function getAllSeries(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        if (!empty($filters)) {
            $query = $this->seriesRepo->query();

            if (!empty($filters['search'])) {
                $term = $filters['search'];
                $query->where(function ($q) use ($term) {
                    $q->where('title', 'like', "%{$term}%")
                      ->orWhere('description', 'like', "%{$term}%");
                });
            }

            if (!empty($filters['series_type'])) {
                $query->where('series_type', $filters['series_type']);
            }

            if (!empty($filters['creator_id'])) {
                $query->where('creator_id', $filters['creator_id']);
            }

            if (isset($filters['is_published'])) {
                $query->where('is_published', $filters['is_published']);
            }

            $query->orderBy('created_at', 'desc');

            return $query->paginate($perPage, ['*'], 'page', $page)->toArray();
        }

        return $this->seriesRepo->paginate($perPage)->toArray();
    }
}
