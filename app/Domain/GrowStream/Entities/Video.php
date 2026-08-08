<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Entities;

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

final class Video
{
    private function __construct(
        private ?VideoId $id,
        private CreatorProfileId $creatorProfileId,
        private string $title,
        private ?string $description,
        private ContentType $contentType,
        private ?int $durationSeconds,
        private AccessLevel $accessLevel,
        private ?ContentRating $contentRating,
        private ?SkillLevel $skillLevel,
        private UploadStatus $uploadStatus,
        private ?string $videoUrl,
        private ?string $thumbnailUrl,
        private VideoProvider $provider,
        private PlaybackPolicy $playbackPolicy,
        private ?SeriesType $seriesType,
        private ?int $seriesId,
        private ?int $seasonNumber,
        private ?int $episodeNumber,
        private array $tags,
        private array $metadata,
        private bool $isPublished,
        private ?\DateTimeImmutable $publishedAt,
        private int $viewsCount,
        private ?StarterKitTier $starterKitTier,
        private ?\DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        CreatorProfileId $creatorProfileId,
        string $title,
        ContentType $contentType,
        AccessLevel $accessLevel,
        ?int $durationSeconds = null,
        ?string $description = null,
        ?ContentRating $contentRating = null,
        ?SkillLevel $skillLevel = null,
        VideoProvider $provider = VideoProvider::Cloudflare,
        PlaybackPolicy $playbackPolicy = PlaybackPolicy::Public,
        ?SeriesType $seriesType = null,
        ?int $seriesId = null,
        ?int $seasonNumber = null,
        ?int $episodeNumber = null,
        ?StarterKitTier $starterKitTier = null,
    ): self {
        return new self(
            id: null,
            creatorProfileId: $creatorProfileId,
            title: $title,
            description: $description,
            contentType: $contentType,
            durationSeconds: $durationSeconds,
            accessLevel: $accessLevel,
            contentRating: $contentRating,
            skillLevel: $skillLevel,
            uploadStatus: UploadStatus::Pending,
            videoUrl: null,
            thumbnailUrl: null,
            provider: $provider,
            playbackPolicy: $playbackPolicy,
            seriesType: $seriesType,
            seriesId: $seriesId,
            seasonNumber: $seasonNumber,
            episodeNumber: $episodeNumber,
            tags: [],
            metadata: [],
            isPublished: false,
            publishedAt: null,
            viewsCount: 0,
            starterKitTier: $starterKitTier,
            createdAt: new \DateTimeImmutable,
            updatedAt: new \DateTimeImmutable,
        );
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? VideoId::fromInt($data['id']) : null,
            creatorProfileId: CreatorProfileId::fromInt($data['creator_profile_id'] ?? $data['creator_id']),
            title: $data['title'],
            description: $data['description'] ?? null,
            contentType: ContentType::fromString($data['content_type']),
            durationSeconds: $data['duration_seconds'] ?? $data['duration'] ?? null,
            accessLevel: AccessLevel::fromString($data['access_level']),
            contentRating: isset($data['content_rating']) ? ContentRating::fromString($data['content_rating']) : null,
            skillLevel: isset($data['skill_level']) ? SkillLevel::fromString($data['skill_level']) : null,
            uploadStatus: UploadStatus::fromString($data['upload_status']),
            videoUrl: $data['video_url'] ?? null,
            thumbnailUrl: $data['thumbnail_url'] ?? null,
            provider: VideoProvider::fromString($data['provider'] ?? $data['video_provider']),
            playbackPolicy: PlaybackPolicy::fromString($data['playback_policy']),
            seriesType: isset($data['series_type']) ? SeriesType::fromString($data['series_type']) : null,
            seriesId: $data['series_id'] ?? null,
            seasonNumber: $data['season_number'] ?? null,
            episodeNumber: $data['episode_number'] ?? null,
            tags: isset($data['tags']) ? (is_string($data['tags']) ? json_decode($data['tags'], true) : $data['tags']) : [],
            metadata: isset($data['metadata']) ? (is_string($data['metadata']) ? json_decode($data['metadata'], true) : $data['metadata']) : [],
            isPublished: (bool) ($data['is_published'] ?? false),
            publishedAt: isset($data['published_at']) ? new \DateTimeImmutable($data['published_at']) : null,
            viewsCount: (int) ($data['views_count'] ?? 0),
            starterKitTier: isset($data['starter_kit_tier']) ? StarterKitTier::fromString($data['starter_kit_tier']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function publish(): void
    {
        if ($this->uploadStatus !== UploadStatus::Ready) {
            throw new \RuntimeException('Cannot publish video that is not ready');
        }
        $this->isPublished = true;
        $this->publishedAt = new \DateTimeImmutable;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function unpublish(): void
    {
        $this->isPublished = false;
        $this->publishedAt = null;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function markReady(): void
    {
        if ($this->uploadStatus === UploadStatus::Failed) {
            throw new \RuntimeException('Cannot mark failed video as ready');
        }
        $this->uploadStatus = UploadStatus::Ready;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function markFailed(): void
    {
        $this->uploadStatus = UploadStatus::Failed;
        $this->isPublished = false;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function incrementViews(): void
    {
        $this->viewsCount++;
    }

    public function updateThumbnail(string $url): void
    {
        $this->thumbnailUrl = $url;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function updateVideoUrl(string $url): void
    {
        $this->videoUrl = $url;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function updateDuration(int $seconds): void
    {
        if ($seconds < 0) {
            throw new \InvalidArgumentException('Duration cannot be negative');
        }
        $this->durationSeconds = $seconds;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function updateMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function addTag(string $tag): void
    {
        if (! in_array($tag, $this->tags, true)) {
            $this->tags[] = $tag;
        }
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function removeTag(string $tag): void
    {
        $this->tags = array_values(array_filter($this->tags, fn (string $t) => $t !== $tag));
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function assignToSeries(int $seriesId, ?int $season = null, ?int $episode = null): void
    {
        $this->seriesId = $seriesId;
        $this->seasonNumber = $season;
        $this->episodeNumber = $episode;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function canBeAccessedBy(string $userAccessLevel): bool
    {
        $levels = ['free' => 0, 'basic' => 1, 'premium' => 2, 'institutional' => 3];
        $required = $levels[$this->accessLevel->value] ?? 0;
        $user = $levels[$userAccessLevel] ?? -1;

        return $user >= $required;
    }

    public function id(): ?VideoId
    {
        return $this->id;
    }

    public function creatorProfileId(): CreatorProfileId
    {
        return $this->creatorProfileId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function contentType(): ContentType
    {
        return $this->contentType;
    }

    public function durationSeconds(): ?int
    {
        return $this->durationSeconds;
    }

    public function accessLevel(): AccessLevel
    {
        return $this->accessLevel;
    }

    public function contentRating(): ?ContentRating
    {
        return $this->contentRating;
    }

    public function skillLevel(): ?SkillLevel
    {
        return $this->skillLevel;
    }

    public function uploadStatus(): UploadStatus
    {
        return $this->uploadStatus;
    }

    public function videoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function thumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }

    public function provider(): VideoProvider
    {
        return $this->provider;
    }

    public function playbackPolicy(): PlaybackPolicy
    {
        return $this->playbackPolicy;
    }

    public function seriesType(): ?SeriesType
    {
        return $this->seriesType;
    }

    public function seriesId(): ?int
    {
        return $this->seriesId;
    }

    public function seasonNumber(): ?int
    {
        return $this->seasonNumber;
    }

    public function episodeNumber(): ?int
    {
        return $this->episodeNumber;
    }

    public function tags(): array
    {
        return $this->tags;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function publishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function viewsCount(): int
    {
        return $this->viewsCount;
    }

    public function starterKitTier(): ?StarterKitTier
    {
        return $this->starterKitTier;
    }

    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id?->toInt(),
            'creator_profile_id' => $this->creatorProfileId->toInt(),
            'title' => $this->title,
            'description' => $this->description,
            'content_type' => $this->contentType->value,
            'duration_seconds' => $this->durationSeconds,
            'access_level' => $this->accessLevel->value,
            'content_rating' => $this->contentRating?->value,
            'skill_level' => $this->skillLevel?->value,
            'upload_status' => $this->uploadStatus->value,
            'video_url' => $this->videoUrl,
            'thumbnail_url' => $this->thumbnailUrl,
            'provider' => $this->provider->value,
            'playback_policy' => $this->playbackPolicy->value,
            'series_type' => $this->seriesType?->value,
            'series_id' => $this->seriesId,
            'season_number' => $this->seasonNumber,
            'episode_number' => $this->episodeNumber,
            'tags' => $this->tags,
            'metadata' => $this->metadata,
            'is_published' => $this->isPublished,
            'published_at' => $this->publishedAt?->format('Y-m-d H:i:s'),
            'views_count' => $this->viewsCount,
            'starter_kit_tier' => $this->starterKitTier?->value,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
