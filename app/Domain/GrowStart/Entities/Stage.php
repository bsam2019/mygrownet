<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Entities;

use App\Domain\GrowStart\ValueObjects\StageSlug;

/**
 * GrowStart Stage Entity
 * 
 * Represents one of the 8 stages in the startup journey.
 */
class Stage
{
    private function __construct(
        private int $id,
        private string $name,
        private StageSlug $slug,
        private ?string $description,
        private int $order,
        private ?string $icon,
        private ?string $color,
        private int $estimatedDays,
        private bool $isActive
    ) {}

    public static function create(
        string $name,
        StageSlug $slug,
        int $order,
        ?string $description = null,
        ?string $icon = null,
        ?string $color = null,
        int $estimatedDays = 7
    ): self {
        return new self(
            id: 0,
            name: $name,
            slug: $slug,
            description: $description,
            order: $order,
            icon: $icon,
            color: $color,
            estimatedDays: $estimatedDays,
            isActive: true
        );
    }

    public static function reconstitute(
        int $id,
        string $name,
        StageSlug $slug,
        ?string $description,
        int $order,
        ?string $icon,
        ?string $color,
        int $estimatedDays,
        bool $isActive
    ): self {
        return new self(
            $id, $name, $slug, $description, $order, $icon, $color, $estimatedDays, $isActive
        );
    }

    public function isFirst(): bool
    {
        return $this->order === 1;
    }

    public function isLast(): bool
    {
        return $this->order === 8;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): StageSlug { return $this->slug; }
    public function getDescription(): ?string { return $this->description; }
    public function getOrder(): int { return $this->order; }
    public function getIcon(): ?string { return $this->icon; }
    public function getColor(): ?string { return $this->color; }
    public function getEstimatedDays(): int { return $this->estimatedDays; }
    public function isActive(): bool { return $this->isActive; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug->value(),
            'description' => $this->description,
            'order' => $this->order,
            'icon' => $this->icon,
            'color' => $this->color,
            'estimated_days' => $this->estimatedDays,
            'is_active' => $this->isActive,
        ];
    }
}
