<?php

namespace Tests\Unit\Domain\GrowStream\Entities;

use App\Domain\GrowStream\Entities\Video;
use App\Domain\GrowStream\ValueObjects\AccessLevel;
use App\Domain\GrowStream\ValueObjects\ContentRating;
use App\Domain\GrowStream\ValueObjects\ContentType;
use App\Domain\GrowStream\ValueObjects\CreatorProfileId;
use App\Domain\GrowStream\ValueObjects\PlaybackPolicy;
use App\Domain\GrowStream\ValueObjects\SeriesType;
use App\Domain\GrowStream\ValueObjects\SkillLevel;
use App\Domain\GrowStream\ValueObjects\StarterKitTier;
use App\Domain\GrowStream\ValueObjects\UploadStatus;
use App\Domain\GrowStream\ValueObjects\VideoId;
use App\Domain\GrowStream\ValueObjects\VideoProvider;
use PHPUnit\Framework\TestCase;

class VideoTest extends TestCase
{
    private CreatorProfileId $creatorId;

    protected function setUp(): void
    {
        $this->creatorId = CreatorProfileId::fromInt(1);
    }

    public function test_create_sets_defaults(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test Video',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );

        $this->assertNull($video->id());
        $this->assertSame('Test Video', $video->title());
        $this->assertSame(UploadStatus::Pending, $video->uploadStatus());
        $this->assertFalse($video->isPublished());
        $this->assertNull($video->publishedAt());
        $this->assertSame(0, $video->viewsCount());
        $this->assertSame([], $video->tags());
        $this->assertSame([], $video->metadata());
        $this->assertInstanceOf(\DateTimeImmutable::class, $video->createdAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $video->updatedAt());
    }

    public function test_create_with_all_optional_fields(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Advanced Course',
            contentType: ContentType::Webinar,
            accessLevel: AccessLevel::Premium,
            durationSeconds: 3600,
            description: 'An advanced course',
            contentRating: ContentRating::PG13,
            skillLevel: SkillLevel::Advanced,
            provider: VideoProvider::Cloudflare,
            playbackPolicy: PlaybackPolicy::Signed,
            seriesType: SeriesType::Course,
            seriesId: 5,
            seasonNumber: 1,
            episodeNumber: 3,
            starterKitTier: StarterKitTier::Premium,
        );

        $this->assertNull($video->id());
        $this->assertSame(3600, $video->durationSeconds());
        $this->assertSame('An advanced course', $video->description());
        $this->assertSame(ContentRating::PG13, $video->contentRating());
        $this->assertSame(SkillLevel::Advanced, $video->skillLevel());
        $this->assertSame(VideoProvider::Cloudflare, $video->provider());
        $this->assertSame(PlaybackPolicy::Signed, $video->playbackPolicy());
        $this->assertSame(SeriesType::Course, $video->seriesType());
        $this->assertSame(5, $video->seriesId());
        $this->assertSame(1, $video->seasonNumber());
        $this->assertSame(3, $video->episodeNumber());
        $this->assertSame(StarterKitTier::Premium, $video->starterKitTier());
    }

    public function test_reconstitute_restores_video(): void
    {
        $video = Video::reconstitute([
            'id' => 42,
            'creator_profile_id' => 2,
            'title' => 'Reconstituted Video',
            'description' => 'From DB',
            'content_type' => 'movie',
            'duration_seconds' => 1800,
            'access_level' => 'premium',
            'content_rating' => 'R',
            'skill_level' => 'expert',
            'upload_status' => 'ready',
            'video_url' => 'https://example.com/video.mp4',
            'thumbnail_url' => 'https://example.com/thumb.jpg',
            'provider' => 'cloudflare',
            'playback_policy' => 'public',
            'series_type' => 'show',
            'series_id' => 10,
            'season_number' => 2,
            'episode_number' => 5,
            'tags' => '["tag1","tag2"]',
            'metadata' => '{"key":"value"}',
            'is_published' => 1,
            'published_at' => '2026-01-15 10:00:00',
            'views_count' => 99,
            'starter_kit_tier' => 'elite',
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-15 10:00:00',
        ]);

        $this->assertInstanceOf(VideoId::class, $video->id());
        $this->assertEquals(42, $video->id()->toInt());
        $this->assertEquals(2, $video->creatorProfileId()->toInt());
        $this->assertSame('Reconstituted Video', $video->title());
        $this->assertSame('From DB', $video->description());
        $this->assertSame(ContentType::Movie, $video->contentType());
        $this->assertSame(1800, $video->durationSeconds());
        $this->assertSame(AccessLevel::Premium, $video->accessLevel());
        $this->assertSame(ContentRating::R, $video->contentRating());
        $this->assertSame(SkillLevel::Expert, $video->skillLevel());
        $this->assertSame(UploadStatus::Ready, $video->uploadStatus());
        $this->assertSame('https://example.com/video.mp4', $video->videoUrl());
        $this->assertSame('https://example.com/thumb.jpg', $video->thumbnailUrl());
        $this->assertSame(VideoProvider::Cloudflare, $video->provider());
        $this->assertSame(PlaybackPolicy::Public, $video->playbackPolicy());
        $this->assertSame(SeriesType::Show, $video->seriesType());
        $this->assertSame(10, $video->seriesId());
        $this->assertSame(2, $video->seasonNumber());
        $this->assertSame(5, $video->episodeNumber());
        $this->assertSame(['tag1', 'tag2'], $video->tags());
        $this->assertSame(['key' => 'value'], $video->metadata());
        $this->assertTrue($video->isPublished());
        $this->assertSame(99, $video->viewsCount());
        $this->assertSame(StarterKitTier::Elite, $video->starterKitTier());
    }

    public function test_reconstitute_with_minimal_data(): void
    {
        $video = Video::reconstitute([
            'creator_profile_id' => 1,
            'title' => 'Minimal',
            'content_type' => 'short',
            'access_level' => 'free',
            'upload_status' => 'pending',
            'provider' => 'local',
            'playback_policy' => 'public',
        ]);

        $this->assertNull($video->id());
        $this->assertNull($video->description());
        $this->assertNull($video->durationSeconds());
        $this->assertNull($video->contentRating());
        $this->assertNull($video->skillLevel());
        $this->assertNull($video->seriesType());
        $this->assertNull($video->seriesId());
        $this->assertNull($video->starterKitTier());
        $this->assertSame([], $video->tags());
        $this->assertSame([], $video->metadata());
        $this->assertFalse($video->isPublished());
        $this->assertSame(0, $video->viewsCount());
    }

    public function test_publish_marks_video_as_published(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );
        $video->markReady();
        $video->publish();

        $this->assertTrue($video->isPublished());
        $this->assertInstanceOf(\DateTimeImmutable::class, $video->publishedAt());
    }

    public function test_publish_throws_when_not_ready(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot publish video that is not ready');
        $video->publish();
    }

    public function test_unpublish_removes_published_state(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );
        $video->markReady();
        $video->publish();
        $video->unpublish();

        $this->assertFalse($video->isPublished());
        $this->assertNull($video->publishedAt());
    }

    public function test_mark_ready_transitions_from_pending(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );
        $video->markReady();

        $this->assertSame(UploadStatus::Ready, $video->uploadStatus());
    }

    public function test_mark_ready_throws_from_failed(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );
        $video->markFailed();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot mark failed video as ready');
        $video->markReady();
    }

    public function test_mark_failed_transitions_to_failed_and_unpublishes(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );
        $video->markReady();
        $video->publish();
        $video->markFailed();

        $this->assertSame(UploadStatus::Failed, $video->uploadStatus());
        $this->assertFalse($video->isPublished());
    }

    public function test_increment_views_increases_count(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );

        $this->assertSame(0, $video->viewsCount());
        $video->incrementViews();
        $this->assertSame(1, $video->viewsCount());
        $video->incrementViews();
        $this->assertSame(2, $video->viewsCount());
    }

    public function test_update_thumbnail(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );
        $video->updateThumbnail('https://example.com/new-thumb.jpg');

        $this->assertSame('https://example.com/new-thumb.jpg', $video->thumbnailUrl());
    }

    public function test_update_video_url(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );
        $video->updateVideoUrl('https://example.com/video.mp4');

        $this->assertSame('https://example.com/video.mp4', $video->videoUrl());
    }

    public function test_update_duration(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );
        $video->updateDuration(300);

        $this->assertSame(300, $video->durationSeconds());
    }

    public function test_update_duration_rejects_negative(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );

        $this->expectException(\InvalidArgumentException::class);
        $video->updateDuration(-1);
    }

    public function test_update_metadata(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );
        $video->updateMetadata(['author' => 'John', 'year' => 2026]);

        $this->assertSame(['author' => 'John', 'year' => 2026], $video->metadata());
    }

    public function test_add_tag_merges_unique(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );
        $video->addTag('tutorial');
        $video->addTag('php');
        $video->addTag('tutorial');

        $this->assertSame(['tutorial', 'php'], $video->tags());
    }

    public function test_remove_tag_filters_out(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );
        $video->addTag('tutorial');
        $video->addTag('php');
        $video->addTag('advanced');
        $video->removeTag('php');

        $this->assertSame(['tutorial', 'advanced'], $video->tags());
    }

    public function test_assign_to_sets_all_series_fields(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Episode,
            accessLevel: AccessLevel::Free,
        );
        $video->assignToSeries(seriesId: 10, season: 2, episode: 7);

        $this->assertSame(10, $video->seriesId());
        $this->assertSame(2, $video->seasonNumber());
        $this->assertSame(7, $video->episodeNumber());
    }

    public function test_assign_to_series_without_season_episode(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Movie,
            accessLevel: AccessLevel::Free,
        );
        $video->assignToSeries(seriesId: 5);

        $this->assertSame(5, $video->seriesId());
        $this->assertNull($video->seasonNumber());
        $this->assertNull($video->episodeNumber());
    }

    public function test_can_be_accessed_by_free_user_can_access_free(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
        );

        $this->assertTrue($video->canBeAccessedBy('free'));
        $this->assertTrue($video->canBeAccessedBy('basic'));
        $this->assertTrue($video->canBeAccessedBy('premium'));
    }

    public function test_can_be_accessed_by_basic_user_cannot_access_premium(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Premium,
        );

        $this->assertFalse($video->canBeAccessedBy('free'));
        $this->assertFalse($video->canBeAccessedBy('basic'));
        $this->assertTrue($video->canBeAccessedBy('premium'));
        $this->assertTrue($video->canBeAccessedBy('institutional'));
    }

    public function test_can_be_accessed_by_unknown_level_returns_false(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Premium,
        );

        $this->assertFalse($video->canBeAccessedBy('unknown'));
    }

    public function test_to_array_returns_all_fields(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Serialize Test',
            contentType: ContentType::Webinar,
            accessLevel: AccessLevel::Institutional,
            durationSeconds: 7200,
            starterKitTier: StarterKitTier::All,
        );
        $video->addTag('test');
        $video->updateMetadata(['key' => 'val']);

        $arr = $video->toArray();

        $this->assertNull($arr['id']);
        $this->assertSame(1, $arr['creator_profile_id']);
        $this->assertSame('Serialize Test', $arr['title']);
        $this->assertSame('webinar', $arr['content_type']);
        $this->assertSame('institutional', $arr['access_level']);
        $this->assertSame(7200, $arr['duration_seconds']);
        $this->assertSame('pending', $arr['upload_status']);
        $this->assertFalse($arr['is_published']);
        $this->assertSame(0, $arr['views_count']);
        $this->assertSame(['test'], $arr['tags']);
        $this->assertSame(['key' => 'val'], $arr['metadata']);
        $this->assertSame('all', $arr['starter_kit_tier']);
    }

    public function test_getters_return_immutable_values(): void
    {
        $video = Video::create(
            creatorProfileId: $this->creatorId,
            title: 'Immutability',
            contentType: ContentType::Short,
            accessLevel: AccessLevel::Free,
            description: 'Desc',
        );

        $this->assertSame('Immutability', $video->title());
        $this->assertSame('Desc', $video->description());
        $this->assertSame(ContentType::Short, $video->contentType());
        $this->assertSame(AccessLevel::Free, $video->accessLevel());
        $this->assertSame(VideoProvider::Cloudflare, $video->provider());
        $this->assertSame(PlaybackPolicy::Public, $video->playbackPolicy());
        $this->assertNull($video->seriesType());
        $this->assertNull($video->contentRating());
        $this->assertNull($video->skillLevel());
    }
}
