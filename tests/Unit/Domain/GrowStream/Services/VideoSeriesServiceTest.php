<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStream\Services;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoSeries;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoSeriesRepositoryInterface;
use App\Domain\GrowStream\Services\VideoSeriesService;
use App\Domain\GrowStream\ValueObjects\AccessLevel;
use App\Domain\GrowStream\ValueObjects\SeriesType;
use App\Domain\GrowStream\ValueObjects\SkillLevel;
use App\Domain\GrowStream\ValueObjects\StarterKitTier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VideoSeriesServiceTest extends TestCase
{
    private VideoSeriesRepositoryInterface&MockObject $seriesRepo;
    private VideoRepositoryInterface&MockObject $videoRepo;
    private VideoSeriesService $service;

    protected function setUp(): void
    {
        $this->seriesRepo = $this->createMock(VideoSeriesRepositoryInterface::class);
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->service = new VideoSeriesService($this->seriesRepo, $this->videoRepo);
    }

    private function mockSeries(array $overrides = []): VideoSeries&MockObject
    {
        $attrs = array_merge([
            'id' => 1,
            'creator_id' => 5,
            'title' => 'Test Series',
            'description' => 'A test series description',
            'series_type' => 'course',
            'poster_url' => 'https://example.com/poster.jpg',
            'banner_url' => 'https://example.com/banner.jpg',
            'access_level' => 'free',
            'skill_level' => 'beginner',
            'starter_kit_tier' => null,
            'total_episodes' => 0,
            'is_published' => false,
            'published_at' => null,
            'created_at' => '2026-01-01T00:00:00Z',
            'updated_at' => '2026-01-01T00:00:00Z',
        ], $overrides);

        $series = $this->getMockBuilder(VideoSeries::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get', 'toArray', 'save', 'delete'])
            ->getMock();

        $series->method('__get')->willReturnCallback(function ($key) use ($attrs) {
            $value = $attrs[$key] ?? null;
            if ($key === 'published_at' && is_string($value)) {
                return new \Illuminate\Support\Carbon($value);
            }
            return $value;
        });

        $series->method('toArray')->willReturn($attrs);

        return $series;
    }

    private function mockVideo(array $overrides = []): Video&MockObject
    {
        $attrs = array_merge([
            'id' => 100,
            'series_id' => 1,
            'season_number' => null,
            'episode_number' => null,
            'title' => 'Test Video',
        ], $overrides);

        $video = $this->getMockBuilder(Video::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get', 'toArray', 'save'])
            ->getMock();

        $video->method('__get')->willReturnCallback(function ($key) use ($attrs) {
            return $attrs[$key] ?? null;
        });

        $video->method('toArray')->willReturn($attrs);

        return $video;
    }

    #[Test]
    public function createSeries_createsSuccessfully(): void
    {
        $savedMock = $this->mockSeries(['id' => 1, 'creator_id' => 5]);

        $this->seriesRepo->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($data) {
                $this->assertSame(5, $data['creator_id']);
                $this->assertSame('New Series', $data['title']);
                $this->assertSame('A description', $data['description']);
                $this->assertSame('course', $data['series_type']);
                $this->assertSame('free', $data['access_level']);
                return true;
            }))
            ->willReturn($savedMock);

        $result = $this->service->createSeries(5, 'New Series', 'A description', SeriesType::Course, AccessLevel::Free);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('Test Series', $result['title']);
    }

    #[Test]
    public function createSeries_withSkillLevelAndStarterKitTier(): void
    {
        $savedMock = $this->mockSeries([
            'id' => 2,
            'creator_id' => 5,
            'title' => 'Advanced Series',
            'series_type' => 'workshop_series',
            'access_level' => 'premium',
            'skill_level' => 'expert',
            'starter_kit_tier' => 'elite',
        ]);

        $this->seriesRepo->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($data) {
                $this->assertSame('expert', $data['skill_level']);
                $this->assertSame('elite', $data['starter_kit_tier']);
                $this->assertSame('premium', $data['access_level']);
                $this->assertSame('workshop_series', $data['series_type']);
                return true;
            }))
            ->willReturn($savedMock);

        $result = $this->service->createSeries(
            5,
            'Advanced Series',
            'Expert level content',
            SeriesType::WorkshopSeries,
            AccessLevel::Premium,
            SkillLevel::Expert,
            StarterKitTier::Elite,
        );

        $this->assertIsArray($result);
        $this->assertSame('expert', $result['skill_level']);
    }

    #[Test]
    public function updateSeries_updatesFields(): void
    {
        $series = $this->mockSeries();
        $this->seriesRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($series);

        $updatedMock = $this->mockSeries(['title' => 'Updated Title']);

        $this->seriesRepo->expects($this->once())
            ->method('update')
            ->with($series, $this->callback(function ($data) {
                $this->assertSame('Updated Title', $data['title']);
                $this->assertSame('premium', $data['access_level']);
                return true;
            }))
            ->willReturn($updatedMock);

        $result = $this->service->updateSeries(1, [
            'title' => 'Updated Title',
            'access_level' => 'premium',
        ]);

        $this->assertIsArray($result);
        $this->assertSame('Updated Title', $result['title']);
    }

    #[Test]
    public function updateSeries_throwsWhenNotFound(): void
    {
        $this->seriesRepo->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Series not found: 999');

        $this->service->updateSeries(999, ['title' => 'Nope']);
    }

    #[Test]
    public function getSeries_returnsSeries(): void
    {
        $series = $this->mockSeries();
        $this->seriesRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($series);

        $result = $this->service->getSeries(1);
        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('Test Series', $result['title']);
    }

    #[Test]
    public function getSeries_returnsNullWhenNotFound(): void
    {
        $this->seriesRepo->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $result = $this->service->getSeries(999);
        $this->assertNull($result);
    }

    #[Test]
    public function getSeriesBySlug_returnsSeries(): void
    {
        $series = $this->mockSeries(['slug' => 'test-series']);
        $this->seriesRepo->expects($this->once())
            ->method('findBySlug')
            ->with('test-series')
            ->willReturn($series);

        $result = $this->service->getSeriesBySlug('test-series');
        $this->assertIsArray($result);
        $this->assertSame('Test Series', $result['title']);
    }

    #[Test]
    public function getSeriesBySlug_returnsNullWhenNotFound(): void
    {
        $this->seriesRepo->expects($this->once())
            ->method('findBySlug')
            ->with('non-existent')
            ->willReturn(null);

        $result = $this->service->getSeriesBySlug('non-existent');
        $this->assertNull($result);
    }

    #[Test]
    public function publishSeries_publishesSuccessfully(): void
    {
        $series = $this->mockSeries(['is_published' => false, 'published_at' => null]);
        $this->seriesRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($series);

        $this->seriesRepo->expects($this->once())
            ->method('update')
            ->with($series, $this->callback(function ($data) {
                $this->assertTrue($data['is_published']);
                $this->assertNotNull($data['published_at']);
                return true;
            }));

        $this->service->publishSeries(1);
    }

    #[Test]
    public function unpublishSeries_unpublishesSuccessfully(): void
    {
        $series = $this->mockSeries(['is_published' => true, 'published_at' => '2026-06-01T00:00:00Z']);
        $this->seriesRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($series);

        $this->seriesRepo->expects($this->once())
            ->method('update')
            ->with($series, $this->callback(function ($data) {
                $this->assertFalse($data['is_published']);
                $this->assertNull($data['published_at']);
                return true;
            }));

        $this->service->unpublishSeries(1);
    }

    #[Test]
    public function deleteSeries_deletesSuccessfully(): void
    {
        $series = $this->mockSeries();
        $this->seriesRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($series);

        $this->videoRepo->expects($this->once())
            ->method('findBySeries')
            ->with(1)
            ->willReturn(new Collection());

        $this->seriesRepo->expects($this->once())
            ->method('delete')
            ->with($series)
            ->willReturn(true);

        $this->service->deleteSeries(1);
    }

    #[Test]
    public function deleteSeries_throwsWhenHasVideos(): void
    {
        $series = $this->mockSeries();
        $this->seriesRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($series);

        $this->videoRepo->expects($this->once())
            ->method('findBySeries')
            ->with(1)
            ->willReturn(new Collection([
                $this->mockVideo(['id' => 10]),
                $this->mockVideo(['id' => 11]),
            ]));

        $this->seriesRepo->expects($this->never())->method('delete');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot delete series 1: it has 2 videos attached');

        $this->service->deleteSeries(1);
    }

    #[Test]
    public function reorderEpisodes_reordersSuccessfully(): void
    {
        $series = $this->mockSeries();
        $this->seriesRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($series);

        $video10 = $this->mockVideo(['id' => 10]);
        $video11 = $this->mockVideo(['id' => 11]);

        $this->videoRepo->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [10, $video10],
                [11, $video11],
            ]);

        $this->videoRepo->expects($this->exactly(2))
            ->method('update')
            ->with(
                $this->anything(),
                $this->callback(function ($data) {
                    return isset($data['season_number']) || isset($data['episode_number']);
                })
            );

        $this->service->reorderEpisodes(1, [
            ['video_id' => 10, 'season_number' => 1, 'episode_number' => 1],
            ['video_id' => 11, 'season_number' => 1, 'episode_number' => 2],
        ]);
    }

    #[Test]
    public function getAllSeries_returnsPaginatedWithoutFilters(): void
    {
        $paginatorMock = $this->createMock(LengthAwarePaginator::class);
        $paginatorMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['data' => [], 'total' => 0, 'current_page' => 1]);

        $this->seriesRepo->expects($this->once())
            ->method('paginate')
            ->with(20)
            ->willReturn($paginatorMock);

        $result = $this->service->getAllSeries([], 1, 20);
        $this->assertIsArray($result);
        $this->assertSame(0, $result['total']);
    }

    #[Test]
    public function getAllSeries_withFilters(): void
    {
        $paginatorMock = $this->createMock(LengthAwarePaginator::class);
        $paginatorMock->method('toArray')
            ->willReturn(['data' => [['id' => 1]], 'total' => 1, 'current_page' => 1]);

        $queryBuilder = new class($paginatorMock) extends \Illuminate\Database\Eloquent\Builder
        {
            private mixed $paginator;

            public function __construct($paginator)
            {
                $this->paginator = $paginator;
            }

            public function where($column, $operator = null, $value = null, $boolean = 'and'): static
            {
                return $this;
            }

            public function orderBy($column, $direction = 'asc'): static
            {
                return $this;
            }

            public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null, $total = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
            {
                return $this->paginator;
            }
        };

        $this->seriesRepo->expects($this->once())
            ->method('query')
            ->willReturn($queryBuilder);

        $result = $this->service->getAllSeries([
            'series_type' => 'course',
            'creator_id' => 5,
            'is_published' => true,
        ], 1, 20);

        $this->assertIsArray($result);
        $this->assertCount(1, $result['data']);
    }

    #[Test]
    public function reorderEpisodes_requiresVideoId(): void
    {
        $series = $this->mockSeries();
        $this->seriesRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($series);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Each episode must specify a video_id');

        $this->service->reorderEpisodes(1, [
            ['season_number' => 1, 'episode_number' => 1],
        ]);
    }

    #[Test]
    public function publishSeries_throwsWhenNotFound(): void
    {
        $this->seriesRepo->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Series not found: 999');

        $this->service->publishSeries(999);
    }

    #[Test]
    public function unpublishSeries_throwsWhenNotFound(): void
    {
        $this->seriesRepo->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Series not found: 999');

        $this->service->unpublishSeries(999);
    }

    #[Test]
    public function deleteSeries_throwsWhenNotFound(): void
    {
        $this->seriesRepo->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Series not found: 999');

        $this->service->deleteSeries(999);
    }
}
