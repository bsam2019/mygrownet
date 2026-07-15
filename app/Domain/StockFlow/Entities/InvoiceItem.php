<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\InvoiceItemId;
use App\Domain\StockFlow\ValueObjects\InvoiceId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\Money;
use DateTimeImmutable;

class InvoiceItem implements Arrayable
{
    private function __construct(
        private InvoiceItemId $id,
        private InvoiceId $invoiceId,
        private ?ItemId $itemId,
        private string $itemName,
        private float $quantity,
        private Money $unitPrice,
        private Money $total,
        private DateTimeImmutable $createdAt,
    ) {}

    public static function create(InvoiceId $invoiceId, ?ItemId $itemId, string $itemName, float $quantity, Money $unitPrice): self
    {
        return new self(InvoiceItemId::generate(), $invoiceId, $itemId, $itemName, $quantity, $unitPrice, $unitPrice->multiply($quantity), new DateTimeImmutable());
    }

    public static function reconstitute(InvoiceItemId $id, InvoiceId $invoiceId, ?ItemId $itemId, string $itemName, float $quantity, Money $unitPrice, Money $total, DateTimeImmutable $createdAt): self
    {
        return new self($id, $invoiceId, $itemId, $itemName, $quantity, $unitPrice, $total, $createdAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function getItemId(): ?ItemId { return $this->itemId; }
    public function getItemName(): string { return $this->itemName; }
    public function getQuantity(): float { return $this->quantity; }
    public function getUnitPrice(): Money { return $this->unitPrice; }
    public function getTotal(): Money { return $this->total; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_invoice_id' => $this->invoiceId->toInt(),
            'sa_item_id' => $this->itemId?->toInt(),
            'item_name' => $this->itemName,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice->toFloat(),
            'total' => $this->total->toFloat(),
        ];
    }
}
