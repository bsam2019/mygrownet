<?php

namespace App\Domain\PrimeEdge\Entities;

use App\Domain\PrimeEdge\ValueObjects\EngagementId;
use DateTimeImmutable;

class EngagementDeliverable
{
    private function __construct(
        private readonly string $id,
        private readonly EngagementId $engagementId,
        private string $name,
        private string $description,
        private ?string $filePath,
        private string $status,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $deliveredAt,
        private ?DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        EngagementId $engagementId,
        string $name,
        string $description = '',
    ): self {
        return new self(
            id: \Ramsey\Uuid\Uuid::uuid4()->toString(),
            engagementId: $engagementId,
            name: $name,
            description: $description,
            filePath: null,
            status: 'pending',
            createdAt: new DateTimeImmutable(),
            deliveredAt: null,
            updatedAt: null,
        );
    }

    public static function reconstitute(
        string $id,
        EngagementId $engagementId,
        string $name,
        string $description,
        ?string $filePath,
        string $status,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $deliveredAt,
        ?DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            engagementId: $engagementId,
            name: $name,
            description: $description,
            filePath: $filePath,
            status: $status,
            createdAt: $createdAt,
            deliveredAt: $deliveredAt,
            updatedAt: $updatedAt,
        );
    }

    public function deliver(string $filePath): void
    {
        $this->filePath = $filePath;
        $this->status = 'delivered';
        $this->deliveredAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): string { return $this->id; }
    public function engagementId(): EngagementId { return $this->engagementId; }
    public function name(): string { return $this->name; }
    public function description(): string { return $this->description; }
    public function filePath(): ?string { return $this->filePath; }
    public function status(): string { return $this->status; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function deliveredAt(): ?DateTimeImmutable { return $this->deliveredAt; }
    public function updatedAt(): ?DateTimeImmutable { return $this->updatedAt; }
}
