<?php

namespace App\Domain\Wedding\Entities;

use DateTimeImmutable;

class WeddingGuest
{
    private function __construct(
        private ?int $id,
        private int $weddingEventId,
        private string $firstName,
        private string $lastName,
        private ?string $email,
        private ?string $phone,
        private int $allowedGuests,
        private ?string $groupName,
        private ?string $notes,
        private bool $invitationSent,
        private string $rsvpStatus,
        private int $confirmedGuests,
        private ?string $dietaryRestrictions,
        private ?string $rsvpMessage,
        private ?DateTimeImmutable $rsvpSubmittedAt
    ) {}

    public static function create(
        int $weddingEventId,
        string $firstName,
        string $lastName,
        ?string $email = null,
        ?string $phone = null,
        int $allowedGuests = 1,
        ?string $groupName = null,
        ?string $notes = null
    ): self {
        return new self(
            id: null,
            weddingEventId: $weddingEventId,
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            phone: $phone,
            allowedGuests: $allowedGuests,
            groupName: $groupName,
            notes: $notes,
            invitationSent: false,
            rsvpStatus: 'pending',
            confirmedGuests: 0,
            dietaryRestrictions: null,
            rsvpMessage: null,
            rsvpSubmittedAt: null
        );
    }

    public static function fromArray(array $data): self
    {
        $rsvpSubmittedAt = null;
        if (!empty($data['rsvp_submitted_at'])) {
            $rsvpSubmittedAt = $data['rsvp_submitted_at'] instanceof DateTimeImmutable
                ? $data['rsvp_submitted_at']
                : new DateTimeImmutable($data['rsvp_submitted_at']);
        }

        return new self(
            id: $data['id'] ?? null,
            weddingEventId: $data['wedding_event_id'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            allowedGuests: (int) ($data['allowed_guests'] ?? 1),
            groupName: $data['group_name'] ?? null,
            notes: $data['notes'] ?? null,
            invitationSent: (bool) ($data['invitation_sent'] ?? false),
            rsvpStatus: $data['rsvp_status'] ?? 'pending',
            confirmedGuests: (int) ($data['confirmed_guests'] ?? 0),
            dietaryRestrictions: $data['dietary_restrictions'] ?? null,
            rsvpMessage: $data['rsvp_message'] ?? null,
            rsvpSubmittedAt: $rsvpSubmittedAt
        );
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getWeddingEventId(): int { return $this->weddingEventId; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getFullName(): string { return $this->firstName . ' ' . $this->lastName; }
    public function getEmail(): ?string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getAllowedGuests(): int { return $this->allowedGuests; }
    public function getGroupName(): ?string { return $this->groupName; }
    public function getNotes(): ?string { return $this->notes; }
    public function isInvitationSent(): bool { return $this->invitationSent; }
    public function getRsvpStatus(): string { return $this->rsvpStatus; }
    public function getConfirmedGuests(): int { return $this->confirmedGuests; }
    public function getDietaryRestrictions(): ?string { return $this->dietaryRestrictions; }
    public function getRsvpMessage(): ?string { return $this->rsvpMessage; }
    public function getRsvpSubmittedAt(): ?DateTimeImmutable { return $this->rsvpSubmittedAt; }
    
    public function isAttending(): bool { return $this->rsvpStatus === 'attending'; }
    public function hasResponded(): bool { return $this->rsvpStatus !== 'pending'; }

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
            'allowed_guests' => $this->allowedGuests,
            'group_name' => $this->groupName,
            'notes' => $this->notes,
            'invitation_sent' => $this->invitationSent,
            'rsvp_status' => $this->rsvpStatus,
            'confirmed_guests' => $this->confirmedGuests,
            'dietary_restrictions' => $this->dietaryRestrictions,
            'rsvp_message' => $this->rsvpMessage,
            'rsvp_submitted_at' => $this->rsvpSubmittedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
