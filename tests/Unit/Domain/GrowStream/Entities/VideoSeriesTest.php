<?php

namespace Tests\Unit\Domain\GrowStream\Entities;

use App\Domain\GrowStream\Entities\VideoSeries;
use App\Domain\GrowStream\ValueObjects\AccessLevel;
use App\Domain\GrowStream\ValueObjects\CreatorProfileId;
use App\Domain\GrowStream\ValueObjects\SeriesType;
use App\Domain\GrowStream\ValueObjects\SkillLevel;
use App\Domain\GrowStream\ValueObjects\StarterKitTier;
use PHPUnit\Framework\TestCase;

class VideoSeriesTest extends TestCase
{
    private CreatorProfileId $creatorId;

    protected function setUp(): void
    {
        $this->creatorId = CreatorProfileId::fromInt(1);
    }

    public function test_create_sets_defaults(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'My Series',
            seriesType: SeriesType::Course,
        );

        $this->assertNull($series->id());
        $this->assertSame('My Series', $series->title());
        $this->assertSame(SeriesType::Course, $series->seriesType());
        $this->assertSame(AccessLevel::Free, $series->accessLevel());
        $this->assertNull($series->description());
        $this->assertNull($series->thumbnailUrl());
        $this->assertNull($series->coverUrl());
        $this->assertNull($series->skillLevel());
        $this->assertNull($series->starterKitTier());
        $this->assertSame(0, $series->episodeCount());
        $this->assertSame(0, $series->totalDurationSeconds());
        $this->assertFalse($series->isPublished());
        $this->assertNull($series->publishedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $series->createdAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $series->updatedAt());
    }

    public function test_create_with_all_optional_fields(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'Advanced Course',
            seriesType: SeriesType::Documentary,
            accessLevel: AccessLevel::Premium,
            description: 'A premium documentary series',
            skillLevel: SkillLevel::Advanced,
            starterKitTier: StarterKitTier::Elite,
        );

        $this->assertSame('Advanced Course', $series->title());
        $this->assertSame(SeriesType::Documentary, $series->seriesType());
        $this->assertSame(AccessLevel::Premium, $series->accessLevel());
        $this->assertSame('A premium documentary series', $series->description());
        $this->assertSame(SkillLevel::Advanced, $series->skillLevel());
        $this->assertSame(StarterKitTier::Elite, $series->starterKitTier());
    }

    public function test_reconstitute_restores_series(): void
    {
        $series = VideoSeries::reconstitute([
            'id' => 10,
            'creator_profile_id' => 3,
            'title' => 'Reconstituted Series',
            'description' => 'From DB',
            'series_type' => 'show',
            'thumbnail_url' => 'https://example.com/thumb.jpg',
            'cover_url' => 'https://example.com/cover.jpg',
            'access_level' => 'institutional',
            'skill_level' => 'beginner',
            'starter_kit_tier' => 'basic',
            'episode_count' => 12,
            'total_duration_seconds' => 36000,
            'is_published' => 1,
            'published_at' => '2026-03-01 08:00:00',
            'created_at' => '2026-02-01 00:00:00',
            'updated_at' => '2026-03-01 08:00:00',
        ]);

        $this->assertSame(10, $series->id());
        $this->assertEquals(3, $series->creatorProfileId()->toInt());
        $this->assertSame('Reconstituted Series', $series->title());
        $this->assertSame('From DB', $series->description());
        $this->assertSame(SeriesType::Show, $series->seriesType());
        $this->assertSame('https://example.com/thumb.jpg', $series->thumbnailUrl());
        $this->assertSame('https://example.com/cover.jpg', $series->coverUrl());
        $this->assertSame(AccessLevel::Institutional, $series->accessLevel());
        $this->assertSame(SkillLevel::Beginner, $series->skillLevel());
        $this->assertSame(StarterKitTier::Basic, $series->starterKitTier());
        $this->assertSame(12, $series->episodeCount());
        $this->assertSame(36000, $series->totalDurationSeconds());
        $this->assertTrue($series->isPublished());
    }

    public function test_reconstitute_with_minimal_data(): void
    {
        $series = VideoSeries::reconstitute([
            'creator_profile_id' => 1,
            'title' => 'Minimal',
            'series_type' => 'workshop_series',
            'access_level' => 'free',
        ]);

        $this->assertNull($series->id());
        $this->assertNull($series->description());
        $this->assertNull($series->thumbnailUrl());
        $this->assertNull($series->coverUrl());
        $this->assertNull($series->skillLevel());
        $this->assertNull($series->starterKitTier());
        $this->assertSame(0, $series->episodeCount());
        $this->assertSame(0, $series->totalDurationSeconds());
        $this->assertFalse($series->isPublished());
        $this->assertNull($series->publishedAt());
    }

    public function test_publish_sets_published(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            seriesType: SeriesType::Course,
        );
        $series->publish();

        $this->assertTrue($series->isPublished());
        $this->assertInstanceOf(\DateTimeImmutable::class, $series->publishedAt());
    }

    public function test_unpublish_removes_published(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            seriesType: SeriesType::Course,
        );
        $series->publish();
        $series->unpublish();

        $this->assertFalse($series->isPublished());
        $this->assertNull($series->publishedAt());
    }

    public function test_set_episode_count(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            seriesType: SeriesType::Course,
        );
        $series->setEpisodeCount(15);

        $this->assertSame(15, $series->episodeCount());
    }

    public function test_set_episode_count_rejects_negative(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            seriesType: SeriesType::Course,
        );

        $this->expectException(\InvalidArgumentException::class);
        $series->setEpisodeCount(-1);
    }

    public function test_set_total_duration(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            seriesType: SeriesType::Course,
        );
        $series->setTotalDuration(7200);

        $this->assertSame(7200, $series->totalDurationSeconds());
    }

    public function test_set_total_duration_rejects_negative(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            seriesType: SeriesType::Course,
        );

        $this->expectException(\InvalidArgumentException::class);
        $series->setTotalDuration(-100);
    }

    public function test_update_thumbnail(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            seriesType: SeriesType::Course,
        );
        $series->updateThumbnail('https://example.com/thumb.jpg');

        $this->assertSame('https://example.com/thumb.jpg', $series->thumbnailUrl());
    }

    public function test_update_cover(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            seriesType: SeriesType::Course,
        );
        $series->updateCover('https://example.com/cover.jpg');

        $this->assertSame('https://example.com/cover.jpg', $series->coverUrl());
    }

    public function test_update_description(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            seriesType: SeriesType::Course,
        );
        $series->updateDescription('Updated description');

        $this->assertSame('Updated description', $series->description());
    }

    public function test_can_be_accessed_by_free_user_can_access_free(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            seriesType: SeriesType::Course,
            accessLevel: AccessLevel::Free,
        );

        $this->assertTrue($series->canBeAccessedBy('free'));
        $this->assertTrue($series->canBeAccessedBy('basic'));
    }

    public function test_can_be_accessed_by_premium_user_can_access_premium(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            seriesType: SeriesType::Course,
            accessLevel: AccessLevel::Premium,
        );

        $this->assertFalse($series->canBeAccessedBy('free'));
        $this->assertFalse($series->canBeAccessedBy('basic'));
        $this->assertTrue($series->canBeAccessedBy('premium'));
        $this->assertTrue($series->canBeAccessedBy('institutional'));
    }

    public function test_can_be_accessed_by_unknown_level_returns_false(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'Test',
            seriesType: SeriesType::Course,
            accessLevel: AccessLevel::Basic,
        );

        $this->assertFalse($series->canBeAccessedBy('unknown'));
    }

    public function test_to_array_returns_all_fields(): void
    {
        $series = VideoSeries::create(
            creatorProfileId: $this->creatorId,
            title: 'Serialize Test',
            seriesType: SeriesType::WorkshopSeries,
            accessLevel: AccessLevel::Basic,
            description: 'Desc',
            skillLevel: SkillLevel::Intermediate,
            starterKitTier: StarterKitTier::Premium,
        );
        $series->publish();
        $series->setEpisodeCount(8);
        $series->setTotalDuration(14400);

        $arr = $series->toArray();

        $this->assertNull($arr['id']);
        $this->assertSame(1, $arr['creator_profile_id']);
        $this->assertSame('Serialize Test', $arr['title']);
        $this->assertSame('Desc', $arr['description']);
        $this->assertSame('workshop_series', $arr['series_type']);
        $this->assertSame('basic', $arr['access_level']);
        $this->assertSame('intermediate', $arr['skill_level']);
        $this->assertSame('premium', $arr['starter_kit_tier']);
        $this->assertSame(8, $arr['episode_count']);
        $this->assertSame(14400, $arr['total_duration_seconds']);
        $this->assertTrue($arr['is_published']);
    }
}
