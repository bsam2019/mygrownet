<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\Money;
use DateTimeImmutable;

class TransferItem implements Arrayable
{
    private function __construct(
        private int $id,
        private ItemId $itemId,
        private float $quantity,
        private ?Money $unitCost,
        private DateTimeImmutable $createdAt,
        private ?string $itemName = null,
    ) {}

    public static function create(ItemId $itemId, float $quantity, ?Money $unitCost = null, ?string $itemName = null): self
    {
        return new self(0, $itemId, $quantity, $unitCost, new DateTimeImmutable(), $itemName);
    }

    public static function reconstitute(int $id, ItemId $itemId, float $quantity, ?Money $unitCost, DateTimeImmutable $createdAt, ?string $itemName = null): self
    {
        return new self($id, $itemId, $quantity, $unitCost, $createdAt, $itemName);
    }

    public function getItemId(): ItemId { return $this->itemId; }
    public function getQuantity(): float { return $this->quantity; }
    public function getUnitCost(): ?Money { return $this->unitCost; }
    public function getItemName(): ?string { return $this->itemName; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sa_item_id' => $this->itemId->toInt(),
            'item_name' => $this->itemName,
            'quantity' => $this->quantity,
            'unit_cost' => $this->unitCost?->toFloat(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
