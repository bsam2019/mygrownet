<?php

namespace App\Domain\PrimeEdge\Entities;

use App\Domain\PrimeEdge\ValueObjects\ServiceId;
use App\Domain\PrimeEdge\ValueObjects\ServiceCategory;
use DateTimeImmutable;

class Service
{
    private function __construct(
        private readonly ServiceId $id,
        private string $name,
        private string $description,
        private ServiceCategory $category,
        private bool $active,
        private int $sortOrder,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        ServiceId $id,
        string $name,
        string $description,
        ServiceCategory $category,
        int $sortOrder = 0,
    ): self {
        return new self(
            id: $id,
            name: $name,
            description: $description,
            category: $category,
            active: true,
            sortOrder: $sortOrder,
            createdAt: new DateTimeImmutable(),
            updatedAt: null,
        );
    }

    public static function reconstitute(
        ServiceId $id,
        string $name,
        string $description,
        ServiceCategory $category,
        bool $active,
        int $sortOrder,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            name: $name,
            description: $description,
            category: $category,
            active: $active,
            sortOrder: $sortOrder,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function update(string $name, string $description, ServiceCategory $category, int $sortOrder): void
    {
        $this->name = $name;
        $this->description = $description;
        $this->category = $category;
        $this->sortOrder = $sortOrder;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->active = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->active = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): ServiceId { return $this->id; }
    public function name(): string { return $this->name; }
    public function description(): string { return $this->description; }
    public function category(): ServiceCategory { return $this->category; }
    public function isActive(): bool { return $this->active; }
    public function sortOrder(): int { return $this->sortOrder; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): ?DateTimeImmutable { return $this->updatedAt; }
}
