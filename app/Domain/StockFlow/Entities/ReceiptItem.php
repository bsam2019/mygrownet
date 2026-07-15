<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\ReceiptId;
use App\Domain\StockFlow\ValueObjects\Money;
use DateTimeImmutable;

class ReceiptItem implements Arrayable
{
    private function __construct(
        private int $id,
        private ReceiptId $receiptId,
        private string $itemDescription,
        private float $quantity,
        private Money $unitPrice,
        private Money $total,
        private DateTimeImmutable $createdAt,
    ) {}

    public static function create(ReceiptId $receiptId, string $itemDescription, float $quantity, Money $unitPrice): self
    {
        return new self(0, $receiptId, $itemDescription, $quantity, $unitPrice, $unitPrice->multiply($quantity), new DateTimeImmutable());
    }

    public static function reconstitute(int $id, ReceiptId $receiptId, string $itemDescription, float $quantity, Money $unitPrice, Money $total, DateTimeImmutable $createdAt): self
    {
        return new self($id, $receiptId, $itemDescription, $quantity, $unitPrice, $total, $createdAt);
    }

    public function id(): int { return $this->id; }
    public function getItemDescription(): string { return $this->itemDescription; }
    public function getQuantity(): float { return $this->quantity; }
    public function getUnitPrice(): Money { return $this->unitPrice; }
    public function getTotal(): Money { return $this->total; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sa_receipt_id' => $this->receiptId->toInt(),
            'item_description' => $this->itemDescription,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice->toFloat(),
            'total' => $this->total->toFloat(),
        ];
    }
}
