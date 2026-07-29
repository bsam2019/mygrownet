<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStream\Services;

use App\Domain\GrowStream\Entities\Video as VideoEntity;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoTagRepositoryInterface;
use App\Domain\GrowStream\Services\VideoManagementService;
use App\Domain\GrowStream\ValueObjects\AccessLevel;
use App\Domain\GrowStream\ValueObjects\ContentRating;
use App\Domain\GrowStream\ValueObjects\ContentType;
use App\Domain\GrowStream\ValueObjects\CreatorProfileId;
use App\Domain\GrowStream\ValueObjects\PlaybackPolicy;
use App\Domain\GrowStream\ValueObjects\SkillLevel;
use App\Domain\GrowStream\ValueObjects\StarterKitTier;
use App\Domain\GrowStream\ValueObjects\UploadStatus;
use App\Domain\GrowStream\ValueObjects\VideoProvider;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[\AllowMockObjectsWithoutExpectations]
final class VideoManagementServiceTest extends TestCase
{
    private VideoRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject $videoRepo;
    private VideoManagementService $service;

    protected function setUp(): void
    {
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->service = new VideoManagementService($this->videoRepo, $this->createStub(VideoTagRepositoryInterface::class));
    }

    private function createReadyVideoData(int $id = 1, array $overrides = []): array
    {
        $entity = VideoEntity::create(
            creatorProfileId: CreatorProfileId::fromInt(1),
            title: $overrides['title'] ?? 'Test Video',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
            description: $overrides['description'] ?? null,
            provider: VideoProvider::Local,
            playbackPolicy: PlaybackPolicy::Public,
        );

        $data = $entity->toArray();
        $data['id'] = $id;
        $data['upload_status'] = UploadStatus::Ready->value;

        return array_merge($data, $overrides);
    }

    private function createMockEloquentVideo(array $data): Video
    {
        $mock = $this->createStub(Video::class);

        $mock->method('toArray')->willReturn($data);

        $id = $data['id'] ?? null;
        if ($id !== null) {
            $mock->method('__get')->willReturnCallback(fn(string $key) => match ($key) {
                'id' => $id,
                default => null,
            });
        }

        return $mock;
    }

    #[Test]
    public function create_video_with_required_params(): void
    {
        $creatorId = CreatorProfileId::fromInt(1);

        $this->videoRepo
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (array $data) {
                $data['id'] = 5;
                return $this->createMockEloquentVideo($data);
            });

        $result = $this->service->createVideo(
            creatorId: $creatorId,
            title: 'My First Video',
            description: null,
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
            contentRating: null,
            skillLevel: null,
            provider: VideoProvider::Local,
            playbackPolicy: PlaybackPolicy::Public,
        );

        $this->assertSame(5, $result['id']);
        $this->assertSame('My First Video', $result['video']['title']);
        $this->assertSame('lesson', $result['video']['content_type']);
        $this->assertFalse($result['video']['is_published']);
        $this->assertSame('pending', $result['video']['upload_status']);
    }

    #[Test]
    public function create_video_with_all_params_and_tags(): void
    {
        $creatorId = CreatorProfileId::fromInt(2);

        $this->videoRepo
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function () {
                return $this->createMockEloquentVideo(['id' => 10]);
            });

        $tagMock = $this->createMock(VideoTagRepositoryInterface::class);
        $tagMock
            ->expects($this->exactly(3))
            ->method('addTag')
            ->willReturnCallback(function (int $videoId, string $tag) {
                $this->assertSame(10, $videoId);
                return $this->createStub(\App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoTag::class);
            });
        $this->service = new VideoManagementService($this->videoRepo, $tagMock);

        $result = $this->service->createVideo(
            creatorId: $creatorId,
            title: 'Advanced PHP',
            description: 'Deep dive into PHP',
            contentType: ContentType::Workshop,
            accessLevel: AccessLevel::Premium,
            contentRating: ContentRating::PG13,
            skillLevel: SkillLevel::Advanced,
            provider: VideoProvider::Cloudflare,
            playbackPolicy: PlaybackPolicy::Signed,
            tags: ['php', 'advanced', 'laravel'],
            seriesId: 3,
            seasonNumber: 1,
            episodeNumber: 5,
            starterKitTier: StarterKitTier::Elite,
        );

        $this->assertSame(10, $result['id']);
        $this->assertSame('Advanced PHP', $result['video']['title']);
        $this->assertSame('Deep dive into PHP', $result['video']['description']);
        $this->assertSame('workshop', $result['video']['content_type']);
        $this->assertSame('premium', $result['video']['access_level']);
        $this->assertSame('PG-13', $result['video']['content_rating']);
        $this->assertSame('advanced', $result['video']['skill_level']);
        $this->assertSame('cloudflare', $result['video']['provider']);
        $this->assertSame('signed', $result['video']['playback_policy']);
        $this->assertSame(3, $result['video']['series_id']);
        $this->assertSame(1, $result['video']['season_number']);
        $this->assertSame(5, $result['video']['episode_number']);
        $this->assertSame('elite', $result['video']['starter_kit_tier']);
    }

    #[Test]
    public function update_video_updates_fields(): void
    {
        $data = $this->createReadyVideoData(1);

        $eloquent = $this->createMockEloquentVideo($data);

        $this->videoRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($eloquent);

        $updatedData = array_merge($data, ['title' => 'Updated Title', 'description' => 'New desc']);

        $updatedMock = $this->createMockEloquentVideo($updatedData);

        $this->videoRepo
            ->expects($this->once())
            ->method('update')
            ->with($eloquent, ['title' => 'Updated Title', 'description' => 'New desc'])
            ->willReturn($updatedMock);

        $result = $this->service->updateVideo(1, ['title' => 'Updated Title', 'description' => 'New desc']);

        $this->assertSame('Updated Title', $result['title']);
        $this->assertSame('New desc', $result['description']);
    }

    #[Test]
    public function update_video_throws_when_not_found(): void
    {
        $this->videoRepo
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Video not found: 999');

        $this->service->updateVideo(999, ['title' => 'Nope']);
    }

    #[Test]
    public function publish_video_succeeds_when_ready(): void
    {
        $data = $this->createReadyVideoData(1);

        $eloquent = $this->createMockEloquentVideo($data);

        $this->videoRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($eloquent);

        $this->videoRepo
            ->expects($this->once())
            ->method('update')
            ->with($eloquent, $this->isArray());

        $this->service->publishVideo(1);
    }

    #[Test]
    public function publish_video_throws_when_not_ready(): void
    {
        $entity = VideoEntity::create(
            creatorProfileId: CreatorProfileId::fromInt(1),
            title: 'Pending Video',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
            provider: VideoProvider::Local,
            playbackPolicy: PlaybackPolicy::Public,
        );
        $data = $entity->toArray();
        $data['id'] = 1;
        // upload_status defaults to 'pending'

        $eloquent = $this->createMockEloquentVideo($data);

        $this->videoRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($eloquent);

        $this->videoRepo
            ->expects($this->never())
            ->method('update');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot publish video that is not ready');

        $this->service->publishVideo(1);
    }

    #[Test]
    public function unpublish_video_succeeds(): void
    {
        $data = $this->createReadyVideoData(1, ['is_published' => true]);

        $eloquent = $this->createMockEloquentVideo($data);

        $this->videoRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($eloquent);

        $this->videoRepo
            ->expects($this->once())
            ->method('update')
            ->with($eloquent, $this->isArray());

        $this->service->unpublishVideo(1);
    }

    #[Test]
    public function delete_video_deletes(): void
    {
        $data = $this->createReadyVideoData(1);

        $eloquent = $this->createMockEloquentVideo($data);

        $this->videoRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($eloquent);

        $this->videoRepo
            ->expects($this->once())
            ->method('delete')
            ->with($eloquent)
            ->willReturn(true);

        $this->service->deleteVideo(1);
    }

    #[Test]
    public function delete_video_throws_when_not_found(): void
    {
        $this->videoRepo
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Video not found: 999');

        $this->service->deleteVideo(999);
    }

    #[Test]
    public function bulk_action_publish_iterates(): void
    {
        $data = $this->createReadyVideoData(1);

        $eloquent = $this->createMockEloquentVideo($data);

        $this->videoRepo
            ->expects($this->exactly(3))
            ->method('findById')
            ->willReturnCallback(function (int $id) use ($data, $eloquent) {
                $copy = $this->createMockEloquentVideo(array_merge($data, ['id' => $id]));
                return $copy;
            });

        $this->videoRepo
            ->expects($this->exactly(3))
            ->method('update')
            ->with($this->isInstanceOf(Video::class), $this->isArray());

        $this->service->bulkAction([1, 2, 3], 'publish');
    }

    #[Test]
    public function bulk_action_unpublish_uses_update_in_bulk(): void
    {
        $this->videoRepo
            ->expects($this->once())
            ->method('updateInBulk')
            ->with([1, 2, 3], ['is_published' => false, 'published_at' => null]);

        $this->service->bulkAction([1, 2, 3], 'unpublish');
    }

    #[Test]
    public function bulk_action_delete_uses_bulk_delete(): void
    {
        $this->videoRepo
            ->expects($this->once())
            ->method('bulkDelete')
            ->with([1, 2, 3]);

        $this->service->bulkAction([1, 2, 3], 'delete');
    }

    #[Test]
    public function bulk_action_throws_for_unknown_action(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown bulk action: archive');

        $this->service->bulkAction([1], 'archive');
    }

    #[Test]
    public function sync_tags_removes_old_and_adds_new(): void
    {
        $tagMock = $this->createMock(VideoTagRepositoryInterface::class);
        $tagMock
            ->expects($this->once())
            ->method('deleteByVideo')
            ->with(1);

        $tagMock
            ->expects($this->exactly(2))
            ->method('addTag')
            ->willReturnCallback(function (int $videoId, string $tag) {
                $this->assertSame(1, $videoId);
                return $this->createStub(\App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoTag::class);
            });
        $this->service = new VideoManagementService($this->videoRepo, $tagMock);

        $this->service->syncTags(1, ['new-tag', 'another-tag']);
    }

    #[Test]
    public function sync_tags_with_empty_array_removes_all(): void
    {
        $tagMock = $this->createMock(VideoTagRepositoryInterface::class);
        $tagMock
            ->expects($this->once())
            ->method('deleteByVideo')
            ->with(1);

        $tagMock
            ->expects($this->never())
            ->method('addTag');
        $this->service = new VideoManagementService($this->videoRepo, $tagMock);

        $this->service->syncTags(1, []);
    }

    #[Test]
    public function update_thumbnail_delegates(): void
    {
        $data = $this->createReadyVideoData(1);

        $eloquent = $this->createMockEloquentVideo($data);

        $this->videoRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($eloquent);

        $this->videoRepo
            ->expects($this->once())
            ->method('update')
            ->with($eloquent, $this->isArray());

        $this->service->updateThumbnail(1, 'https://example.com/thumb.jpg');
    }

    #[Test]
    public function update_video_url_delegates(): void
    {
        $data = $this->createReadyVideoData(1);

        $eloquent = $this->createMockEloquentVideo($data);

        $this->videoRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($eloquent);

        $this->videoRepo
            ->expects($this->once())
            ->method('update')
            ->with($eloquent, $this->isArray());

        $this->service->updateVideoUrl(1, 'https://cdn.example.com/video.mp4');
    }

    #[Test]
    public function update_duration_delegates(): void
    {
        $data = $this->createReadyVideoData(1);

        $eloquent = $this->createMockEloquentVideo($data);

        $this->videoRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($eloquent);

        $this->videoRepo
            ->expects($this->once())
            ->method('update')
            ->with($eloquent, $this->isArray());

        $this->service->updateDuration(1, 3600);
    }

    #[Test]
    public function mark_upload_status_ready(): void
    {
        $entity = VideoEntity::create(
            creatorProfileId: CreatorProfileId::fromInt(1),
            title: 'Processing Video',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
            provider: VideoProvider::Local,
            playbackPolicy: PlaybackPolicy::Public,
        );
        $data = $entity->toArray();
        $data['id'] = 1;
        $data['upload_status'] = 'processing';

        $eloquent = $this->createMockEloquentVideo($data);

        $this->videoRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($eloquent);

        $this->videoRepo
            ->expects($this->once())
            ->method('update')
            ->with($eloquent, $this->isArray());

        $this->service->markUploadStatus(1, UploadStatus::Ready);
    }

    #[Test]
    public function mark_upload_status_failed(): void
    {
        $entity = VideoEntity::create(
            creatorProfileId: CreatorProfileId::fromInt(1),
            title: 'Processing Video',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
            provider: VideoProvider::Local,
            playbackPolicy: PlaybackPolicy::Public,
        );
        $data = $entity->toArray();
        $data['id'] = 1;
        $data['upload_status'] = 'processing';

        $eloquent = $this->createMockEloquentVideo($data);

        $this->videoRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($eloquent);

        $this->videoRepo
            ->expects($this->once())
            ->method('update')
            ->with($eloquent, $this->isArray());

        $this->service->markUploadStatus(1, UploadStatus::Failed);
    }

    #[Test]
    public function mark_upload_status_pending(): void
    {
        $entity = VideoEntity::create(
            creatorProfileId: CreatorProfileId::fromInt(1),
            title: 'Failed Video',
            contentType: ContentType::Lesson,
            accessLevel: AccessLevel::Free,
            provider: VideoProvider::Local,
            playbackPolicy: PlaybackPolicy::Public,
        );
        $data = $entity->toArray();
        $data['id'] = 1;
        $data['upload_status'] = 'failed';

        $eloquent = $this->createMockEloquentVideo($data);

        $this->videoRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($eloquent);

        $this->videoRepo
            ->expects($this->once())
            ->method('update')
            ->with($eloquent, ['upload_status' => 'pending']);

        $this->service->markUploadStatus(1, UploadStatus::Pending);
    }

    #[Test]
    public function find_or_fail_throws_when_video_not_found(): void
    {
        $this->videoRepo
            ->expects($this->once())
            ->method('findById')
            ->with(404)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Video not found: 404');

        $this->service->publishVideo(404);
    }
}
