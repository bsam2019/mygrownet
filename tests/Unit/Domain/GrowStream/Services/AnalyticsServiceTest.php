<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStream\Services;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile as EloquentCreator;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video as EloquentVideo;
use App\Domain\GrowStream\Repositories\CreatorProfileRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoViewRepositoryInterface;
use App\Domain\GrowStream\Repositories\WatchHistoryRepositoryInterface;
use App\Domain\GrowStream\Services\AnalyticsService;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Support\BuilderDouble;

class AnalyticsServiceTest extends TestCase
{
    private VideoRepositoryInterface&MockObject $videoRepo;
    private CreatorProfileRepositoryInterface&MockObject $creatorRepo;
    private VideoViewRepositoryInterface&MockObject $viewRepo;
    private WatchHistoryRepositoryInterface&MockObject $watchRepo;
    private AnalyticsService $service;

    protected function setUp(): void
    {
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->creatorRepo = $this->createMock(CreatorProfileRepositoryInterface::class);
        $this->viewRepo = $this->createMock(VideoViewRepositoryInterface::class);
        $this->watchRepo = $this->createMock(WatchHistoryRepositoryInterface::class);
        $this->service = new AnalyticsService(
            $this->videoRepo,
            $this->creatorRepo,
            $this->viewRepo,
            $this->watchRepo,
        );
    }

    private function mockVideo(array $attrs = []): EloquentVideo&MockObject
    {
        $v = $this->createMock(EloquentVideo::class);
        $v->expects($this->any())->method('toArray')->willReturn($attrs);
        $v->expects($this->any())->method('__get')->willReturnCallback(fn ($k) => $attrs[$k] ?? null);
        return $v;
    }

    private function mockCreator(array $attrs = []): EloquentCreator&MockObject
    {
        $c = $this->createMock(EloquentCreator::class);
        $c->expects($this->any())->method('toArray')->willReturn($attrs);
        $c->expects($this->any())->method('__get')->willReturnCallback(fn ($k) => $attrs[$k] ?? null);
        return $c;
    }

    private function makeBuilder(array $overrides = []): BuilderDouble
    {
        $b = new BuilderDouble();
        foreach ($overrides as $method => $value) {
            $b->setReturn($method, $value);
        }
        return $b;
    }

    // ─── getOverview ───────────────────────────────────────────────────────

    #[Test]
    public function get_overview_returns_all_metrics(): void
    {
        $this->videoRepo->expects($this->exactly(4))->method('query')
            ->willReturn($this->makeBuilder(['count' => 200, 'get' => new Collection()]));

        $this->viewRepo->expects($this->exactly(3))->method('query')
            ->willReturnOnConsecutiveCalls(
                $this->makeBuilder(['count' => 1500]),
                $this->makeBuilder(['count' => 300]),
                $this->makeBuilder(['get' => new Collection([
                    (object) ['date' => '2026-07-28', 'views' => 10],
                    (object) ['date' => '2026-07-29', 'views' => 15],
                ])]),
            );

        $this->watchRepo->expects($this->once())->method('totalWatchTime')->willReturn(500000);
        $this->watchRepo->expects($this->once())->method('completionCount')->willReturn(800);
        $this->watchRepo->expects($this->once())->method('averageWatchDuration')->willReturn(120.5);

        $this->watchRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['count' => 1000]));

        $result = $this->service->getOverview(30);

        $this->assertSame(200, $result['total_videos']);
        $this->assertSame(1500, $result['period_views']);
        $this->assertSame(300, $result['unique_viewers']);
        $this->assertSame(500000, $result['total_watch_time_seconds']);
        $this->assertSame(800, $result['completion_count']);
        $this->assertSame(80.0, $result['average_completion_rate']);
        $this->assertSame(120.5, $result['average_watch_duration_seconds']);
        $this->assertCount(2, $result['daily_views']);
    }

    #[Test]
    public function get_overview_returns_zero_completion_rate_when_no_history(): void
    {
        $this->videoRepo->expects($this->exactly(4))->method('query')
            ->willReturn($this->makeBuilder(['count' => 0, 'get' => new Collection()]));

        $this->viewRepo->expects($this->exactly(3))->method('query')
            ->willReturnOnConsecutiveCalls(
                $this->makeBuilder(['count' => 0]),
                $this->makeBuilder(['count' => 0]),
                $this->makeBuilder(['get' => new Collection()]),
            );

        $this->watchRepo->expects($this->once())->method('totalWatchTime')->willReturn(0);
        $this->watchRepo->expects($this->once())->method('completionCount')->willReturn(0);
        $this->watchRepo->expects($this->once())->method('averageWatchDuration')->willReturn(0.0);

        $this->watchRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['count' => 0]));

        $result = $this->service->getOverview();

        $this->assertSame(0.0, $result['average_completion_rate']);
    }

    // ─── getVideoAnalytics ─────────────────────────────────────────────────

    #[Test]
    public function get_video_analytics_returns_all_stats(): void
    {
        $video = $this->mockVideo(['id' => 5, 'title' => 'My Video', 'view_count' => 200]);

        $this->videoRepo->expects($this->once())->method('findById')->with(5)->willReturn($video);

        $this->viewRepo->expects($this->once())->method('getTotalViews')->with(5)->willReturn(200);
        $this->viewRepo->expects($this->once())->method('getUniqueViewers')->with(5)->willReturn(45);
        $this->viewRepo->expects($this->once())->method('getViewsAnalytics')->with(5, 'daily')->willReturn([
            ['date' => '2026-07-28', 'views' => 10],
        ]);

        $this->watchRepo->expects($this->once())->method('completionCount')->with(5)->willReturn(60);
        $this->watchRepo->expects($this->once())->method('averageWatchDuration')->with(5)->willReturn(180.0);

        $this->watchRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['count' => 100]));

        $this->viewRepo->expects($this->exactly(2))->method('query')
            ->willReturnOnConsecutiveCalls(
                $this->makeBuilder(['get' => new Collection([(object) ['device_type' => 'mobile', 'count' => 60]])]),
                $this->makeBuilder(['get' => new Collection([(object) ['bucket' => '1-5min', 'count' => 30]])]),
            );

        $this->viewRepo->expects($this->once())->method('getViewsByVideo')
            ->with(5, $this->isInstanceOf(\DateTimeInterface::class), $this->isInstanceOf(\DateTimeInterface::class))
            ->willReturn(new Collection([(object) ['viewed_at' => '2026-07-28', 'video_id' => 5]]));

        $result = $this->service->getVideoAnalytics(5);

        $this->assertSame(5, $result['video_id']);
        $this->assertSame('My Video', $result['title']);
        $this->assertSame(200, $result['total_views']);
        $this->assertSame(45, $result['unique_viewers']);
        $this->assertSame(1, $result['period_views']);
        $this->assertSame(60.0, $result['completion_rate']);
        $this->assertSame(180.0, $result['average_watch_duration_seconds']);
        $this->assertCount(1, $result['views_analytics']);
        $this->assertCount(1, $result['device_breakdown']);
        $this->assertCount(1, $result['duration_buckets']);
    }

    #[Test]
    public function get_video_analytics_returns_empty_when_video_not_found(): void
    {
        $this->videoRepo->expects($this->once())->method('findById')->with(999)->willReturn(null);

        $result = $this->service->getVideoAnalytics(999);

        $this->assertSame([], $result);
    }

    #[Test]
    public function get_video_analytics_handles_zero_views(): void
    {
        $video = $this->mockVideo(['id' => 1, 'view_count' => 0]);

        $this->videoRepo->expects($this->once())->method('findById')->with(1)->willReturn($video);

        $this->viewRepo->expects($this->once())->method('getTotalViews')->with(1)->willReturn(0);
        $this->viewRepo->expects($this->once())->method('getUniqueViewers')->with(1)->willReturn(0);
        $this->viewRepo->expects($this->once())->method('getViewsAnalytics')->with(1, 'daily')->willReturn([]);

        $this->watchRepo->expects($this->once())->method('completionCount')->with(1)->willReturn(0);
        $this->watchRepo->expects($this->once())->method('averageWatchDuration')->with(1)->willReturn(0.0);

        $this->watchRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['count' => 0]));

        $this->viewRepo->expects($this->exactly(2))->method('query')
            ->willReturn($this->makeBuilder(['get' => new Collection()]));

        $this->viewRepo->expects($this->once())->method('getViewsByVideo')->willReturn(new Collection());

        $result = $this->service->getVideoAnalytics(1);

        $this->assertSame(0.0, $result['completion_rate']);
    }

    // ─── getCreatorAnalytics ───────────────────────────────────────────────

    #[Test]
    public function get_creator_analytics_returns_creator_stats(): void
    {
        $creator = $this->mockCreator(['id' => 3, 'display_name' => 'Alice']);

        $this->creatorRepo->expects($this->once())->method('findById')->with(3)->willReturn($creator);

        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['count' => 15, 'sum' => 5000, 'pluck' => new Collection([1, 2])]));

        $this->watchRepo->expects($this->exactly(3))->method('query')
            ->willReturnOnConsecutiveCalls(
                $this->makeBuilder(['sum' => 3600]),
                $this->makeBuilder(['count' => 20]),
                $this->makeBuilder(['count' => 40]),
            );

        $result = $this->service->getCreatorAnalytics(3);

        $this->assertSame(3, $result['creator_id']);
        $this->assertSame('Alice', $result['display_name']);
        $this->assertSame(15, $result['total_videos']);
        $this->assertSame(15, $result['published_videos']);
        $this->assertSame(15, $result['new_videos']);
        $this->assertSame(5000, $result['total_views']);
        $this->assertSame(3600, $result['period_watch_seconds']);
        $this->assertSame(50.0, $result['average_completion_rate']);
    }

    #[Test]
    public function get_creator_analytics_returns_empty_when_creator_not_found(): void
    {
        $this->creatorRepo->expects($this->once())->method('findById')->with(999)->willReturn(null);

        $result = $this->service->getCreatorAnalytics(999);

        $this->assertSame([], $result);
    }

    #[Test]
    public function get_creator_analytics_handles_no_videos(): void
    {
        $creator = $this->mockCreator(['id' => 2]);

        $this->creatorRepo->expects($this->once())->method('findById')->with(2)->willReturn($creator);

        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['count' => 0, 'sum' => 0, 'pluck' => new Collection()]));

        $result = $this->service->getCreatorAnalytics(2);

        $this->assertSame(0, $result['total_videos']);
        $this->assertSame(0, $result['period_watch_seconds']);
        $this->assertSame(0.0, $result['average_completion_rate']);
    }

    // ─── getEngagement ─────────────────────────────────────────────────────

    #[Test]
    public function get_engagement_returns_engagement_metrics(): void
    {
        $this->viewRepo->expects($this->exactly(4))->method('query')
            ->willReturnOnConsecutiveCalls(
                $this->makeBuilder(['count' => 50]),
                $this->makeBuilder(['avg' => 240.0]),
                $this->makeBuilder(['get' => new Collection([(object) ['user_id' => 1], (object) ['user_id' => 1]])]),
                $this->makeBuilder(['get' => new Collection([(object) ['hour' => 19, 'views' => 200]])]),
            );

        $this->watchRepo->expects($this->exactly(2))->method('query')
            ->willReturnOnConsecutiveCalls(
                $this->makeBuilder(['count' => 120]),
                $this->makeBuilder(['count' => 200]),
            );

        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['get' => new Collection([(object) ['content_type' => 'lesson', 'count' => 10, 'total_views' => 500]])]));

        $result = $this->service->getEngagement();

        $this->assertSame(50, $result['active_users']);
        $this->assertSame(240.0, $result['average_session_duration_seconds']);
        $this->assertSame(60.0, $result['average_completion_rate']);
        $this->assertSame(120, $result['total_completions']);
        $this->assertSame(2, $result['returning_viewers']);
        $this->assertCount(1, $result['peak_hours']);
        $this->assertCount(1, $result['content_type_stats']);
    }

    #[Test]
    public function get_engagement_returns_zero_rate_when_no_entries(): void
    {
        $this->viewRepo->expects($this->exactly(4))->method('query')
            ->willReturnOnConsecutiveCalls(
                $this->makeBuilder(['count' => 0]),
                $this->makeBuilder(['avg' => 0.0]),
                $this->makeBuilder(['get' => new Collection()]),
                $this->makeBuilder(['get' => new Collection()]),
            );

        $this->watchRepo->expects($this->exactly(2))->method('query')
            ->willReturn($this->makeBuilder(['count' => 0]));

        $this->videoRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['get' => new Collection()]));

        $result = $this->service->getEngagement();

        $this->assertSame(0.0, $result['average_session_duration_seconds']);
        $this->assertSame(0.0, $result['average_completion_rate']);
    }

    // ─── getCompletionRate ─────────────────────────────────────────────────

    #[Test]
    public function get_completion_rate_returns_percentage(): void
    {
        $this->watchRepo->expects($this->once())->method('completionCount')->with(5)->willReturn(30);
        $this->watchRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['count' => 60]));

        $result = $this->service->getCompletionRate(5);

        $this->assertSame(50.0, $result);
    }

    #[Test]
    public function get_completion_rate_returns_zero_when_no_watch_history(): void
    {
        $this->watchRepo->expects($this->once())->method('completionCount')->with(5)->willReturn(0);
        $this->watchRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['count' => 0]));

        $result = $this->service->getCompletionRate(5);

        $this->assertSame(0.0, $result);
    }

    #[Test]
    public function get_completion_rate_with_video_id_zero_filters_by_zero(): void
    {
        $this->watchRepo->expects($this->once())->method('completionCount')->with(0)->willReturn(0);
        $this->watchRepo->expects($this->once())->method('query')
            ->willReturn($this->makeBuilder(['count' => 0]));

        $result = $this->service->getCompletionRate(0);

        $this->assertSame(0.0, $result);
    }

    // ─── getAverageWatchTime ─────────────────────────────────────────────────

    #[Test]
    public function get_average_watch_time_returns_float(): void
    {
        $this->watchRepo->expects($this->once())->method('averageWatchDuration')->with(null)->willReturn(180.0);

        $result = $this->service->getAverageWatchTime();

        $this->assertSame(180.0, $result);
    }

    #[Test]
    public function get_average_watch_time_for_specific_video(): void
    {
        $this->watchRepo->expects($this->once())->method('averageWatchDuration')->with(5)->willReturn(240.5);

        $result = $this->service->getAverageWatchTime(5);

        $this->assertSame(240.5, $result);
    }

    #[Test]
    public function get_average_watch_time_returns_zero_when_no_data(): void
    {
        $this->watchRepo->expects($this->once())->method('averageWatchDuration')->with(null)->willReturn(0.0);

        $result = $this->service->getAverageWatchTime();

        $this->assertSame(0.0, $result);
    }
}
