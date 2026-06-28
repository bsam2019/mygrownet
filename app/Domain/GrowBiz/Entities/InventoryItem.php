<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Entities;

use App\Domain\GrowBiz\ValueObjects\InventoryItemId;
use DateTimeImmutable;

class InventoryItem
{
    private function __construct(
        private InventoryItemId $id,
        private int $userId,
        private string $name,
        private ?string $sku,
        private ?string $description,
        private ?int $categoryId,
        private string $unit,
        private float $costPrice,
        private float $sellingPrice,
        private int $currentStock,
        private int $lowStockThreshold,
        private ?string $location,
        private ?string $barcode,
        private ?string $imagePath,
        private bool $isActive,
        private bool $trackStock,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $userId,
        string $name,
        ?string $sku = null,
        ?string $description = null,
        ?int $categoryId = null,
        string $unit = 'piece',
        float $costPrice = 0,
        float $sellingPrice = 0,
        int $initialStock = 0,
        int $lowStockThreshold = 10,
        ?string $location = null,
        ?string $barcode = null
    ): self {
        return new self(
            id: InventoryItemId::generate(),
            userId: $userId,
            name: $name,
            sku: $sku,
            description: $description,
            categoryId: $categoryId,
            unit: $unit,
            costPrice: $costPrice,
            sellingPrice: $sellingPrice,
            currentStock: $initialStock,
            lowStockThreshold: $lowStockThreshold,
            location: $location,
            barcode: $barcode,
            imagePath: null,
            isActive: true,
            trackStock: true,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public static function reconstitute(
        InventoryItemId $id,
        int $userId,
        string $name,
        ?string $sku,
        ?string $description,
        ?int $categoryId,
        string $unit,
        float $costPrice,
        float $sellingPrice,
        int $currentStock,
        int $lowStockThreshold,
        ?string $location,
        ?string $barcode,
        ?string $imagePath,
        bool $isActive,
        bool $trackStock,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            $id, $userId, $name, $sku, $description, $categoryId, $unit,
            $costPrice, $sellingPrice, $currentStock, $lowStockThreshold,
            $location, $barcode, $imagePath, $isActive, $trackStock,
            $createdAt, $updatedAt
        );
    }

    public function update(
        string $name,
        ?string $sku,
        ?string $description,
        ?int $categoryId,
        string $unit,
        float $costPrice,
        float $sellingPrice,
        int $lowStockThreshold,
        ?string $location,
        ?string $barcode
    ): void {
        $this->name = $name;
        $this->sku = $sku;
        $this->description = $description;
        $this->categoryId = $categoryId;
        $this->unit = $unit;
        $this->costPrice = $costPrice;
        $this->sellingPrice = $sellingPrice;
        $this->lowStockThreshold = $lowStockThreshold;
        $this->location = $location;
        $this->barcode = $barcode;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function adjustStock(int $quantity): void
    {
        $this->currentStock += $quantity;
        if ($this->currentStock < 0) {
            $this->currentStock = 0;
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setStock(int $quantity): void
    {
        $this->currentStock = max(0, $quantity);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setImagePath(?string $path): void
    {
        $this->imagePath = $path;
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

    public function isLowStock(): bool
    {
        return $this->trackStock && $this->currentStock <= $this->lowStockThreshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->trackStock && $this->currentStock <= 0;
    }

    public function getStockValue(): float
    {
        return $this->currentStock * $this->costPrice;
    }

    public function getPotentialRevenue(): float
    {
        return $this->currentStock * $this->sellingPrice;
    }

    public function getProfitMargin(): float
    {
        if ($this->costPrice <= 0) return 100;
        return (($this->sellingPrice - $this->costPrice) / $this->costPrice) * 100;
    }

    // Getters
    public function getId(): InventoryItemId { return $this->id; }
    public function id(): int { return $this->id->toInt(); }
    public function getUserId(): int { return $this->userId; }
    public function getName(): string { return $this->name; }
    public function getSku(): ?string { return $this->sku; }
    public function getDescription(): ?string { return $this->description; }
    public function getCategoryId(): ?int { return $this->categoryId; }
    public function getUnit(): string { return $this->unit; }
    public function getCostPrice(): float { return $this->costPrice; }
    public function getSellingPrice(): float { return $this->sellingPrice; }
    public function getCurrentStock(): int { return $this->currentStock; }
    public function getLowStockThreshold(): int { return $this->lowStockThreshold; }
    public function getLocation(): ?string { return $this->location; }
    public function getBarcode(): ?string { return $this->barcode; }
    public function getImagePath(): ?string { return $this->imagePath; }
    public function isActive(): bool { return $this->isActive; }
    public function tracksStock(): bool { return $this->trackStock; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
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
            'is_low_stock' => $this->isLowStock(),
            'is_out_of_stock' => $this->isOutOfStock(),
            'stock_value' => $this->getStockValue(),
            'potential_revenue' => $this->getPotentialRevenue(),
            'profit_margin' => round($this->getProfitMargin(), 1),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
