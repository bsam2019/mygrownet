<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStream\Services;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video as EloquentVideo;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory as EloquentWatchHistory;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoViewRepositoryInterface;
use App\Domain\GrowStream\Repositories\WatchHistoryRepositoryInterface;
use App\Domain\GrowStream\Services\WatchService;
use App\Domain\GrowStream\ValueObjects\DeviceType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WatchServiceTest extends TestCase
{
    private VideoRepositoryInterface $videoRepo;
    private WatchHistoryRepositoryInterface $watchHistoryRepo;
    private VideoViewRepositoryInterface $viewRepo;
    private WatchService $service;

    protected function setUp(): void
    {
        $this->videoRepo = $this->createStub(VideoRepositoryInterface::class);
        $this->watchHistoryRepo = $this->createStub(WatchHistoryRepositoryInterface::class);
        $this->viewRepo = $this->createStub(VideoViewRepositoryInterface::class);
        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);
    }

    private function mockVideo(array $overrides = []): EloquentVideo
    {
        $data = array_merge([
            'id' => 1,
            'creator_profile_id' => 1,
            'title' => 'Test Video',
            'description' => 'A test video',
            'content_type' => 'movie',
            'duration_seconds' => 3600,
            'access_level' => 'free',
            'content_rating' => null,
            'skill_level' => null,
            'upload_status' => 'ready',
            'video_url' => 'https://example.com/video.mp4',
            'thumbnail_url' => null,
            'provider' => 'digitalocean',
            'playback_policy' => 'public',
            'series_type' => null,
            'series_id' => null,
            'season_number' => null,
            'episode_number' => null,
            'tags' => [],
            'metadata' => [],
            'is_published' => true,
            'published_at' => '2026-01-01 00:00:00',
            'views_count' => 100,
            'starter_kit_tier' => null,
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ], $overrides);

        $mock = $this->createMock(EloquentVideo::class);
        $mock->method('toArray')->willReturn($data);
        return $mock;
    }

    private function mockWatchHistoryEloquent(array $overrides = []): EloquentWatchHistory
    {
        $data = array_merge([
            'id' => 10,
            'user_id' => 42,
            'video_id' => 1,
            'watched_seconds' => 120,
            'completed' => false,
            'watched_at' => '2026-01-01 00:00:00',
            'device_type' => 'desktop',
            'created_at' => '2026-01-01 00:00:00',
        ], $overrides);

        $mock = $this->createMock(EloquentWatchHistory::class);
        $mock->method('toArray')->willReturn($data);
        return $mock;
    }

    private function mockPaginator(array $data): LengthAwarePaginator
    {
        $mock = $this->createMock(LengthAwarePaginator::class);
        $mock->method('toArray')->willReturn($data);
        return $mock;
    }

    // ---- authorizePlayback ----

    #[Test]
    public function authorize_playback_success(): void
    {
        $videoMock = $this->mockVideo();
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->videoRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($videoMock);
        $this->videoRepo->expects($this->once())
            ->method('save')
            ->with($this->arrayHasKey('views_count'));

        $this->viewRepo = $this->createMock(VideoViewRepositoryInterface::class);
        $this->viewRepo->expects($this->once())
            ->method('recordView')
            ->with(1, 42, null, null);

        $this->watchHistoryRepo = $this->createMock(WatchHistoryRepositoryInterface::class);
        $this->watchHistoryRepo->expects($this->once())
            ->method('updateOrCreate')
            ->with(
                ['user_id' => 42, 'video_id' => 1],
                $this->arrayHasKey('is_completed'),
            );

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $result = $this->service->authorizePlayback(1, 42);

        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('playback_url', $result);
        $this->assertArrayHasKey('video', $result);
        $this->assertArrayHasKey('watch_progress', $result);
        $this->assertSame('https://example.com/video.mp4', $result['playback_url']);
        $this->assertSame(0, $result['watch_progress']['watched_seconds']);
    }

    #[Test]
    public function authorize_playback_throws_when_video_not_found(): void
    {
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->videoRepo->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Video not found');
        $this->service->authorizePlayback(999, 42);
    }

    #[Test]
    public function authorize_playback_throws_when_not_published(): void
    {
        $videoMock = $this->mockVideo(['is_published' => false]);
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->videoRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($videoMock);

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Video is not available for playback');
        $this->service->authorizePlayback(1, 42);
    }

    #[Test]
    public function authorize_playback_throws_when_not_ready(): void
    {
        $videoMock = $this->mockVideo(['upload_status' => 'processing']);
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->videoRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($videoMock);

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Video is not available for playback');
        $this->service->authorizePlayback(1, 42);
    }

    #[Test]
    public function authorize_playback_throws_when_access_denied(): void
    {
        $videoMock = $this->mockVideo(['access_level' => 'premium']);
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->videoRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($videoMock);

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Access denied');
        $this->service->authorizePlayback(1, 42);
    }

    #[Test]
    public function authorize_playback_records_view_and_increments_count(): void
    {
        $videoMock = $this->mockVideo(['views_count' => 50]);
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->videoRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($videoMock);
        $this->videoRepo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $data) {
                return $data['views_count'] === 51;
            }));

        $this->viewRepo = $this->createMock(VideoViewRepositoryInterface::class);
        $this->viewRepo->expects($this->once())
            ->method('recordView')
            ->with(1, 42, '192.168.1.1', 'Mozilla/5.0');

        $this->watchHistoryRepo = $this->createMock(WatchHistoryRepositoryInterface::class);
        $this->watchHistoryRepo->expects($this->once())
            ->method('updateOrCreate');

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $this->service->authorizePlayback(1, 42, '192.168.1.1', 'Mozilla/5.0');
    }

    // ---- updateProgress ----

    #[Test]
    public function update_progress_resumes_existing_history(): void
    {
        $historyMock = $this->mockWatchHistoryEloquent(['watched_seconds' => 30]);
        $this->watchHistoryRepo = $this->createMock(WatchHistoryRepositoryInterface::class);
        $this->watchHistoryRepo->expects($this->once())
            ->method('findByUserAndVideo')
            ->with(42, 1)
            ->willReturn($historyMock);
        $this->watchHistoryRepo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $data) {
                return $data['watched_seconds'] >= 180;
            }));

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $result = $this->service->updateProgress(1, 42, 180, 3600);

        $this->assertArrayHasKey('progress_percentage', $result);
        $this->assertArrayHasKey('is_completed', $result);
        $this->assertFalse($result['is_completed']);
    }

    #[Test]
    public function update_progress_creates_new_history_when_none_exists(): void
    {
        $this->watchHistoryRepo = $this->createMock(WatchHistoryRepositoryInterface::class);
        $this->watchHistoryRepo->expects($this->once())
            ->method('findByUserAndVideo')
            ->with(42, 1)
            ->willReturn(null);
        $this->watchHistoryRepo->expects($this->once())
            ->method('save');

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $result = $this->service->updateProgress(1, 42, 60, 3600);

        $this->assertFalse($result['is_completed']);
    }

    #[Test]
    public function update_progress_marks_completed_at_threshold(): void
    {
        $historyMock = $this->mockWatchHistoryEloquent(['watched_seconds' => 0]);
        $this->watchHistoryRepo = $this->createMock(WatchHistoryRepositoryInterface::class);
        $this->watchHistoryRepo->expects($this->once())
            ->method('findByUserAndVideo')
            ->with(42, 1)
            ->willReturn($historyMock);
        $this->watchHistoryRepo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $data) {
                return $data['completed'] === true;
            }));

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $result = $this->service->updateProgress(1, 42, 95, 100);

        $this->assertTrue($result['is_completed']);
        $this->assertSame(95.0, $result['progress_percentage']);
    }

    #[Test]
    public function update_progress_does_not_mark_completed_below_threshold(): void
    {
        $historyMock = $this->mockWatchHistoryEloquent(['watched_seconds' => 0]);
        $this->watchHistoryRepo = $this->createMock(WatchHistoryRepositoryInterface::class);
        $this->watchHistoryRepo->expects($this->once())
            ->method('findByUserAndVideo')
            ->with(42, 1)
            ->willReturn($historyMock);
        $this->watchHistoryRepo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (array $data) {
                return $data['completed'] === false;
            }));

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $result = $this->service->updateProgress(1, 42, 50, 100);

        $this->assertFalse($result['is_completed']);
        $this->assertSame(50.0, $result['progress_percentage']);
    }

    // ---- getHistory, getContinueWatching, getInProgress ----

    #[Test]
    public function get_history_delegates_to_repository(): void
    {
        $expected = ['data' => [['id' => 1]]];
        $paginator = $this->mockPaginator($expected);

        $this->watchHistoryRepo = $this->createMock(WatchHistoryRepositoryInterface::class);
        $this->watchHistoryRepo->expects($this->once())
            ->method('paginateForUser')
            ->with(42, 15)
            ->willReturn($paginator);

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $result = $this->service->getHistory(42, 1, 15);
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function get_history_uses_default_per_page(): void
    {
        $expected = ['data' => []];
        $paginator = $this->mockPaginator($expected);

        $this->watchHistoryRepo = $this->createMock(WatchHistoryRepositoryInterface::class);
        $this->watchHistoryRepo->expects($this->once())
            ->method('paginateForUser')
            ->with(42, 20)
            ->willReturn($paginator);

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $result = $this->service->getHistory(42);
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function get_continue_watching_delegates(): void
    {
        $expected = [['id' => 1]];
        $collection = new Collection($expected);

        $this->watchHistoryRepo = $this->createMock(WatchHistoryRepositoryInterface::class);
        $this->watchHistoryRepo->expects($this->once())
            ->method('continueWatching')
            ->with(42, 5)
            ->willReturn($collection);

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $result = $this->service->getContinueWatching(42, 5);
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function get_in_progress_delegates(): void
    {
        $expected = [['id' => 1]];
        $collection = new Collection($expected);

        $this->watchHistoryRepo = $this->createMock(WatchHistoryRepositoryInterface::class);
        $this->watchHistoryRepo->expects($this->once())
            ->method('inProgress')
            ->with(42)
            ->willReturn($collection);

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $result = $this->service->getInProgress(42);
        $this->assertSame($expected, $result);
    }

    // ---- canWatch ----

    #[Test]
    public function can_watch_returns_true_for_authenticated_user_on_free_video(): void
    {
        $videoMock = $this->mockVideo(['access_level' => 'free']);
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->videoRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($videoMock);

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $this->assertTrue($this->service->canWatch(1, 42));
    }

    #[Test]
    public function can_watch_returns_false_for_authenticated_user_on_premium_video_without_subscription(): void
    {
        $videoMock = $this->mockVideo(['access_level' => 'premium']);
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->videoRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($videoMock);

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $this->assertFalse($this->service->canWatch(1, 42));
    }

    #[Test]
    public function can_watch_returns_true_for_free_video_without_user(): void
    {
        $videoMock = $this->mockVideo(['access_level' => 'free']);
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->videoRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($videoMock);

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $this->assertTrue($this->service->canWatch(1, null));
    }

    #[Test]
    public function can_watch_returns_false_for_premium_video_without_user(): void
    {
        $videoMock = $this->mockVideo(['access_level' => 'premium']);
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->videoRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($videoMock);

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $this->assertFalse($this->service->canWatch(1, null));
    }

    #[Test]
    public function can_watch_returns_false_when_video_not_found(): void
    {
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->videoRepo->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->service = new WatchService($this->videoRepo, $this->watchHistoryRepo, $this->viewRepo);

        $this->assertFalse($this->service->canWatch(999, null));
    }

    // ---- detectDeviceType ----

    #[Test]
    public function detect_device_type_returns_desktop_for_null_user_agent(): void
    {
        $this->assertSame(DeviceType::Desktop, WatchService::detectDeviceType(null));
    }

    #[Test]
    public function detect_device_type_returns_mobile_for_mobile_user_agent(): void
    {
        $this->assertSame(DeviceType::Mobile, WatchService::detectDeviceType('Mozilla/5.0 (Linux; Android 13) AppleWebKit/537.36'));
    }

    #[Test]
    public function detect_device_type_returns_mobile_for_iphone(): void
    {
        $this->assertSame(DeviceType::Mobile, WatchService::detectDeviceType('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0)'));
    }

    #[Test]
    public function detect_device_type_returns_tablet_for_ipad(): void
    {
        $this->assertSame(DeviceType::Tablet, WatchService::detectDeviceType('Mozilla/5.0 (iPad; CPU OS 17_0)'));
    }

    #[Test]
    public function detect_device_type_returns_desktop_for_desktop_user_agent(): void
    {
        $this->assertSame(DeviceType::Desktop, WatchService::detectDeviceType('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'));
    }

    #[Test]
    public function detect_device_type_case_insensitive(): void
    {
        $this->assertSame(DeviceType::Mobile, WatchService::detectDeviceType('USER-AGENT: ANDROID 13'));
    }
}