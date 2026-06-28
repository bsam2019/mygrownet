<?php

namespace App\Domain\Wedding\Entities;

use DateTimeImmutable;

class WeddingRsvp
{
    private function __construct(
        private ?int $id,
        private int $weddingEventId,
        private string $firstName,
        private string $lastName,
        private string $email,
        private ?string $phone,
        private bool $attending,
        private int $guestCount,
        private ?string $dietaryRestrictions,
        private ?string $message,
        private DateTimeImmutable $submittedAt
    ) {}

    public static function create(
        int $weddingEventId,
        string $firstName,
        string $lastName,
        string $email,
        ?string $phone,
        bool $attending,
        int $guestCount,
        ?string $dietaryRestrictions,
        ?string $message
    ): self {
        return new self(
            id: null,
            weddingEventId: $weddingEventId,
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            phone: $phone,
            attending: $attending,
            guestCount: $guestCount,
            dietaryRestrictions: $dietaryRestrictions,
            message: $message,
            submittedAt: new DateTimeImmutable()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            weddingEventId: $data['wedding_event_id'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            attending: (bool) $data['attending'],
            guestCount: (int) ($data['guest_count'] ?? 0),
            dietaryRestrictions: $data['dietary_restrictions'] ?? null,
            message: $data['message'] ?? null,
            submittedAt: isset($data['submitted_at']) 
                ? new DateTimeImmutable($data['submitted_at']) 
                : new DateTimeImmutable()
        );
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getWeddingEventId(): int { return $this->weddingEventId; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getFullName(): string { return $this->firstName . ' ' . $this->lastName; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function isAttending(): bool { return $this->attending; }
    public function getGuestCount(): int { return $this->guestCount; }
    public function getDietaryRestrictions(): ?string { return $this->dietaryRestrictions; }
    public function getMessage(): ?string { return $this->message; }
    public function getSubmittedAt(): DateTimeImmutable { return $this->submittedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'wedding_event_id' => $this->weddingEventId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'full_name' => $this->getFullName(),
            'email' => $this->email,
            'phone' => $this->phone,
            'attending' => $this->attending,
            'guest_count' => $this->guestCount,
            'dietary_restrictions' => $this->dietaryRestrictions,
            'message' => $this->message,
            'submitted_at' => $this->submittedAt->format('Y-m-d H:i:s'),
        ];
    }
}
