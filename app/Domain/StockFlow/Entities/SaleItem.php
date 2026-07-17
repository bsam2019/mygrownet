<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\SaleItemId;
use App\Domain\StockFlow\ValueObjects\SaleId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\LotId;
use App\Domain\StockFlow\ValueObjects\Money;
use DateTimeImmutable;

class SaleItem implements Arrayable
{
    private function __construct(
        private SaleItemId $id,
        private SaleId $saleId,
        private ItemId $itemId,
        private ?LotId $lotId,
        private string $itemName,
        private float $quantity,
        private Money $unitPrice,
        private Money $total,
        private DateTimeImmutable $createdAt,
    ) {}

    public static function create(SaleId $saleId, ItemId $itemId, ?LotId $lotId, string $itemName, float $quantity, Money $unitPrice): self
    {
        return new self(SaleItemId::generate(), $saleId, $itemId, $lotId, $itemName, $quantity, $unitPrice, $unitPrice->multiply($quantity), new DateTimeImmutable());
    }

    public static function reconstitute(SaleItemId $id, SaleId $saleId, ItemId $itemId, ?LotId $lotId, string $itemName, float $quantity, Money $unitPrice, Money $total, DateTimeImmutable $createdAt): self
    {
        return new self($id, $saleId, $itemId, $lotId, $itemName, $quantity, $unitPrice, $total, $createdAt);
    }

    public function getLotId(): ?LotId { return $this->lotId; }

    public function id(): int { return $this->id->toInt(); }
    public function getItemId(): ItemId { return $this->itemId; }
    public function getItemName(): string { return $this->itemName; }
    public function getQuantity(): float { return $this->quantity; }
    public function getUnitPrice(): Money { return $this->unitPrice; }
    public function getTotal(): Money { return $this->total; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_sale_id' => $this->saleId->toInt(),
            'sa_item_id' => $this->itemId->toInt(),
            'sa_lot_id' => $this->lotId?->toInt(),
            'item_name' => $this->itemName,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice->toFloat(),
            'total' => $this->total->toFloat(),
        ];
    }
}
