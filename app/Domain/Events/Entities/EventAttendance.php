<?php

namespace App\Domain\Events\Entities;

use App\Domain\Events\ValueObjects\EventId;
use DateTimeImmutable;

class EventAttendance
{
    private function __construct(
        private int $userId,
        private EventId $eventId,
        private DateTimeImmutable $checkedInAt,
        private ?DateTimeImmutable $checkedOutAt = null,
        private ?int $attendanceMinutes = null,
        private string $checkInMethod = 'manual'
    ) {}

    public static function checkIn(
        int $userId,
        EventId $eventId,
        string $method = 'manual'
    ): self {
        return new self(
            $userId,
            $eventId,
            new DateTimeImmutable(),
            null,
            null,
            $method
        );
    }

    public function checkOut(): void
    {
        $this->checkedOutAt = new DateTimeImmutable();
        $this->attendanceMinutes = (int) (($this->checkedOutAt->getTimestamp() - $this->checkedInAt->getTimestamp()) / 60);
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getEventId(): EventId
    {
        return $this->eventId;
    }

    public function getCheckedInAt(): DateTimeImmutable
    {
        return $this->checkedInAt;
    }

    public function getAttendanceMinutes(): ?int
    {
        return $this->attendanceMinutes;
    }

    public function hasCheckedOut(): bool
    {
        return $this->checkedOutAt !== null;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'live_event_id' => $this->eventId->value(),
            'checked_in_at' => $this->checkedInAt->format('Y-m-d H:i:s'),
            'checked_out_at' => $this->checkedOutAt?->format('Y-m-d H:i:s'),
            'attendance_minutes' => $this->attendanceMinutes,
            'check_in_method' => $this->checkInMethod,
        ];
    }
}
