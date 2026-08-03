<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Entities;

final class AttributionLink
{
    private function __construct(
        private ?int $id,
        private int $creatorId,
        private ?string $source,
        private string $visitorSessionId,
        private ?int $convertedUserId,
        private int $watchMinutesAttributed,
        private ?\DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        int $creatorId,
        string $visitorSessionId,
        ?string $source = null,
    ): self {
        return new self(
            id: null,
            creatorId: $creatorId,
            source: $source,
            visitorSessionId: $visitorSessionId,
            convertedUserId: null,
            watchMinutesAttributed: 0,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            creatorId: (int) $data['creator_id'],
            source: $data['source'] ?? null,
            visitorSessionId: (string) $data['visitor_session_id'],
            convertedUserId: isset($data['converted_user_id']) ? (int) $data['converted_user_id'] : null,
            watchMinutesAttributed: (int) ($data['watch_minutes_attributed'] ?? 0),
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function recordConversion(int $userId): void
    {
        $this->convertedUserId = $userId;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function accumulateWatchMinutes(int $minutes): void
    {
        if ($minutes < 0) {
            throw new \InvalidArgumentException('Watch minutes cannot be negative');
        }

        $this->watchMinutesAttributed += $minutes;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function creatorId(): int
    {
        return $this->creatorId;
    }

    public function source(): ?string
    {
        return $this->source;
    }

    public function visitorSessionId(): string
    {
        return $this->visitorSessionId;
    }

    public function convertedUserId(): ?int
    {
        return $this->convertedUserId;
    }

    public function watchMinutesAttributed(): int
    {
        return $this->watchMinutesAttributed;
    }

    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'creator_id' => $this->creatorId,
            'source' => $this->source,
            'visitor_session_id' => $this->visitorSessionId,
            'converted_user_id' => $this->convertedUserId,
            'watch_minutes_attributed' => $this->watchMinutesAttributed,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
