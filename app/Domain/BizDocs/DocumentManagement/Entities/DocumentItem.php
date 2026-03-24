<?php

namespace App\Domain\BizDocs\DocumentManagement\Entities;

use App\Domain\BizDocs\DocumentManagement\ValueObjects\Money;

class DocumentItem
{
    private function __construct(
        private ?int $id,
        private string $description,
        private ?string $dimensions,
        private float $dimensionsValue,
        private float $quantity,
        private Money $unitPrice,
        private float $taxRate,
        private Money $discountAmount,
        private int $sortOrder
    ) {
        $this->validate();
    }

    public static function create(
        string $description,
        float $quantity,
        Money $unitPrice,
        float $taxRate = 0,
        ?Money $discountAmount = null,
        int $sortOrder = 0,
        ?string $dimensions = null,
        float $dimensionsValue = 1.0
    ): self {
        return new self(
            null,
            $description,
            $dimensions,
            $dimensionsValue,
            $quantity,
            $unitPrice,
            $taxRate,
            $discountAmount ?? Money::fromAmount(0, $unitPrice->currency()),
            $sortOrder
        );
    }

    public static function fromPersistence(
        int $id,
        string $description,
        float $quantity,
        Money $unitPrice,
        float $taxRate,
        Money $discountAmount,
        int $sortOrder,
        ?string $dimensions = null,
        float $dimensionsValue = 1.0
    ): self {
        return new self($id, $description, $dimensions, $dimensionsValue, $quantity, $unitPrice, $taxRate, $discountAmount, $sortOrder);
    }

    private function validate(): void
    {
        if (empty($this->description)) {
            throw new \InvalidArgumentException('Description cannot be empty');
        }

        if ($this->quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero');
        }

        if ($this->taxRate < 0 || $this->taxRate > 100) {
            throw new \InvalidArgumentException('Tax rate must be between 0 and 100');
        }
    }

    public function calculateLineTotal(): Money
    {
        $effectiveQuantity = $this->dimensionsValue * $this->quantity;
        $subtotal = $this->unitPrice->multiply($effectiveQuantity);
        $afterDiscount = $subtotal->subtract($this->discountAmount);
        $taxAmount = $afterDiscount->percentage($this->taxRate);
        
        return $afterDiscount->add($taxAmount);
    }

    public function calculateSubtotal(): Money
    {
        $effectiveQuantity = $this->dimensionsValue * $this->quantity;
        return $this->unitPrice->multiply($effectiveQuantity);
    }

    public function calculateTaxAmount(): Money
    {
        $afterDiscount = $this->calculateSubtotal()->subtract($this->discountAmount);
        return $afterDiscount->percentage($this->taxRate);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function dimensions(): ?string
    {
        return $this->dimensions;
    }

    public function dimensionsValue(): float
    {
        return $this->dimensionsValue;
    }

    public function quantity(): float
    {
        return $this->quantity;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function taxRate(): float
    {
        return $this->taxRate;
    }

    public function discountAmount(): Money
    {
        return $this->discountAmount;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function updateDescription(string $description): void
    {
        if (empty($description)) {
            throw new \InvalidArgumentException('Description cannot be empty');
        }
        $this->description = $description;
    }

    public function updateQuantity(float $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero');
        }
        $this->quantity = $quantity;
    }

    public function updateUnitPrice(Money $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }
}
