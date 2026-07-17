<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\PurchaseOrderItemId;
use App\Domain\StockFlow\ValueObjects\PurchaseOrderId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\LotId;
use App\Domain\StockFlow\ValueObjects\Money;
use DateTimeImmutable;

class PurchaseOrderItem implements Arrayable
{
    private function __construct(
        private PurchaseOrderItemId $id,
        private PurchaseOrderId $purchaseOrderId,
        private ItemId $itemId,
        private ?LotId $lotId,
        private float $quantityOrdered,
        private float $quantityReceived,
        private Money $unitCost,
        private Money $totalCost,
        private ?string $itemName,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(PurchaseOrderId $purchaseOrderId, ItemId $itemId, ?LotId $lotId, float $quantityOrdered, Money $unitCost, ?string $itemName = null): self
    {
        return new self(PurchaseOrderItemId::generate(), $purchaseOrderId, $itemId, $lotId, $quantityOrdered, 0, $unitCost, $unitCost->multiply($quantityOrdered), $itemName, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(PurchaseOrderItemId $id, PurchaseOrderId $purchaseOrderId, ItemId $itemId, ?LotId $lotId, float $quantityOrdered, float $quantityReceived, Money $unitCost, Money $totalCost, ?string $itemName, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $purchaseOrderId, $itemId, $lotId, $quantityOrdered, $quantityReceived, $unitCost, $totalCost, $itemName, $createdAt, $updatedAt);
    }

    public function getLotId(): ?LotId { return $this->lotId; }

    public function receive(float $quantity): void
    {
        $this->quantityReceived += $quantity;
        if ($this->quantityReceived > $this->quantityOrdered) {
            $this->quantityReceived = $this->quantityOrdered;
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isFullyReceived(): bool { return $this->quantityReceived >= $this->quantityOrdered; }
    public function getItemId(): ItemId { return $this->itemId; }
    public function getItemName(): ?string { return $this->itemName; }
    public function getQuantityOrdered(): float { return $this->quantityOrdered; }
    public function getQuantityReceived(): float { return $this->quantityReceived; }
    public function getUnitCost(): Money { return $this->unitCost; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_purchase_order_id' => $this->purchaseOrderId->toInt(),
            'sa_item_id' => $this->itemId->toInt(),
            'sa_lot_id' => $this->lotId?->toInt(),
            'item_name' => $this->itemName,
            'quantity_ordered' => $this->quantityOrdered,
            'quantity_received' => $this->quantityReceived,
            'unit_cost' => $this->unitCost->toFloat(),
            'total_cost' => $this->totalCost->toFloat(),
        ];
    }
}
