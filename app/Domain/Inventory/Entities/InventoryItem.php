<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use DateTimeImmutable;

class InventoryItem
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $name,
        public readonly ?string $sku,
        public readonly ?string $description,
        public readonly ?int $categoryId,
        public readonly string $unit,
        public readonly float $costPrice,
        public readonly float $sellingPrice,
        public readonly int $currentStock,
        public readonly int $lowStockThreshold,
        public readonly ?string $location,
        public readonly ?string $barcode,
        public readonly ?string $imagePath,
        public readonly bool $isActive,
        public readonly bool $trackStock,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
        public readonly ?DateTimeImmutable $deletedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) $data['user_id'],
            name: $data['name'],
            sku: $data['sku'] ?? null,
            description: $data['description'] ?? null,
            categoryId: $data['category_id'] ?? null,
            unit: $data['unit'] ?? 'piece',
            costPrice: (float) ($data['cost_price'] ?? 0),
            sellingPrice: (float) ($data['selling_price'] ?? 0),
            currentStock: (int) ($data['current_stock'] ?? 0),
            lowStockThreshold: (int) ($data['low_stock_threshold'] ?? 10),
            location: $data['location'] ?? null,
            barcode: $data['barcode'] ?? null,
            imagePath: $data['image_path'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            trackStock: (bool) ($data['track_stock'] ?? true),
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? new DateTimeImmutable($data['deleted_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'category_id' => $this->categoryId,
            'unit' => $this->unit,
            'cost_price' => $this->costPrice,
            'selling_price' => $this->sellingPrice,
            'current_stock' => $this->currentStock,
            'low_stock_threshold' => $this->lowStockThreshold,
            'location' => $this->location,
            'barcode' => $this->barcode,
            'image_path' => $this->imagePath,
            'is_active' => $this->isActive,
            'track_stock' => $this->trackStock,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deletedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function isLowStock(): bool
    {
        return $this->currentStock <= $this->lowStockThreshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->currentStock <= 0;
    }

    public function stockValue(): float
    {
        return $this->currentStock * $this->costPrice;
    }

    public function retailValue(): float
    {
        return $this->currentStock * $this->sellingPrice;
    }
}
