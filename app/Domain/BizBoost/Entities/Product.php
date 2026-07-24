<?php

namespace App\Domain\BizBoost\Entities;

class Product
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $name,
        public readonly ?string $sku,
        public readonly ?string $description,
        public readonly float $price,
        public readonly ?float $salePrice,
        public readonly ?string $currency,
        public readonly ?string $category,
        public readonly ?int $categoryId,
        public readonly ?int $stockQuantity,
        public readonly bool $trackInventory,
        public readonly bool $isActive,
        public readonly bool $isFeatured,
        public readonly ?int $sortOrder,
        public readonly ?array $attributes,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            sku: $data['sku'] ?? null,
            description: $data['description'] ?? null,
            price: (float) ($data['price'] ?? 0),
            salePrice: isset($data['sale_price']) ? (float) $data['sale_price'] : null,
            currency: $data['currency'] ?? null,
            category: $data['category'] ?? null,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            stockQuantity: isset($data['stock_quantity']) ? (int) $data['stock_quantity'] : null,
            trackInventory: (bool) ($data['track_inventory'] ?? false),
            isActive: (bool) ($data['is_active'] ?? true),
            isFeatured: (bool) ($data['is_featured'] ?? false),
            sortOrder: isset($data['sort_order']) ? (int) $data['sort_order'] : null,
            attributes: $data['attributes'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public static function create(array $data): self
    {
        return new self(
            id: null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            sku: $data['sku'] ?? null,
            description: $data['description'] ?? null,
            price: (float) $data['price'],
            salePrice: isset($data['sale_price']) ? (float) $data['sale_price'] : null,
            currency: $data['currency'] ?? 'ZMW',
            category: $data['category'] ?? null,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            stockQuantity: isset($data['stock_quantity']) ? (int) $data['stock_quantity'] : null,
            trackInventory: (bool) ($data['track_inventory'] ?? false),
            isActive: (bool) ($data['is_active'] ?? true),
            isFeatured: (bool) ($data['is_featured'] ?? false),
            sortOrder: isset($data['sort_order']) ? (int) $data['sort_order'] : null,
            attributes: $data['attributes'] ?? null,
            createdAt: null,
            updatedAt: null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => $this->price,
            'sale_price' => $this->salePrice,
            'currency' => $this->currency,
            'category' => $this->category,
            'category_id' => $this->categoryId,
            'stock_quantity' => $this->stockQuantity,
            'track_inventory' => $this->trackInventory,
            'is_active' => $this->isActive,
            'is_featured' => $this->isFeatured,
            'sort_order' => $this->sortOrder,
            'attributes' => $this->attributes,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}