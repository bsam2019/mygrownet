<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Entities;

use App\Domain\QuickInvoice\ValueObjects\Money;
use Illuminate\Support\Str;

class LineItem
{
    private string $id;
    private string $description;
    private float $quantity;
    private ?string $unit;
    private Money $unitPrice;
    private Money $amount;
    private int $sortOrder;

    private function __construct(
        string $id,
        string $description,
        float $quantity,
        ?string $unit,
        Money $unitPrice,
        int $sortOrder
    ) {
        $this->id = $id;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->unit = $unit;
        $this->unitPrice = $unitPrice;
        $this->sortOrder = $sortOrder;
        $this->calculateAmount();
    }

    public static function create(
        string $description,
        float $quantity,
        float $unitPrice,
        string $currency = 'ZMW',
        ?string $unit = null,
        int $sortOrder = 0
    ): self {
        return new self(
            id: Str::uuid()->toString(),
            description: trim($description),
            quantity: $quantity,
            unit: $unit,
            unitPrice: Money::create($unitPrice, $currency),
            sortOrder: $sortOrder
        );
    }

    public static function fromArray(array $data, string $currency = 'ZMW'): self
    {
        return new self(
            id: $data['id'] ?? Str::uuid()->toString(),
            description: trim($data['description']),
            quantity: (float) $data['quantity'],
            unit: $data['unit'] ?? null,
            unitPrice: Money::create((float) $data['unit_price'], $currency),
            sortOrder: (int) ($data['sort_order'] ?? 0)
        );
    }

    private function calculateAmount(): void
    {
        $this->amount = $this->unitPrice->multiply($this->quantity);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function quantity(): float
    {
        return $this->quantity;
    }

    public function unit(): ?string
    {
        return $this->unit;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function updateQuantity(float $quantity): void
    {
        $this->quantity = $quantity;
        $this->calculateAmount();
    }

    public function updateUnitPrice(float $price): void
    {
        $this->unitPrice = Money::create($price, $this->unitPrice->currency());
        $this->calculateAmount();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'unit_price' => $this->unitPrice->amount(),
            'amount' => $this->amount->amount(),
            'sort_order' => $this->sortOrder,
        ];
    }
}
