<?php

namespace App\Domain\Workshop\Entities;

use DateTimeImmutable;

class WorkshopRegistration
{
    public function __construct(
        private ?int $id,
        private int $workshopId,
        private int $userId,
        private ?int $paymentId,
        private string $status,
        private ?string $registrationNotes,
        private ?DateTimeImmutable $attendedAt,
        private ?DateTimeImmutable $completedAt,
        private bool $certificateIssued,
        private ?DateTimeImmutable $certificateIssuedAt,
        private bool $pointsAwarded,
        private ?DateTimeImmutable $pointsAwardedAt,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $workshopId,
        int $userId,
        ?string $registrationNotes = null
    ): self {
        return new self(
            id: null,
            workshopId: $workshopId,
            userId: $userId,
            paymentId: null,
            status: 'pending_payment',
            registrationNotes: $registrationNotes,
            attendedAt: null,
            completedAt: null,
            certificateIssued: false,
            certificateIssuedAt: null,
            pointsAwarded: false,
            pointsAwardedAt: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public function confirmPayment(int $paymentId): void
    {
        if ($this->status !== 'pending_payment') {
            throw new \DomainException('Can only confirm payment for pending registrations');
        }
        $this->paymentId = $paymentId;
        $this->status = 'registered';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAttended(): void
    {
        if ($this->status !== 'registered') {
            throw new \DomainException('Only registered members can be marked as attended');
        }
        $this->status = 'attended';
        $this->attendedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markCompleted(): void
    {
        if (!in_array($this->status, ['registered', 'attended'])) {
            throw new \DomainException('Only registered or attended members can complete workshop');
        }
        $this->status = 'completed';
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function issueCertificate(): void
    {
        if ($this->status !== 'completed') {
            throw new \DomainException('Certificate can only be issued for completed workshops');
        }
        if ($this->certificateIssued) {
            throw new \DomainException('Certificate already issued');
        }
        $this->certificateIssued = true;
        $this->certificateIssuedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function awardPoints(): void
    {
        if ($this->status !== 'completed') {
            throw new \DomainException('Points can only be awarded for completed workshops');
        }
        if ($this->pointsAwarded) {
            throw new \DomainException('Points already awarded');
        }
        $this->pointsAwarded = true;
        $this->pointsAwardedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        if (in_array($this->status, ['completed', 'cancelled'])) {
            throw new \DomainException('Cannot cancel completed or already cancelled registration');
        }
        $this->status = 'cancelled';
        $this->updatedAt = new DateTimeImmutable();
    }

    // Getters
    public function id(): ?int { return $this->id; }
    public function workshopId(): int { return $this->workshopId; }
    public function userId(): int { return $this->userId; }
    public function paymentId(): ?int { return $this->paymentId; }
    public function status(): string { return $this->status; }
    public function registrationNotes(): ?string { return $this->registrationNotes; }
    public function attendedAt(): ?DateTimeImmutable { return $this->attendedAt; }
    public function completedAt(): ?DateTimeImmutable { return $this->completedAt; }
    public function certificateIssued(): bool { return $this->certificateIssued; }
    public function certificateIssuedAt(): ?DateTimeImmutable { return $this->certificateIssuedAt; }
    public function pointsAwarded(): bool { return $this->pointsAwarded; }
    public function pointsAwardedAt(): ?DateTimeImmutable { return $this->pointsAwardedAt; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
