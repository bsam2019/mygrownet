<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\CountItemId;
use App\Domain\StockFlow\ValueObjects\PhysicalCountId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\Money;
use DateTimeImmutable;

class CountItem
{
    private function __construct(
        private CountItemId $id,
        private PhysicalCountId $physicalCountId,
        private ItemId $itemId,
        private ?BinId $binId,
        private float $systemQuantity,
        private float $physicalQuantity,
        private float $variance,
        private Money $unitPrice,
        private Money $varianceValue,
        private ?string $itemName,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(PhysicalCountId $physicalCountId, ItemId $itemId, ?BinId $binId, float $systemQuantity, Money $unitPrice, ?string $itemName = null): self
    {
        return new self(CountItemId::generate(), $physicalCountId, $itemId, $binId, $systemQuantity, 0, -$systemQuantity, $unitPrice, Money::zero(), $itemName, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(CountItemId $id, PhysicalCountId $physicalCountId, ItemId $itemId, ?BinId $binId, float $systemQuantity, float $physicalQuantity, float $variance, Money $unitPrice, Money $varianceValue, ?string $itemName, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $physicalCountId, $itemId, $binId, $systemQuantity, $physicalQuantity, $variance, $unitPrice, $varianceValue, $itemName, $createdAt, $updatedAt);
    }

    public function recordPhysical(float $physicalQuantity): void
    {
        $this->physicalQuantity = $physicalQuantity;
        $this->variance = $physicalQuantity - $this->systemQuantity;
        $this->varianceValue = $this->unitPrice->multiply($this->variance);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): int { return $this->id->toInt(); }
    public function getItemId(): ItemId { return $this->itemId; }
    public function getSystemQuantity(): float { return $this->systemQuantity; }
    public function getPhysicalQuantity(): float { return $this->physicalQuantity; }
    public function getVariance(): float { return $this->variance; }
    public function getVarianceValue(): Money { return $this->varianceValue; }
    public function getUnitPrice(): Money { return $this->unitPrice; }
    public function getItemName(): ?string { return $this->itemName; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_physical_count_id' => $this->physicalCountId->toInt(),
            'sa_item_id' => $this->itemId->toInt(),
            'sa_bin_id' => $this->binId?->toInt(),
            'item_name' => $this->itemName,
            'system_quantity' => $this->systemQuantity,
            'physical_quantity' => $this->physicalQuantity,
            'variance' => $this->variance,
            'unit_price' => $this->unitPrice->toFloat(),
            'variance_value' => $this->varianceValue->toFloat(),
        ];
    }
}
