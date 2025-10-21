<?php

namespace App\Domain\Workshop\Entities;

use App\Domain\Workshop\ValueObjects\WorkshopCategory;
use App\Domain\Workshop\ValueObjects\DeliveryFormat;
use DateTimeImmutable;

class Workshop
{
    public function __construct(
        private ?int $id,
        private string $title,
        private string $slug,
        private string $description,
        private WorkshopCategory $category,
        private DeliveryFormat $deliveryFormat,
        private float $price,
        private ?int $maxParticipants,
        private int $lpReward,
        private int $bpReward,
        private DateTimeImmutable $startDate,
        private DateTimeImmutable $endDate,
        private ?string $location,
        private ?string $meetingLink,
        private ?string $requirements,
        private ?string $learningOutcomes,
        private ?string $instructorName,
        private ?string $instructorBio,
        private ?string $featuredImage,
        private string $status,
        private int $createdBy,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        string $title,
        string $slug,
        string $description,
        WorkshopCategory $category,
        DeliveryFormat $deliveryFormat,
        float $price,
        ?int $maxParticipants,
        int $lpReward,
        int $bpReward,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        ?string $location,
        ?string $meetingLink,
        int $createdBy
    ): self {
        return new self(
            id: null,
            title: $title,
            slug: $slug,
            description: $description,
            category: $category,
            deliveryFormat: $deliveryFormat,
            price: $price,
            maxParticipants: $maxParticipants,
            lpReward: $lpReward,
            bpReward: $bpReward,
            startDate: $startDate,
            endDate: $endDate,
            location: $location,
            meetingLink: $meetingLink,
            requirements: null,
            learningOutcomes: null,
            instructorName: null,
            instructorBio: null,
            featuredImage: null,
            status: 'draft',
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public function publish(): void
    {
        if ($this->status !== 'draft') {
            throw new \DomainException('Only draft workshops can be published');
        }
        $this->status = 'published';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function start(): void
    {
        if ($this->status !== 'published') {
            throw new \DomainException('Only published workshops can be started');
        }
        $this->status = 'ongoing';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function complete(): void
    {
        if ($this->status !== 'ongoing') {
            throw new \DomainException('Only ongoing workshops can be completed');
        }
        $this->status = 'completed';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        if (in_array($this->status, ['completed', 'cancelled'])) {
            throw new \DomainException('Cannot cancel completed or already cancelled workshops');
        }
        $this->status = 'cancelled';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isFull(int $currentRegistrations): bool
    {
        if ($this->maxParticipants === null) {
            return false;
        }
        return $currentRegistrations >= $this->maxParticipants;
    }

    public function isRegistrationOpen(): bool
    {
        return $this->status === 'published' && 
               new DateTimeImmutable() < $this->startDate;
    }

    // Getters
    public function id(): ?int { return $this->id; }
    public function title(): string { return $this->title; }
    public function slug(): string { return $this->slug; }
    public function description(): string { return $this->description; }
    public function category(): WorkshopCategory { return $this->category; }
    public function deliveryFormat(): DeliveryFormat { return $this->deliveryFormat; }
    public function price(): float { return $this->price; }
    public function maxParticipants(): ?int { return $this->maxParticipants; }
    public function lpReward(): int { return $this->lpReward; }
    public function bpReward(): int { return $this->bpReward; }
    public function startDate(): DateTimeImmutable { return $this->startDate; }
    public function endDate(): DateTimeImmutable { return $this->endDate; }
    public function location(): ?string { return $this->location; }
    public function meetingLink(): ?string { return $this->meetingLink; }
    public function status(): string { return $this->status; }
}
