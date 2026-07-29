<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Entities;

use App\Domain\GrowStream\ValueObjects\AccessLevel;
use App\Domain\GrowStream\ValueObjects\CreatorProfileId;
use App\Domain\GrowStream\ValueObjects\SeriesType;
use App\Domain\GrowStream\ValueObjects\SkillLevel;
use App\Domain\GrowStream\ValueObjects\StarterKitTier;

final class VideoSeries
{
    private function __construct(
        private ?int $id,
        private CreatorProfileId $creatorProfileId,
        private string $title,
        private ?string $description,
        private SeriesType $seriesType,
        private ?string $thumbnailUrl,
        private ?string $coverUrl,
        private AccessLevel $accessLevel,
        private ?SkillLevel $skillLevel,
        private ?StarterKitTier $starterKitTier,
        private int $episodeCount,
        private int $totalDurationSeconds,
        private bool $isPublished,
        private ?\DateTimeImmutable $publishedAt,
        private ?\DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        CreatorProfileId $creatorProfileId,
        string $title,
        SeriesType $seriesType,
        AccessLevel $accessLevel = AccessLevel::Free,
        ?string $description = null,
        ?SkillLevel $skillLevel = null,
        ?StarterKitTier $starterKitTier = null,
    ): self {
        return new self(
            id: null,
            creatorProfileId: $creatorProfileId,
            title: $title,
            description: $description,
            seriesType: $seriesType,
            thumbnailUrl: null,
            coverUrl: null,
            accessLevel: $accessLevel,
            skillLevel: $skillLevel,
            starterKitTier: $starterKitTier,
            episodeCount: 0,
            totalDurationSeconds: 0,
            isPublished: false,
            publishedAt: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            creatorProfileId: CreatorProfileId::fromInt($data['creator_profile_id']),
            title: $data['title'],
            description: $data['description'] ?? null,
            seriesType: SeriesType::fromString($data['series_type']),
            thumbnailUrl: $data['thumbnail_url'] ?? null,
            coverUrl: $data['cover_url'] ?? null,
            accessLevel: AccessLevel::fromString($data['access_level']),
            skillLevel: isset($data['skill_level']) ? SkillLevel::fromString($data['skill_level']) : null,
            starterKitTier: isset($data['starter_kit_tier']) ? StarterKitTier::fromString($data['starter_kit_tier']) : null,
            episodeCount: (int) ($data['episode_count'] ?? 0),
            totalDurationSeconds: (int) ($data['total_duration_seconds'] ?? 0),
            isPublished: (bool) ($data['is_published'] ?? false),
            publishedAt: isset($data['published_at']) ? new \DateTimeImmutable($data['published_at']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function publish(): void
    {
        $this->isPublished = true;
        $this->publishedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function unpublish(): void
    {
        $this->isPublished = false;
        $this->publishedAt = null;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setEpisodeCount(int $count): void
    {
        if ($count < 0) {
            throw new \InvalidArgumentException('Episode count cannot be negative');
        }
        $this->episodeCount = $count;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setTotalDuration(int $seconds): void
    {
        if ($seconds < 0) {
            throw new \InvalidArgumentException('Total duration cannot be negative');
        }
        $this->totalDurationSeconds = $seconds;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateThumbnail(string $url): void
    {
        $this->thumbnailUrl = $url;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateCover(string $url): void
    {
        $this->coverUrl = $url;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateDescription(string $description): void
    {
        $this->description = $description;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function canBeAccessedBy(string $userAccessLevel): bool
    {
        $levels = ['free' => 0, 'basic' => 1, 'premium' => 2, 'institutional' => 3];
        $required = $levels[$this->accessLevel->value] ?? 0;
        $user = $levels[$userAccessLevel] ?? -1;
        return $user >= $required;
    }

    public function id(): ?int
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

    public function seriesType(): SeriesType
    {
        return $this->seriesType;
    }

    public function thumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }

    public function coverUrl(): ?string
    {
        return $this->coverUrl;
    }

    public function accessLevel(): AccessLevel
    {
        return $this->accessLevel;
    }

    public function skillLevel(): ?SkillLevel
    {
        return $this->skillLevel;
    }

    public function starterKitTier(): ?StarterKitTier
    {
        return $this->starterKitTier;
    }

    public function episodeCount(): int
    {
        return $this->episodeCount;
    }

    public function totalDurationSeconds(): int
    {
        return $this->totalDurationSeconds;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function publishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
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
            'id' => $this->id,
            'creator_profile_id' => $this->creatorProfileId->toInt(),
            'title' => $this->title,
            'description' => $this->description,
            'series_type' => $this->seriesType->value,
            'thumbnail_url' => $this->thumbnailUrl,
            'cover_url' => $this->coverUrl,
            'access_level' => $this->accessLevel->value,
            'skill_level' => $this->skillLevel?->value,
            'starter_kit_tier' => $this->starterKitTier?->value,
            'episode_count' => $this->episodeCount,
            'total_duration_seconds' => $this->totalDurationSeconds,
            'is_published' => $this->isPublished,
            'published_at' => $this->publishedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
