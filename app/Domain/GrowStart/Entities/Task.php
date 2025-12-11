<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Entities;

/**
 * GrowStart Task Entity
 * 
 * Represents a task within a stage that users need to complete.
 */
class Task
{
    private function __construct(
        private int $id,
        private int $stageId,
        private ?int $industryId,
        private ?int $countryId,
        private string $title,
        private ?string $description,
        private ?string $instructions,
        private ?string $externalLink,
        private int $estimatedHours,
        private int $order,
        private bool $isRequired,
        private bool $isPremium
    ) {}

    public static function create(
        int $stageId,
        string $title,
        ?int $industryId = null,
        ?int $countryId = null,
        ?string $description = null,
        ?string $instructions = null,
        ?string $externalLink = null,
        int $estimatedHours = 1,
        int $order = 0,
        bool $isRequired = true,
        bool $isPremium = false
    ): self {
        return new self(
            id: 0,
            stageId: $stageId,
            industryId: $industryId,
            countryId: $countryId,
            title: $title,
            description: $description,
            instructions: $instructions,
            externalLink: $externalLink,
            estimatedHours: $estimatedHours,
            order: $order,
            isRequired: $isRequired,
            isPremium: $isPremium
        );
    }

    public static function reconstitute(
        int $id,
        int $stageId,
        ?int $industryId,
        ?int $countryId,
        string $title,
        ?string $description,
        ?string $instructions,
        ?string $externalLink,
        int $estimatedHours,
        int $order,
        bool $isRequired,
        bool $isPremium
    ): self {
        return new self(
            $id, $stageId, $industryId, $countryId, $title, $description,
            $instructions, $externalLink, $estimatedHours, $order, $isRequired, $isPremium
        );
    }

    public function isGeneric(): bool
    {
        return $this->industryId === null && $this->countryId === null;
    }

    public function isIndustrySpecific(): bool
    {
        return $this->industryId !== null;
    }

    public function isCountrySpecific(): bool
    {
        return $this->countryId !== null;
    }

    public function appliesToIndustry(?int $industryId): bool
    {
        return $this->industryId === null || $this->industryId === $industryId;
    }

    public function appliesToCountry(?int $countryId): bool
    {
        return $this->countryId === null || $this->countryId === $countryId;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getStageId(): int { return $this->stageId; }
    public function getIndustryId(): ?int { return $this->industryId; }
    public function getCountryId(): ?int { return $this->countryId; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getInstructions(): ?string { return $this->instructions; }
    public function getExternalLink(): ?string { return $this->externalLink; }
    public function getEstimatedHours(): int { return $this->estimatedHours; }
    public function getOrder(): int { return $this->order; }
    public function isRequired(): bool { return $this->isRequired; }
    public function isPremium(): bool { return $this->isPremium; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'stage_id' => $this->stageId,
            'industry_id' => $this->industryId,
            'country_id' => $this->countryId,
            'title' => $this->title,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'external_link' => $this->externalLink,
            'estimated_hours' => $this->estimatedHours,
            'order' => $this->order,
            'is_required' => $this->isRequired,
            'is_premium' => $this->isPremium,
        ];
    }
}
