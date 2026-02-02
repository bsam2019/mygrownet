<?php

namespace App\Domain\Promotion\ValueObjects;

class CardId
{
    private function __construct(private int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Card ID must be positive');
        }
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(CardId $other): bool
    {
        return $this->value === $other->value;
    }
}
