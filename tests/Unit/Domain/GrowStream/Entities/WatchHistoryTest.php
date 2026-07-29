<?php

namespace Tests\Unit\Domain\GrowStream\Entities;

use App\Domain\GrowStream\Entities\WatchHistory;
use App\Domain\GrowStream\ValueObjects\DeviceType;
use App\Domain\GrowStream\ValueObjects\VideoId;
use PHPUnit\Framework\TestCase;

class WatchHistoryTest extends TestCase
{
    private VideoId $videoId;

    protected function setUp(): void
    {
        $this->videoId = VideoId::fromInt(1);
    }

    public function test_create_sets_defaults(): void
    {
        $entry = WatchHistory::create(
            userId: 42,
            videoId: $this->videoId,
        );

        $this->assertNull($entry->id());
        $this->assertSame(42, $entry->userId());
        $this->assertTrue($this->videoId->equals($entry->videoId()));
        $this->assertSame(0, $entry->watchedSeconds());
        $this->assertFalse($entry->completed());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entry->watchedAt());
        $this->assertNull($entry->deviceType());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entry->createdAt());
    }

    public function test_create_with_custom_watched_seconds(): void
    {
        $entry = WatchHistory::create(
            userId: 42,
            videoId: $this->videoId,
            watchedSeconds: 120,
        );

        $this->assertSame(120, $entry->watchedSeconds());
    }

    public function test_create_with_device_type(): void
    {
        $entry = WatchHistory::create(
            userId: 42,
            videoId: $this->videoId,
            watchedSeconds: 0,
            deviceType: DeviceType::Mobile,
        );

        $this->assertSame(DeviceType::Mobile, $entry->deviceType());
    }

    public function test_reconstitute_restores_watch_history(): void
    {
        $entry = WatchHistory::reconstitute([
            'id' => 99,
            'user_id' => 10,
            'video_id' => 5,
            'watched_seconds' => 300,
            'completed' => 1,
            'watched_at' => '2026-07-01 14:30:00',
            'device_type' => 'desktop',
            'created_at' => '2026-07-01 14:30:00',
        ]);

        $this->assertSame(99, $entry->id());
        $this->assertSame(10, $entry->userId());
        $this->assertEquals(5, $entry->videoId()->toInt());
        $this->assertSame(300, $entry->watchedSeconds());
        $this->assertTrue($entry->completed());
        $this->assertSame(DeviceType::Desktop, $entry->deviceType());
    }

    public function test_reconstitute_with_minimal_data(): void
    {
        $entry = WatchHistory::reconstitute([
            'user_id' => 1,
            'video_id' => 1,
            'watched_at' => '2026-01-01 00:00:00',
        ]);

        $this->assertNull($entry->id());
        $this->assertSame(0, $entry->watchedSeconds());
        $this->assertFalse($entry->completed());
        $this->assertNull($entry->deviceType());
        $this->assertNull($entry->createdAt());
    }

    public function test_resume_adds_seconds(): void
    {
        $entry = WatchHistory::create(
            userId: 42,
            videoId: $this->videoId,
            watchedSeconds: 30,
        );
        $entry->resume(additionalSeconds: 60, videoDuration: 300);

        $this->assertSame(90, $entry->watchedSeconds());
        $this->assertFalse($entry->completed());
    }

    public function test_resume_marks_completed_when_exceeding_duration(): void
    {
        $entry = WatchHistory::create(
            userId: 42,
            videoId: $this->videoId,
            watchedSeconds: 200,
        );
        $entry->resume(additionalSeconds: 200, videoDuration: 300);

        $this->assertSame(400, $entry->watchedSeconds());
        $this->assertTrue($entry->completed());
    }

    public function test_resume_marks_completed_at_exact_duration(): void
    {
        $entry = WatchHistory::create(
            userId: 42,
            videoId: $this->videoId,
            watchedSeconds: 100,
        );
        $entry->resume(additionalSeconds: 200, videoDuration: 300);

        $this->assertTrue($entry->completed());
    }

    public function test_resume_with_zero_duration_does_not_mark_completed(): void
    {
        $entry = WatchHistory::create(
            userId: 42,
            videoId: $this->videoId,
            watchedSeconds: 100,
        );
        $entry->resume(additionalSeconds: 50, videoDuration: 0);

        $this->assertSame(150, $entry->watchedSeconds());
        $this->assertFalse($entry->completed());
    }

    public function test_resume_rejects_negative_seconds(): void
    {
        $entry = WatchHistory::create(
            userId: 42,
            videoId: $this->videoId,
            watchedSeconds: 50,
        );

        $this->expectException(\InvalidArgumentException::class);
        $entry->resume(additionalSeconds: -10, videoDuration: 300);
    }

    public function test_mark_completed(): void
    {
        $entry = WatchHistory::create(
            userId: 42,
            videoId: $this->videoId,
        );
        $entry->markCompleted();

        $this->assertTrue($entry->completed());
    }

    public function test_get_progress_percent_returns_percentage(): void
    {
        $entry = WatchHistory::create(
            userId: 42,
            videoId: $this->videoId,
            watchedSeconds: 75,
        );

        $this->assertSame(25.0, $entry->getProgressPercent(videoDuration: 300));
    }

    public function test_get_progress_percent_returns_100_when_exceeds(): void
    {
        $entry = WatchHistory::create(
            userId: 42,
            videoId: $this->videoId,
            watchedSeconds: 400,
        );

        $this->assertSame(100.0, $entry->getProgressPercent(videoDuration: 300));
    }

    public function test_get_progress_percent_returns_zero_for_no_watch(): void
    {
        $entry = WatchHistory::create(
            userId: 42,
            videoId: $this->videoId,
            watchedSeconds: 0,
        );

        $this->assertSame(0.0, $entry->getProgressPercent(videoDuration: 300));
    }

    public function test_get_progress_percent_returns_zero_for_zero_duration(): void
    {
        $entry = WatchHistory::create(
            userId: 42,
            videoId: $this->videoId,
            watchedSeconds: 100,
        );

        $this->assertSame(0.0, $entry->getProgressPercent(videoDuration: 0));
    }

    public function test_to_array_returns_all_fields(): void
    {
        $entry = WatchHistory::create(
            userId: 7,
            videoId: $this->videoId,
            watchedSeconds: 150,
            deviceType: DeviceType::Tablet,
        );
        $entry->markCompleted();

        $arr = $entry->toArray();

        $this->assertNull($arr['id']);
        $this->assertSame(7, $arr['user_id']);
        $this->assertSame(1, $arr['video_id']);
        $this->assertSame(150, $arr['watched_seconds']);
        $this->assertTrue($arr['completed']);
        $this->assertSame('tablet', $arr['device_type']);
        $this->assertArrayHasKey('watched_at', $arr);
        $this->assertArrayHasKey('created_at', $arr);
    }
}
