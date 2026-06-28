<?php

namespace App\Domain\Wedding\Entities;

use App\Domain\Wedding\ValueObjects\WeddingBudget;
use App\Domain\Wedding\ValueObjects\WeddingStatus;
use Carbon\Carbon;

class WeddingEvent
{
    public function __construct(
        private ?int $id,
        private int $userId,
        private string $partnerName,
        private ?string $partnerEmail,
        private ?string $partnerPhone,
        private Carbon $weddingDate,
        private ?string $venueName,
        private ?string $venueLocation,
        private WeddingBudget $budget,
        private int $guestCount,
        private WeddingStatus $status,
        private ?string $notes,
        private ?array $preferences,
        private ?Carbon $createdAt = null,
        private ?Carbon $updatedAt = null
    ) {}

    public static function create(
        int $userId,
        string $partnerName,
        Carbon $weddingDate,
        WeddingBudget $budget,
        int $guestCount = 0
    ): self {
        return new self(
            id: null,
            userId: $userId,
            partnerName: $partnerName,
            partnerEmail: null,
            partnerPhone: null,
            weddingDate: $weddingDate,
            venueName: null,
            venueLocation: null,
            budget: $budget,
            guestCount: $guestCount,
            status: WeddingStatus::planning(),
            notes: null,
            preferences: null,
            createdAt: Carbon::now(),
            updatedAt: Carbon::now()
        );
    }

    public function updateVenue(string $venueName, string $venueLocation): void
    {
        $this->venueName = $venueName;
        $this->venueLocation = $venueLocation;
        $this->updatedAt = Carbon::now();
    }

    public function updateBudget(WeddingBudget $budget): void
    {
        $this->budget = $budget;
        $this->updatedAt = Carbon::now();
    }

    public function updateGuestCount(int $guestCount): void
    {
        if ($guestCount < 0) {
            throw new \InvalidArgumentException('Guest count cannot be negative');
        }
        
        $this->guestCount = $guestCount;
        $this->updatedAt = Carbon::now();
    }

    public function confirm(): void
    {
        if (!$this->canBeConfirmed()) {
            throw new \DomainException('Wedding cannot be confirmed without venue and date');
        }
        
        $this->status = WeddingStatus::confirmed();
        $this->updatedAt = Carbon::now();
    }

    public function complete(): void
    {
        if (!$this->status->isConfirmed()) {
            throw new \DomainException('Only confirmed weddings can be completed');
        }
        
        $this->status = WeddingStatus::completed();
        $this->updatedAt = Carbon::now();
    }

    public function cancel(): void
    {
        if ($this->status->isCompleted()) {
            throw new \DomainException('Completed weddings cannot be cancelled');
        }
        
        $this->status = WeddingStatus::cancelled();
        $this->updatedAt = Carbon::now();
    }

    public function isUpcoming(): bool
    {
        return $this->weddingDate->isFuture() && 
               ($this->status->isPlanning() || $this->status->isConfirmed());
    }

    public function daysUntilWedding(): int
    {
        return Carbon::now()->diffInDays($this->weddingDate, false);
    }

    private function canBeConfirmed(): bool
    {
        return !empty($this->venueName) && 
               !empty($this->venueLocation) && 
               $this->weddingDate->isFuture();
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getPartnerName(): string { return $this->partnerName; }
    public function getPartnerEmail(): ?string { return $this->partnerEmail; }
    public function getPartnerPhone(): ?string { return $this->partnerPhone; }
    public function getWeddingDate(): Carbon { return $this->weddingDate; }
    public function getVenueName(): ?string { return $this->venueName; }
    public function getVenueLocation(): ?string { return $this->venueLocation; }
    public function getBudget(): WeddingBudget { return $this->budget; }
    public function getGuestCount(): int { return $this->guestCount; }
    public function getStatus(): WeddingStatus { return $this->status; }
    public function getNotes(): ?string { return $this->notes; }
    public function getPreferences(): ?array { return $this->preferences; }
    public function getCreatedAt(): ?Carbon { return $this->createdAt; }
    public function getUpdatedAt(): ?Carbon { return $this->updatedAt; }
}