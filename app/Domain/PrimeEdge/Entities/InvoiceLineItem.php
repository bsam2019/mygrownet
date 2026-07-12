<?php

namespace App\Domain\PrimeEdge\Entities;

use App\Domain\PrimeEdge\ValueObjects\Money;

class InvoiceLineItem
{
    private function __construct(
        private string $description,
        private Money $unitPrice,
        private int $quantity,
    ) {
        if ($quantity <= 0) {
            throw new \DomainException('Quantity must be positive');
        }
    }

    public static function create(string $description, Money $unitPrice, int $quantity = 1): self
    {
        return new self($description, $unitPrice, $quantity);
    }

    public static function reconstitute(string $description, Money $unitPrice, int $quantity): self
    {
        return new self($description, $unitPrice, $quantity);
    }

    public function total(): Money
    {
        return $this->unitPrice->multiply($this->quantity);
    }

    public function description(): string { return $this->description; }
    public function unitPrice(): Money { return $this->unitPrice; }
    public function quantity(): int { return $this->quantity; }
}
