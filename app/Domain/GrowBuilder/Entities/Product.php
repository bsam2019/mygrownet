<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Entities;

use App\Domain\GrowBuilder\ValueObjects\Money;
use App\Domain\GrowBuilder\ValueObjects\ProductId;
use DateTimeImmutable;

class Product
{
    private function __construct(
        private ?ProductId $id,
        private int $siteId,
        private string $name,
        private string $slug,
        private ?string $description,
        private ?string $shortDescription,
        private Money $price,
        private ?Money $comparePrice,
        private array $images,
        private int $stockQuantity,
        private bool $trackStock,
        private ?string $sku,
        private ?string $category,
        private array $variants,
        private array $attributes,
        private ?float $weight,
        private bool $isActive,
        private bool $isFeatured,
        private int $sortOrder,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        int $siteId,
        string $name,
        string $slug,
        int $priceInNgwee,
        ?string $description = null,
        int $stockQuantity = 0,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: null,
            siteId: $siteId,
            name: $name,
            slug: self::normalizeSlug($slug),
            description: $description,
            shortDescription: null,
            price: Money::fromNgwee($priceInNgwee),
            comparePrice: null,
            images: [],
            stockQuantity: $stockQuantity,
            trackStock: true,
            sku: null,
            category: null,
            variants: [],
            attributes: [],
            weight: null,
            isActive: true,
            isFeatured: false,
            sortOrder: 0,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public static function reconstitute(
        ProductId $id,
        int $siteId,
        string $name,
        string $slug,
        ?string $description,
        ?string $shortDescription,
        Money $price,
        ?Money $comparePrice,
        array $images,
        int $stockQuantity,
        bool $trackStock,
        ?string $sku,
        ?string $category,
        array $variants,
        array $attributes,
        ?float $weight,
        bool $isActive,
        bool $isFeatured,
        int $sortOrder,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id, $siteId, $name, $slug, $description, $shortDescription,
            $price, $comparePrice, $images, $stockQuantity, $trackStock,
            $sku, $category, $variants, $attributes, $weight, $isActive,
            $isFeatured, $sortOrder, $createdAt, $updatedAt
        );
    }

    public function updateDetails(
        string $name,
        ?string $description,
        ?string $shortDescription,
        ?string $category,
    ): void {
        $this->name = $name;
        $this->description = $description;
        $this->shortDescription = $shortDescription;
        $this->category = $category;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePricing(int $priceInNgwee, ?int $comparePriceInNgwee = null): void
    {
        $this->price = Money::fromNgwee($priceInNgwee);
        $this->comparePrice = $comparePriceInNgwee ? Money::fromNgwee($comparePriceInNgwee) : null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateStock(int $quantity): void
    {
        $this->stockQuantity = max(0, $quantity);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function decrementStock(int $quantity): void
    {
        if ($this->trackStock) {
            $this->stockQuantity = max(0, $this->stockQuantity - $quantity);
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function incrementStock(int $quantity): void
    {
        if ($this->trackStock) {
            $this->stockQuantity += $quantity;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function setImages(array $images): void
    {
        $this->images = $images;
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

    public function setFeatured(bool $featured): void
    {
        $this->isFeatured = $featured;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isInStock(): bool
    {
        if (!$this->trackStock) {
            return true;
        }
        return $this->stockQuantity > 0;
    }

    public function hasDiscount(): bool
    {
        return $this->comparePrice !== null && 
               $this->comparePrice->getAmountInNgwee() > $this->price->getAmountInNgwee();
    }

    public function getDiscountPercentage(): int
    {
        if (!$this->hasDiscount()) {
            return 0;
        }
        $original = $this->comparePrice->getAmountInNgwee();
        $current = $this->price->getAmountInNgwee();
        return (int) round((($original - $current) / $original) * 100);
    }

    private static function normalizeSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    // Getters
    public function getId(): ?ProductId { return $this->id; }
    public function getSiteId(): int { return $this->siteId; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getDescription(): ?string { return $this->description; }
    public function getShortDescription(): ?string { return $this->shortDescription; }
    public function getPrice(): Money { return $this->price; }
    public function getComparePrice(): ?Money { return $this->comparePrice; }
    public function getImages(): array { return $this->images; }
    public function getMainImage(): ?string { return $this->images[0] ?? null; }
    public function getStockQuantity(): int { return $this->stockQuantity; }
    public function isTrackingStock(): bool { return $this->trackStock; }
    public function getSku(): ?string { return $this->sku; }
    public function getCategory(): ?string { return $this->category; }
    public function getVariants(): array { return $this->variants; }
    public function getAttributes(): array { return $this->attributes; }
    public function getWeight(): ?float { return $this->weight; }
    public function isActive(): bool { return $this->isActive; }
    public function isFeatured(): bool { return $this->isFeatured; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
