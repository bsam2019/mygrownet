<?php

namespace App\Domain\PrimeEdge\Entities;

use App\Domain\PrimeEdge\ValueObjects\AppointmentId;
use App\Domain\PrimeEdge\ValueObjects\AppointmentStatus;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\DateTimeRange;
use DateTimeImmutable;

class Appointment
{
    private function __construct(
        private readonly AppointmentId $id,
        private readonly ClientId $clientId,
        private string $title,
        private ?string $description,
        private DateTimeRange $dateTimeRange,
        private AppointmentStatus $status,
        private ?string $meetingLink,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        AppointmentId $id,
        ClientId $clientId,
        string $title,
        DateTimeRange $dateTimeRange,
        ?string $description = null,
        ?string $meetingLink = null,
    ): self {
        return new self(
            id: $id,
            clientId: $clientId,
            title: $title,
            description: $description,
            dateTimeRange: $dateTimeRange,
            status: AppointmentStatus::SCHEDULED,
            meetingLink: $meetingLink,
            createdAt: new DateTimeImmutable(),
            updatedAt: null,
        );
    }

    public static function reconstitute(
        AppointmentId $id,
        ClientId $clientId,
        string $title,
        ?string $description,
        DateTimeRange $dateTimeRange,
        AppointmentStatus $status,
        ?string $meetingLink,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            clientId: $clientId,
            title: $title,
            description: $description,
            dateTimeRange: $dateTimeRange,
            status: $status,
            meetingLink: $meetingLink,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function confirm(): void
    {
        if (!$this->status->canTransitionTo(AppointmentStatus::CONFIRMED)) {
            throw new \DomainException("Cannot confirm appointment in status {$this->status->value}");
        }
        $this->status = AppointmentStatus::CONFIRMED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function complete(): void
    {
        if (!$this->status->canTransitionTo(AppointmentStatus::COMPLETED)) {
            throw new \DomainException("Cannot complete appointment in status {$this->status->value}");
        }
        $this->status = AppointmentStatus::COMPLETED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        if (!$this->status->canTransitionTo(AppointmentStatus::CANCELLED)) {
            throw new \DomainException("Cannot cancel appointment in status {$this->status->value}");
        }
        $this->status = AppointmentStatus::CANCELLED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markNoShow(): void
    {
        if (!$this->status->canTransitionTo(AppointmentStatus::NO_SHOW)) {
            throw new \DomainException("Cannot mark no-show for appointment in status {$this->status->value}");
        }
        $this->status = AppointmentStatus::NO_SHOW;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reschedule(DateTimeRange $newRange): void
    {
        if ($this->status === AppointmentStatus::COMPLETED) {
            throw new \DomainException('Cannot reschedule a completed appointment');
        }
        $this->dateTimeRange = $newRange;
        $this->status = AppointmentStatus::SCHEDULED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): AppointmentId { return $this->id; }
    public function clientId(): ClientId { return $this->clientId; }
    public function title(): string { return $this->title; }
    public function description(): ?string { return $this->description; }
    public function dateTimeRange(): DateTimeRange { return $this->dateTimeRange; }
    public function status(): AppointmentStatus { return $this->status; }
    public function meetingLink(): ?string { return $this->meetingLink; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): ?DateTimeImmutable { return $this->updatedAt; }
}
