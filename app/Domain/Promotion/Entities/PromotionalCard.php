<?php

namespace App\Domain\Promotion\Entities;

use App\Domain\Promotion\ValueObjects\CardCategory;
use App\Domain\Promotion\ValueObjects\CardId;

/**
 * Promotional Card Entity
 * 
 * Represents a shareable promotional card with OG metadata
 * for social media sharing and LGR activity tracking.
 */
class PromotionalCard
{
    private function __construct(
        private CardId $id,
        private string $title,
        private string $slug,
        private ?string $description,
        private string $imagePath,
        private CardCategory $category,
        private int $sortOrder,
        private bool $isActive,
        private ?string $ogTitle,
        private ?string $ogDescription,
        private ?string $ogImage,
        private int $shareCount,
        private int $viewCount
    ) {}

    public static function create(
        CardId $id,
        string $title,
        string $slug,
        string $imagePath,
        CardCategory $category
    ): self {
        return new self(
            id: $id,
            title: $title,
            slug: $slug,
            description: null,
            imagePath: $imagePath,
            category: $category,
            sortOrder: 0,
            isActive: true,
            ogTitle: null,
            ogDescription: null,
            ogImage: null,
            shareCount: 0,
            viewCount: 0
        );
    }

    public function incrementShareCount(): void
    {
        $this->shareCount++;
    }

    public function incrementViewCount(): void
    {
        $this->viewCount++;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function updateSortOrder(int $order): void
    {
        $this->sortOrder = $order;
    }

    public function updateMetadata(
        ?string $description,
        ?string $ogTitle,
        ?string $ogDescription,
        ?string $ogImage
    ): void {
        $this->description = $description;
        $this->ogTitle = $ogTitle;
        $this->ogDescription = $ogDescription;
        $this->ogImage = $ogImage;
    }

    // Getters
    public function getId(): CardId
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    public function getCategory(): CardCategory
    {
        return $this->category;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getShareCount(): int
    {
        return $this->shareCount;
    }

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function getOgTitle(): ?string
    {
        return $this->ogTitle ?? $this->title;
    }

    public function getOgDescription(): ?string
    {
        return $this->ogDescription ?? $this->description;
    }

    public function getOgImage(): ?string
    {
        return $this->ogImage ?? $this->imagePath;
    }
}
