<?php

namespace App\Domain\CMS\Core\ValueObjects;

use InvalidArgumentException;

final class CustomerNumber
{
    private function __construct(
        private readonly string $value
    ) {
        if (empty($value)) {
            throw new InvalidArgumentException('Customer number cannot be empty');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function generate(int $sequence): self
    {
        return new self(sprintf('CUST-%04d', $sequence));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(CustomerNumber $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
