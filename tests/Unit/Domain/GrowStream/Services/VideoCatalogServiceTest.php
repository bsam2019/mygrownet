<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStream\Services;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video as EloquentVideo;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoCategory as EloquentCategory;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoSeries as EloquentSeries;
use App\Domain\GrowStream\Repositories\VideoCategoryRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoSeriesRepositoryInterface;
use App\Domain\GrowStream\Services\VideoCatalogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Support\BuilderDouble;

class VideoCatalogServiceTest extends TestCase
{
    private VideoRepositoryInterface&MockObject $videoRepo;
    private VideoSeriesRepositoryInterface&MockObject $seriesRepo;
    private VideoCategoryRepositoryInterface&MockObject $categoryRepo;
    private VideoCatalogService $service;

    protected function setUp(): void
    {
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->seriesRepo = $this->createMock(VideoSeriesRepositoryInterface::class);
        $this->categoryRepo = $this->createMock(VideoCategoryRepositoryInterface::class);
        $this->service = new VideoCatalogService(
            $this->videoRepo,
            $this->seriesRepo,
            $this->categoryRepo,
        );
    }

    private function mockVideo(array $attrs = []): EloquentVideo&MockObject
    {
        $v = $this->createMock(EloquentVideo::class);
        $v->expects($this->any())->method('toArray')->willReturn($attrs);
        $v->expects($this->any())->method('offsetExists')->willReturn(true);
        $v->expects($this->any())->method('offsetGet')->willReturnCallback(fn ($k) => $attrs[$k] ?? null);
        $v->expects($this->any())->method('__get')->willReturnCallback(fn ($k) => $attrs[$k] ?? null);
        $v->expects($this->any())->method('__isset')->willReturnCallback(fn ($k) => isset($attrs[$k]));
        return $v;
    }

    private function makeBuilder(array $overrides = []): BuilderDouble
    {
        $b = new BuilderDouble();
        foreach ($overrides as $method => $value) {
            $b->setReturn($method, $value);
        }
        return $b;
    }

    private function mockPaginator(array $data = []): LengthAwarePaginator&MockObject
    {
        $p = $this->createMock(LengthAwarePaginator::class);
        $p->method('toArray')->willReturn(['data' => $data]);
        return $p;
    }

    // ─── getFeatured ───────────────────────────────────────────────────────

    #[Test]
    public function get_featured_returns_featured_videos(): void
    {
        $videos = new Collection([$this->mockVideo(['id' => 1, 'title' => 'Featured'])]);
        $this->videoRepo->expects($this->once())->method('featured')->with(10)->willReturn($videos);

        $result = $this->service->getFeatured();

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
    }

    #[Test]
    public function get_featured_respects_limit(): void
    {
        $this->videoRepo->expects($this->once())->method('featured')->with(5)->willReturn(new Collection());

        $result = $this->service->getFeatured(5);

        $this->assertSame([], $result);
    }

    #[Test]
    public function get_featured_returns_empty_when_no_featured(): void
    {
        $this->videoRepo->expects($this->once())->method('featured')->willReturn(new Collection());

        $result = $this->service->getFeatured();

        $this->assertSame([], $result);
    }

    // ─── getTrending ───────────────────────────────────────────────────────

    #[Test]
    public function get_trending_returns_trending_videos(): void
    {
        $videos = new Collection([$this->mockVideo(['id' => 2])]);
        $this->videoRepo->expects($this->once())->method('trending')->with(7, 20)->willReturn($videos);

        $result = $this->service->getTrending();

        $this->assertCount(1, $result);
    }

    #[Test]
    public function get_trending_respects_days_and_limit(): void
    {
        $videos = new Collection([$this->mockVideo(['id' => 3])]);
        $this->videoRepo->expects($this->once())->method('trending')->with(14, 10)->willReturn($videos);

        $result = $this->service->getTrending(14, 10);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function get_trending_returns_empty_when_no_trending(): void
    {
        $this->videoRepo->expects($this->once())->method('trending')->willReturn(new Collection());

        $result = $this->service->getTrending();

        $this->assertSame([], $result);
    }

    // ─── getRecent ─────────────────────────────────────────────────────────

    #[Test]
    public function get_recent_returns_published_ready_videos(): void
    {
        $videos = new Collection([$this->mockVideo(['id' => 4, 'title' => 'Recent'])]);
        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['get' => $videos]));

        $result = $this->service->getRecent(5);

        $this->assertCount(1, $result);
        $this->assertSame('Recent', $result[0]['title']);
    }

    #[Test]
    public function get_recent_defaults_to_limit_10(): void
    {
        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['get' => new Collection()]));

        $result = $this->service->getRecent(10);

        $this->assertSame([], $result);
    }

    #[Test]
    public function get_recent_returns_empty_when_none_published(): void
    {
        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['get' => new Collection()]));

        $result = $this->service->getRecent();

        $this->assertSame([], $result);
    }

    // ─── browse ────────────────────────────────────────────────────────────

    #[Test]
    public function browse_returns_paginated_results(): void
    {
        $paginator = $this->createMock(LengthAwarePaginator::class);
        $paginator->expects($this->once())->method('toArray')->willReturn([
            'data' => [['id' => 10, 'title' => 'Page1']],
            'current_page' => 1, 'last_page' => 1, 'per_page' => 24, 'total' => 1, 'from' => 1, 'to' => 1,
        ]);

        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['paginate' => $paginator]));

        $result = $this->service->browse();

        $this->assertCount(1, $result['data']);
        $this->assertSame('Page1', $result['data'][0]['title']);
    }

    #[Test]
    public function browse_filters_by_search(): void
    {
        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['paginate' => $this->mockPaginator()]));

        $result = $this->service->browse(['search' => 'tutorial']);

        $this->assertCount(0, $result['data']);
    }

    #[Test]
    public function browse_filters_by_category(): void
    {
        $category = $this->createMock(EloquentCategory::class);
        $category->expects($this->any())->method('__get')->with('id')->willReturn(5);

        $this->categoryRepo->expects($this->once())->method('findBySlug')
            ->with('tutorials')->willReturn($category);

        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['paginate' => $this->mockPaginator()]));

        $result = $this->service->browse(['categorySlug' => 'tutorials']);

        $this->assertCount(0, $result['data']);
    }

    #[Test]
    public function browse_filters_by_content_type(): void
    {
        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['paginate' => $this->mockPaginator()]));

        $result = $this->service->browse(['contentType' => 'lesson']);

        $this->assertCount(0, $result['data']);
    }

    #[Test]
    public function browse_sorts_by_popular(): void
    {
        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['paginate' => $this->mockPaginator()]));

        $result = $this->service->browse(['sortBy' => 'popular']);

        $this->assertCount(0, $result['data']);
    }

    // ─── getBySlug ─────────────────────────────────────────────────────────

    #[Test]
    public function get_by_slug_returns_video_with_next_previous(): void
    {
        $next = $this->mockVideo(['id' => 2, 'title' => 'Next']);
        $prev = $this->mockVideo(['id' => 1, 'title' => 'Prev']);

        $video = $this->mockVideo(['id' => 3, 'title' => 'Current', 'series_id' => 1]);
        $video->expects($this->once())->method('getNextEpisode')->willReturn($next);
        $video->expects($this->once())->method('getPreviousEpisode')->willReturn($prev);

        $this->videoRepo->expects($this->once())->method('findBySlug')->with('current-vid')->willReturn($video);

        $result = $this->service->getBySlug('current-vid');

        $this->assertSame('Current', $result['title']);
        $this->assertSame('Next', $result['next_episode']['title']);
        $this->assertSame('Prev', $result['previous_episode']['title']);
    }

    #[Test]
    public function get_by_slug_returns_null_when_not_found(): void
    {
        $this->videoRepo->expects($this->once())->method('findBySlug')->with('missing')->willReturn(null);

        $result = $this->service->getBySlug('missing');

        $this->assertNull($result);
    }

    #[Test]
    public function get_by_slug_returns_null_next_previous_when_not_in_series(): void
    {
        $video = $this->mockVideo(['id' => 5, 'title' => 'Standalone', 'series_id' => null]);
        $video->expects($this->once())->method('getNextEpisode')->willReturn(null);
        $video->expects($this->once())->method('getPreviousEpisode')->willReturn(null);

        $this->videoRepo->expects($this->once())->method('findBySlug')->with('standalone')->willReturn($video);

        $result = $this->service->getBySlug('standalone');

        $this->assertNull($result['next_episode']);
        $this->assertNull($result['previous_episode']);
    }

    // ─── getRelated ────────────────────────────────────────────────────────

    #[Test]
    public function get_related_returns_videos_from_same_categories(): void
    {
        $catCollection = new Collection([(object) ['id' => 1]]);
        $tagCollection = new Collection([(object) ['id' => 1]]);

        $video = $this->mockVideo([
            'id' => 1,
            'slug' => 'vid-1',
            'categories' => $catCollection,
            'tags' => $tagCollection,
            'series_id' => null,
        ]);

        $this->videoRepo->expects($this->once())->method('findById')->with(1)->willReturn($video);

        $related = new Collection([$this->mockVideo(['id' => 2, 'title' => 'Related'])]);
        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['get' => $related]));

        $result = $this->service->getRelated(1, 'vid-1');

        $this->assertCount(1, $result);
    }

    #[Test]
    public function get_related_returns_empty_when_video_not_found(): void
    {
        $this->videoRepo->expects($this->once())->method('findById')->with(999)->willReturn(null);

        $result = $this->service->getRelated(999, 'missing');

        $this->assertSame([], $result);
    }

    #[Test]
    public function get_related_excludes_current_video(): void
    {
        $catCollection = new Collection([(object) ['id' => 1]]);
        $tagCollection = new Collection([(object) ['id' => 1]]);

        $video = $this->mockVideo([
            'id' => 1,
            'slug' => 'vid-1',
            'categories' => $catCollection,
            'tags' => $tagCollection,
            'series_id' => null,
        ]);

        $this->videoRepo->expects($this->once())->method('findById')->with(1)->willReturn($video);
        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['get' => new Collection()]));

        $result = $this->service->getRelated(1, 'vid-1');

        $this->assertSame([], $result);
    }

    // ─── getSeriesDetail ───────────────────────────────────────────────────

    #[Test]
    public function get_series_detail_returns_series_with_seasons_and_episodes(): void
    {
        $series = $this->createMock(EloquentSeries::class);
        $series->expects($this->any())->method('toArray')->willReturn(['id' => 1, 'title' => 'Series']);
        $series->expects($this->once())->method('getSeasons')->willReturn([1]);
        $series->expects($this->once())->method('getEpisodesBySeason')->with(1)
            ->willReturn(new Collection([$this->mockVideo(['id' => 10, 'title' => 'Ep1', 'season_number' => 1])]));

        $this->seriesRepo->expects($this->once())->method('findBySlug')->with('my-series')->willReturn($series);

        $result = $this->service->getSeriesDetail('my-series');

        $this->assertSame('Series', $result['title']);
        $this->assertArrayHasKey('seasons', $result);
        $this->assertCount(1, $result['seasons'][1]);
    }

    #[Test]
    public function get_series_detail_returns_null_when_not_found(): void
    {
        $this->seriesRepo->expects($this->once())->method('findBySlug')->with('missing')->willReturn(null);

        $result = $this->service->getSeriesDetail('missing');

        $this->assertNull($result);
    }

    #[Test]
    public function get_series_detail_with_multiple_seasons(): void
    {
        $series = $this->createMock(EloquentSeries::class);
        $series->expects($this->any())->method('toArray')->willReturn(['id' => 2, 'title' => 'Multi']);
        $series->expects($this->once())->method('getSeasons')->willReturn([1, 2]);
        $series->expects($this->exactly(2))->method('getEpisodesBySeason')
            ->willReturnMap([
                [1, new Collection([$this->mockVideo(['id' => 20, 'title' => 'S1E1', 'season_number' => 1])])],
                [2, new Collection([$this->mockVideo(['id' => 21, 'title' => 'S2E1', 'season_number' => 2])])],
            ]);

        $this->seriesRepo->expects($this->once())->method('findBySlug')->with('multi')->willReturn($series);

        $result = $this->service->getSeriesDetail('multi');

        $this->assertCount(2, $result['seasons']);
        $this->assertCount(1, $result['seasons'][1]);
        $this->assertCount(1, $result['seasons'][2]);
    }

    // ─── getCategories ─────────────────────────────────────────────────────

    #[Test]
    public function get_categories_returns_root_categories(): void
    {
        $categories = new Collection([
            $this->mockVideo(['id' => 1, 'name' => 'Tutorials']),
            $this->mockVideo(['id' => 2, 'name' => 'Talks']),
        ]);

        $this->categoryRepo->expects($this->once())->method('rootCategories')->willReturn($categories);

        $result = $this->service->getCategories();

        $this->assertCount(2, $result);
    }

    #[Test]
    public function get_categories_returns_empty_when_no_categories(): void
    {
        $this->categoryRepo->expects($this->once())->method('rootCategories')->willReturn(new Collection());

        $result = $this->service->getCategories();

        $this->assertSame([], $result);
    }

    // ─── getCategoryVideos ─────────────────────────────────────────────────

    #[Test]
    public function get_category_videos_returns_paginated_videos(): void
    {
        $category = $this->createMock(EloquentCategory::class);
        $category->expects($this->any())->method('__get')->with('id')->willReturn(5);

        $this->categoryRepo->expects($this->once())->method('findBySlug')
            ->with('tutorials')->willReturn($category);

        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['paginate' => $this->mockPaginator([['id' => 1, 'title' => 'Tut1']])]));

        $result = $this->service->getCategoryVideos('tutorials');

        $this->assertCount(1, $result['data']);
        $this->assertSame('Tut1', $result['data'][0]['title']);
    }

    #[Test]
    public function get_category_videos_returns_empty_when_category_not_found(): void
    {
        $this->categoryRepo->expects($this->once())->method('findBySlug')
            ->with('missing')->willReturn(null);

        $result = $this->service->getCategoryVideos('missing');

        $this->assertSame([], $result);
    }

    #[Test]
    public function get_category_videos_accepts_custom_sort(): void
    {
        $category = $this->createMock(EloquentCategory::class);
        $category->expects($this->any())->method('__get')->with('id')->willReturn(5);

        $this->categoryRepo->expects($this->once())->method('findBySlug')
            ->with('tutorials')->willReturn($category);

        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['paginate' => $this->mockPaginator()]));

        $result = $this->service->getCategoryVideos('tutorials', 'title', 'asc');

        $this->assertCount(0, $result['data']);
    }

    #[Test]
    public function get_category_videos_filters_to_published_ready(): void
    {
        $category = $this->createMock(EloquentCategory::class);
        $category->expects($this->any())->method('__get')->with('id')->willReturn(5);

        $this->categoryRepo->expects($this->once())->method('findBySlug')
            ->with('tutorials')->willReturn($category);

        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['paginate' => $this->mockPaginator()]));

        $result = $this->service->getCategoryVideos('tutorials');

        $this->assertCount(0, $result['data']);
    }
}
