<?php

namespace App\Domain\GrowMart\Entities;

use App\Domain\GrowMart\ValueObjects\Money;
use App\Domain\GrowMart\ValueObjects\ProductStatus;

class Product
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $categoryId,
        private readonly string $name,
        private readonly string $slug,
        private readonly string $description,
        private readonly string $unit,
        private readonly Money $price,
        private readonly ?Money $comparePrice,
        private readonly ProductStatus $status,
        private readonly array $images = [],
        private readonly int $totalStock = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            categoryId: $data['category_id'],
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? '',
            unit: $data['unit'],
            price: Money::fromNgwee($data['price']),
            comparePrice: isset($data['compare_price']) ? Money::fromNgwee($data['compare_price']) : null,
            status: ProductStatus::fromString($data['status'] ?? 'active'),
            images: $data['images'] ?? [],
            totalStock: $data['total_stock'] ?? 0,
        );
    }

    public function id(): ?int { return $this->id; }
    public function categoryId(): int { return $this->categoryId; }
    public function name(): string { return $this->name; }
    public function slug(): string { return $this->slug; }
    public function description(): string { return $this->description; }
    public function unit(): string { return $this->unit; }
    public function price(): Money { return $this->price; }
    public function comparePrice(): ?Money { return $this->comparePrice; }
    public function status(): ProductStatus { return $this->status; }
    public function images(): array { return $this->images; }
    public function totalStock(): int { return $this->totalStock; }

    public function isAvailable(): bool
    {
        return $this->status->isActive() && $this->totalStock > 0;
    }

    public function hasDiscount(): bool
    {
        return $this->comparePrice !== null && $this->comparePrice->ngwee() > $this->price->ngwee();
    }

    public function getDiscountPercentage(): int
    {
        if (!$this->hasDiscount()) {
            return 0;
        }
        return (int) round((1 - $this->price->ngwee() / $this->comparePrice->ngwee()) * 100);
    }
}
