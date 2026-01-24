<?php

namespace App\Domain\Ubumi\Entities;

use App\Domain\Ubumi\ValueObjects\CheckInId;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\CheckInStatus;
use DateTimeImmutable;

final class CheckIn
{
    private function __construct(
        private readonly CheckInId $id,
        private readonly PersonId $personId,
        private CheckInStatus $status,
        private ?string $note,
        private ?string $location,
        private ?string $photoUrl,
        private readonly DateTimeImmutable $checkedInAt,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        PersonId $personId,
        CheckInStatus $status,
        ?string $note = null,
        ?string $location = null,
        ?string $photoUrl = null,
        ?DateTimeImmutable $checkedInAt = null
    ): self {
        $now = new DateTimeImmutable();
        
        return new self(
            id: CheckInId::generate(),
            personId: $personId,
            status: $status,
            note: $note,
            location: $location,
            photoUrl: $photoUrl,
            checkedInAt: $checkedInAt ?? $now,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function reconstitute(
        CheckInId $id,
        PersonId $personId,
        CheckInStatus $status,
        ?string $note,
        ?string $location,
        ?string $photoUrl,
        DateTimeImmutable $checkedInAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            personId: $personId,
            status: $status,
            note: $note,
            location: $location,
            photoUrl: $photoUrl,
            checkedInAt: $checkedInAt,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    // Getters
    public function id(): CheckInId
    {
        return $this->id;
    }

    public function personId(): PersonId
    {
        return $this->personId;
    }

    public function status(): CheckInStatus
    {
        return $this->status;
    }

    public function note(): ?string
    {
        return $this->note;
    }

    public function location(): ?string
    {
        return $this->location;
    }

    public function photoUrl(): ?string
    {
        return $this->photoUrl;
    }

    public function checkedInAt(): DateTimeImmutable
    {
        return $this->checkedInAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Business logic
    public function requiresAlert(): bool
    {
        return $this->status->requiresAlert();
    }

    public function isRecent(int $hours = 24): bool
    {
        $now = new DateTimeImmutable();
        $diff = $now->getTimestamp() - $this->checkedInAt->getTimestamp();
        return $diff <= ($hours * 3600);
    }

    public function updateNote(string $note): void
    {
        $this->note = $note;
        $this->updatedAt = new DateTimeImmutable();
    }
}
