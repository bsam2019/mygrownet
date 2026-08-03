<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Entities\Video as VideoEntity;
use App\Domain\GrowStream\Exceptions\VideoNotFoundException;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoTagRepositoryInterface;
use App\Domain\GrowStream\ValueObjects\AccessLevel;
use App\Domain\GrowStream\ValueObjects\ContentRating;
use App\Domain\GrowStream\ValueObjects\ContentType;
use App\Domain\GrowStream\ValueObjects\CreatorProfileId;
use App\Domain\GrowStream\ValueObjects\PlaybackPolicy;
use App\Domain\GrowStream\ValueObjects\SkillLevel;
use App\Domain\GrowStream\ValueObjects\StarterKitTier;
use App\Domain\GrowStream\ValueObjects\UploadStatus;
use App\Domain\GrowStream\ValueObjects\VideoProvider;

final class VideoManagementService
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly VideoTagRepositoryInterface $tagRepository,
    ) {}

    public function createVideo(
        CreatorProfileId $creatorId,
        string $title,
        ?string $description,
        ContentType $contentType,
        AccessLevel $accessLevel,
        ?ContentRating $contentRating,
        ?SkillLevel $skillLevel,
        VideoProvider $provider,
        PlaybackPolicy $playbackPolicy,
        array $tags = [],
        ?int $seriesId = null,
        ?int $seasonNumber = null,
        ?int $episodeNumber = null,
        ?StarterKitTier $starterKitTier = null,
    ): array {
        $video = VideoEntity::create(
            creatorProfileId: $creatorId,
            title: $title,
            contentType: $contentType,
            accessLevel: $accessLevel,
            description: $description,
            contentRating: $contentRating,
            skillLevel: $skillLevel,
            provider: $provider,
            playbackPolicy: $playbackPolicy,
            seriesId: $seriesId,
            seasonNumber: $seasonNumber,
            episodeNumber: $episodeNumber,
            starterKitTier: $starterKitTier,
        );

        $data = $video->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);

        $saved = $this->videoRepository->save($data);

        $videoId = $saved->id;

        foreach ($tags as $tag) {
            $this->tagRepository->addTag($videoId, $tag);
        }

        $result = $video->toArray();
        $result['id'] = $videoId;

        return ['id' => $videoId, 'video' => $result];
    }

    public function updateVideo(int $videoId, array $data): array
    {
        $eloquent = $this->findOrFail($videoId);

        $updated = $this->videoRepository->update($eloquent, $data);

        return VideoEntity::reconstitute($updated->toArray())->toArray();
    }

    public function publishVideo(int $videoId): void
    {
        $eloquent = $this->findOrFail($videoId);

        $video = VideoEntity::reconstitute($eloquent->toArray());
        $video->publish();

        $this->videoRepository->update($eloquent, $video->toArray());
    }

    public function unpublishVideo(int $videoId): void
    {
        $eloquent = $this->findOrFail($videoId);

        $video = VideoEntity::reconstitute($eloquent->toArray());
        $video->unpublish();

        $this->videoRepository->update($eloquent, $video->toArray());
    }

    public function deleteVideo(int $videoId): void
    {
        $eloquent = $this->findOrFail($videoId);

        $this->videoRepository->delete($eloquent);
    }

    public function bulkAction(array $videoIds, string $action): void
    {
        switch ($action) {
            case 'publish':
                foreach ($videoIds as $id) {
                    $this->publishVideo($id);
                }
                break;
            case 'unpublish':
                $this->videoRepository->updateInBulk($videoIds, [
                    'is_published' => false,
                    'published_at' => null,
                ]);
                break;
            case 'delete':
                $this->videoRepository->bulkDelete($videoIds);
                break;
            default:
                throw new \InvalidArgumentException("Unknown bulk action: {$action}");
        }
    }

    public function syncTags(int $videoId, array $tags): void
    {
        $this->tagRepository->deleteByVideo($videoId);

        foreach ($tags as $tag) {
            $this->tagRepository->addTag($videoId, $tag);
        }
    }

    public function updateThumbnail(int $videoId, string $thumbnailUrl): void
    {
        $eloquent = $this->findOrFail($videoId);

        $video = VideoEntity::reconstitute($eloquent->toArray());
        $video->updateThumbnail($thumbnailUrl);

        $this->videoRepository->update($eloquent, $video->toArray());
    }

    public function updateVideoUrl(int $videoId, string $videoUrl): void
    {
        $eloquent = $this->findOrFail($videoId);

        $video = VideoEntity::reconstitute($eloquent->toArray());
        $video->updateVideoUrl($videoUrl);

        $this->videoRepository->update($eloquent, $video->toArray());
    }

    public function updateDuration(int $videoId, int $seconds): void
    {
        $eloquent = $this->findOrFail($videoId);

        $video = VideoEntity::reconstitute($eloquent->toArray());
        $video->updateDuration($seconds);

        $this->videoRepository->update($eloquent, $video->toArray());
    }

    public function markUploadStatus(int $videoId, UploadStatus $status): void
    {
        $eloquent = $this->findOrFail($videoId);

        if ($status === UploadStatus::Ready || $status === UploadStatus::Failed) {
            $video = VideoEntity::reconstitute($eloquent->toArray());
            match ($status) {
                UploadStatus::Ready => $video->markReady(),
                UploadStatus::Failed => $video->markFailed(),
            };
            $this->videoRepository->update($eloquent, $video->toArray());
        } else {
            $this->videoRepository->update($eloquent, ['upload_status' => $status->value]);
        }
    }

    public function submitForReview(int $videoId): void
    {
        $eloquent = $this->findOrFail($videoId);

        $this->videoRepository->update($eloquent, ['moderation_status' => 'pending_review']);
    }

    public function approveVideoReview(int $videoId, ?int $reviewedBy = null): void
    {
        $eloquent = $this->findOrFail($videoId);

        $this->videoRepository->update($eloquent, [
            'moderation_status' => 'approved',
            'moderation_reason' => null,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewedBy,
        ]);
    }

    public function rejectVideoReview(int $videoId, string $reason, ?int $reviewedBy = null): void
    {
        $eloquent = $this->findOrFail($videoId);

        $this->videoRepository->update($eloquent, [
            'moderation_status' => 'rejected',
            'moderation_reason' => $reason,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewedBy,
        ]);
    }

    private function findOrFail(int $videoId): Video
    {
        $video = $this->videoRepository->findById($videoId);

        if ($video === null) {
            throw VideoNotFoundException::forId($videoId);
        }

        return $video;
    }
}
