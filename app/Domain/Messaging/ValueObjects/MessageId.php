<?php

namespace App\Domain\Messaging\ValueObjects;

use InvalidArgumentException;

final class MessageId
{
    private function __construct(private int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('Message ID must be positive');
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

    public function equals(MessageId $other): bool
    {
        return $this->value === $other->value;
    }
}
