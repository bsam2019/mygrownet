<?php

namespace App\Domain\GrowBuilder\Product\DTOs;

class ProductData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $shortDescription,
        public readonly int $priceInNgwee,
        public readonly ?int $comparePriceInNgwee,
        public readonly int $stockQuantity,
        public readonly bool $trackStock,
        public readonly ?string $sku,
        public readonly ?string $category,
        public readonly array $images,
        public readonly bool $isActive,
        public readonly bool $isFeatured,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            shortDescription: $data['short_description'] ?? null,
            priceInNgwee: (int) (($data['price'] ?? 0) * 100),
            comparePriceInNgwee: isset($data['compare_price']) ? (int) ($data['compare_price'] * 100) : null,
            stockQuantity: $data['stock_quantity'] ?? 0,
            trackStock: $data['track_stock'] ?? true,
            sku: $data['sku'] ?? null,
            category: $data['category'] ?? null,
            images: $data['images'] ?? [],
            isActive: $data['is_active'] ?? true,
            isFeatured: $data['is_featured'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'short_description' => $this->shortDescription,
            'price' => $this->priceInNgwee,
            'compare_price' => $this->comparePriceInNgwee,
            'stock_quantity' => $this->stockQuantity,
            'track_stock' => $this->trackStock,
            'sku' => $this->sku,
            'category' => $this->category,
            'images' => $this->images,
            'is_active' => $this->isActive,
            'is_featured' => $this->isFeatured,
        ];
    }
}
