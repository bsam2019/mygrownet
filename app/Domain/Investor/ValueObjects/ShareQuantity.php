<?php

namespace App\Domain\Investor\ValueObjects;

use InvalidArgumentException;

class ShareQuantity
{
    private function __construct(private readonly int $quantity)
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException('Share quantity cannot be negative');
        }
    }

    public static function fromInt(int $quantity): self
    {
        return new self($quantity);
    }

    public function value(): int
    {
        return $this->quantity;
    }

    public function add(ShareQuantity $other): self
    {
        return new self($this->quantity + $other->quantity);
    }

    public function subtract(ShareQuantity $other): self
    {
        $result = $this->quantity - $other->quantity;
        if ($result < 0) {
            throw new InvalidArgumentException('Cannot subtract more shares than available');
        }
        return new self($result);
    }

    public function isGreaterThan(ShareQuantity $other): bool
    {
        return $this->quantity > $other->quantity;
    }

    public function equals(ShareQuantity $other): bool
    {
        return $this->quantity === $other->quantity;
    }

    public function __toString(): string
    {
        return (string) $this->quantity;
    }
}
