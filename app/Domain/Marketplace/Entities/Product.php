<?php

namespace App\Domain\Marketplace\Entities;

use App\Domain\Marketplace\ValueObjects\ProductStatus;
use App\Domain\Marketplace\ValueObjects\Money;

class Product
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $sellerId,
        public readonly int $categoryId,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $description,
        public readonly Money $price,
        public readonly ?Money $comparePrice,
        public readonly int $stockQuantity,
        public readonly array $images,
        public readonly ProductStatus $status,
        public readonly bool $isFeatured,
        public readonly ?\DateTimeImmutable $createdAt = null,
    ) {}

    public function isAvailable(): bool
    {
        return $this->status->isActive() && $this->stockQuantity > 0;
    }

    public function hasDiscount(): bool
    {
        return $this->comparePrice !== null 
            && $this->comparePrice->amount() > $this->price->amount();
    }

    public function getDiscountPercentage(): int
    {
        if (!$this->hasDiscount()) {
            return 0;
        }

        return (int) round(
            (($this->comparePrice->amount() - $this->price->amount()) / $this->comparePrice->amount()) * 100
        );
    }

    public function canBePurchased(int $quantity): bool
    {
        return $this->isAvailable() && $this->stockQuantity >= $quantity;
    }
}
