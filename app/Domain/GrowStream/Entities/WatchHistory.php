<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Entities;

use App\Domain\GrowStream\ValueObjects\DeviceType;
use App\Domain\GrowStream\ValueObjects\VideoId;

final class WatchHistory
{
    private function __construct(
        private ?int $id,
        private int $userId,
        private VideoId $videoId,
        private int $watchedSeconds,
        private bool $completed,
        private \DateTimeImmutable $watchedAt,
        private ?DeviceType $deviceType,
        private ?\DateTimeImmutable $createdAt,
    ) {}

    public static function create(
        int $userId,
        VideoId $videoId,
        int $watchedSeconds = 0,
        ?DeviceType $deviceType = null,
    ): self {
        return new self(
            id: null,
            userId: $userId,
            videoId: $videoId,
            watchedSeconds: $watchedSeconds,
            completed: false,
            watchedAt: new \DateTimeImmutable(),
            deviceType: $deviceType,
            createdAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            userId: (int) $data['user_id'],
            videoId: VideoId::fromInt((int) $data['video_id']),
            watchedSeconds: (int) ($data['watched_seconds'] ?? 0),
            completed: (bool) ($data['completed'] ?? false),
            watchedAt: new \DateTimeImmutable($data['watched_at']),
            deviceType: isset($data['device_type']) ? DeviceType::fromString($data['device_type']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
        );
    }

    public function resume(int $additionalSeconds, int $videoDuration): void
    {
        if ($additionalSeconds < 0) {
            throw new \InvalidArgumentException('Additional seconds cannot be negative');
        }

        $this->watchedSeconds += $additionalSeconds;

        if ($videoDuration > 0 && $this->watchedSeconds >= $videoDuration) {
            $this->completed = true;
        }
    }

    public function markCompleted(): void
    {
        $this->completed = true;
    }

    public function getProgressPercent(int $videoDuration): float
    {
        if ($videoDuration <= 0) {
            return 0.0;
        }

        return min(100.0, round(($this->watchedSeconds / $videoDuration) * 100, 2));
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function videoId(): VideoId
    {
        return $this->videoId;
    }

    public function watchedSeconds(): int
    {
        return $this->watchedSeconds;
    }

    public function completed(): bool
    {
        return $this->completed;
    }

    public function watchedAt(): \DateTimeImmutable
    {
        return $this->watchedAt;
    }

    public function deviceType(): ?DeviceType
    {
        return $this->deviceType;
    }

    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'video_id' => $this->videoId->toInt(),
            'watched_seconds' => $this->watchedSeconds,
            'completed' => $this->completed,
            'watched_at' => $this->watchedAt->format('Y-m-d H:i:s'),
            'device_type' => $this->deviceType?->value,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}
