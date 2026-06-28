<?php

namespace App\Domain\Events\Entities;

use App\Domain\Events\ValueObjects\EventId;
use App\Domain\Events\ValueObjects\EventType;
use DateTimeImmutable;

class LiveEvent
{
    private function __construct(
        private EventId $id,
        private string $title,
        private string $slug,
        private ?string $description,
        private EventType $eventType,
        private DateTimeImmutable $scheduledAt,
        private int $durationMinutes,
        private ?string $meetingLink,
        private ?int $maxAttendees,
        private bool $isPublished,
        private bool $requiresRegistration
    ) {}

    public static function create(
        string $title,
        string $slug,
        DateTimeImmutable $scheduledAt,
        int $durationMinutes = 60,
        string $eventType = 'webinar'
    ): self {
        return new self(
            EventId::generate(),
            $title,
            $slug,
            null,
            EventType::fromString($eventType),
            $scheduledAt,
            $durationMinutes,
            null,
            null,
            true,
            true
        );
    }

    public function getId(): EventId
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getScheduledAt(): DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function getDurationMinutes(): int
    {
        return $this->durationMinutes;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setMeetingLink(string $link): void
    {
        $this->meetingLink = $link;
    }

    public function publish(): void
    {
        $this->isPublished = true;
    }

    public function unpublish(): void
    {
        $this->isPublished = false;
    }

    public function isUpcoming(): bool
    {
        return $this->scheduledAt > new DateTimeImmutable();
    }

    public function isHappeningNow(): bool
    {
        $now = new DateTimeImmutable();
        $endTime = $this->scheduledAt->modify("+{$this->durationMinutes} minutes");
        
        return $now >= $this->scheduledAt && $now <= $endTime;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'event_type' => $this->eventType->value(),
            'scheduled_at' => $this->scheduledAt->format('Y-m-d H:i:s'),
            'duration_minutes' => $this->durationMinutes,
            'meeting_link' => $this->meetingLink,
            'max_attendees' => $this->maxAttendees,
            'is_published' => $this->isPublished,
            'requires_registration' => $this->requiresRegistration,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            EventId::fromInt($data['id']),
            $data['title'],
            $data['slug'],
            $data['description'] ?? null,
            EventType::fromString($data['event_type']),
            new DateTimeImmutable($data['scheduled_at']),
            $data['duration_minutes'],
            $data['meeting_link'] ?? null,
            $data['max_attendees'] ?? null,
            $data['is_published'],
            $data['requires_registration']
        );
    }
}
