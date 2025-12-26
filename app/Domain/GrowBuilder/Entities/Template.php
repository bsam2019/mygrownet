<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Entities;

use App\Domain\GrowBuilder\ValueObjects\TemplateId;
use App\Domain\GrowBuilder\ValueObjects\TemplateCategory;
use DateTimeImmutable;

class Template
{
    private function __construct(
        private ?TemplateId $id,
        private string $name,
        private string $slug,
        private TemplateCategory $category,
        private ?string $description,
        private ?string $previewImage,
        private ?string $thumbnail,
        private array $structureJson,
        private array $defaultStyles,
        private bool $isPremium,
        private int $price,
        private bool $isActive,
        private int $usageCount,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        string $name,
        string $slug,
        TemplateCategory $category,
        array $structureJson,
        ?string $description = null,
        bool $isPremium = false,
        int $price = 0,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: null,
            name: $name,
            slug: $slug,
            category: $category,
            description: $description,
            previewImage: null,
            thumbnail: null,
            structureJson: $structureJson,
            defaultStyles: [],
            isPremium: $isPremium,
            price: $price,
            isActive: true,
            usageCount: 0,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public static function reconstitute(
        TemplateId $id,
        string $name,
        string $slug,
        TemplateCategory $category,
        ?string $description,
        ?string $previewImage,
        ?string $thumbnail,
        array $structureJson,
        array $defaultStyles,
        bool $isPremium,
        int $price,
        bool $isActive,
        int $usageCount,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id, $name, $slug, $category, $description, $previewImage,
            $thumbnail, $structureJson, $defaultStyles, $isPremium,
            $price, $isActive, $usageCount, $createdAt, $updatedAt
        );
    }

    public function incrementUsage(): void
    {
        $this->usageCount++;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePricing(bool $isPremium, int $price): void
    {
        $this->isPremium = $isPremium;
        $this->price = $price;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setPreviewImage(?string $previewImage): void
    {
        $this->previewImage = $previewImage;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setThumbnail(?string $thumbnail): void
    {
        $this->thumbnail = $thumbnail;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateStructure(array $structureJson): void
    {
        $this->structureJson = $structureJson;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDefaultStyles(array $defaultStyles): void
    {
        $this->defaultStyles = $defaultStyles;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isFree(): bool
    {
        return !$this->isPremium || $this->price === 0;
    }

    // Getters
    public function getId(): ?TemplateId { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getCategory(): TemplateCategory { return $this->category; }
    public function getDescription(): ?string { return $this->description; }
    public function getPreviewImage(): ?string { return $this->previewImage; }
    public function getThumbnail(): ?string { return $this->thumbnail; }
    public function getStructureJson(): array { return $this->structureJson; }
    public function getDefaultStyles(): array { return $this->defaultStyles; }
    public function isPremium(): bool { return $this->isPremium; }
    public function getPrice(): int { return $this->price; }
    public function isActive(): bool { return $this->isActive; }
    public function getUsageCount(): int { return $this->usageCount; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
