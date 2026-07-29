<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Repositories\VideoCategoryRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoSeriesRepositoryInterface;

class VideoCatalogService
{
    public function __construct(
        private VideoRepositoryInterface $videoRepo,
        private VideoSeriesRepositoryInterface $seriesRepo,
        private VideoCategoryRepositoryInterface $categoryRepo,
    ) {}

    public function getFeatured(int $limit = 10): array
    {
        return $this->videoRepo->featured($limit)->toArray();
    }

    public function getTrending(int $days = 7, int $limit = 20): array
    {
        return $this->videoRepo->trending($days, $limit)->toArray();
    }

    public function getRecent(int $limit = 10): array
    {
        return $this->videoRepo->query()
            ->where('is_published', true)
            ->where('upload_status', 'ready')
            ->latest('published_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function browse(array $filters = [], int $page = 1, int $perPage = 24): array
    {
        $query = $this->videoRepo->query()
            ->where('is_published', true)
            ->where('upload_status', 'ready');

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if (!empty($filters['categorySlug'])) {
            $category = $this->categoryRepo->findBySlug($filters['categorySlug']);
            if ($category) {
                $query->whereHas('categories', fn ($q) => $q->where('growstream_video_categories.id', $category->id));
            }
        }

        if (!empty($filters['contentType'])) {
            $query->where('content_type', $filters['contentType']);
        }

        if (!empty($filters['accessLevel'])) {
            $query->where('access_level', $filters['accessLevel']);
        }

        $sortField = match ($filters['sortBy'] ?? 'latest') {
            'popular' => 'view_count',
            'trending' => 'view_count',
            default => 'published_at',
        };
        $query->orderBy($sortField, 'desc');

        return $query->paginate($perPage, ['*'], 'page', $page)->toArray();
    }

    public function getBySlug(string $slug): ?array
    {
        $video = $this->videoRepo->findBySlug($slug);
        if (!$video) {
            return null;
        }

        $result = $video->toArray();
        $result['next_episode'] = $video->getNextEpisode()?->toArray();
        $result['previous_episode'] = $video->getPreviousEpisode()?->toArray();

        return $result;
    }

    public function getRelated(int $videoId, string $slug, int $limit = 12): array
    {
        $video = $this->videoRepo->findById($videoId);
        if (!$video) {
            return [];
        }

        $categoryIds = $video->categories->pluck('id')->toArray();
        $tagIds = $video->tags->pluck('id')->toArray();

        $query = $this->videoRepo->query()
            ->where('id', '!=', $videoId)
            ->where('is_published', true)
            ->where('upload_status', 'ready');

        if (!empty($categoryIds)) {
            $query->whereHas('categories', fn ($q) => $q->whereIn('growstream_video_categories.id', $categoryIds));
        }
        if (!empty($tagIds)) {
            $query->orWhereHas('tags', fn ($q) => $q->whereIn('growstream_video_tags.id', $tagIds));
        }

        return $query->limit($limit)->get()->toArray();
    }

    public function getSeriesDetail(string $slug): ?array
    {
        $series = $this->seriesRepo->findBySlug($slug);
        if (!$series) {
            return null;
        }

        $seasons = $series->getSeasons();
        $episodesBySeason = [];
        foreach ($seasons as $season) {
            $episodesBySeason[(int) $season] = $series->getEpisodesBySeason((int) $season)->toArray();
        }

        return array_merge($series->toArray(), ['seasons' => $episodesBySeason]);
    }

    public function getCategories(): array
    {
        return $this->categoryRepo->rootCategories()->toArray();
    }

    public function getCategoryVideos(string $categorySlug, string $sortBy = 'published_at', string $sortOrder = 'desc', int $perPage = 20): array
    {
        $category = $this->categoryRepo->findBySlug($categorySlug);
        if (!$category) {
            return [];
        }

        return $this->videoRepo->query()
            ->where('is_published', true)
            ->where('upload_status', 'ready')
            ->whereHas('categories', fn ($q) => $q->where('growstream_video_categories.id', $category->id))
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->toArray();
    }
}
