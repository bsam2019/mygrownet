<?php

declare(strict_types=1);

namespace App\Domain\MLM\ValueObjects;

use InvalidArgumentException;

final class CommissionLevel
{
    private const MIN_LEVEL = 1;
    private const MAX_LEVEL = 5;

    private function __construct(private int $value)
    {
        if ($value < self::MIN_LEVEL || $value > self::MAX_LEVEL) {
            throw new InvalidArgumentException(
                sprintf('Commission level must be between %d and %d', self::MIN_LEVEL, self::MAX_LEVEL)
            );
        }
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public static function levelOne(): self
    {
        return new self(1);
    }

    public static function levelTwo(): self
    {
        return new self(2);
    }

    public static function levelThree(): self
    {
        return new self(3);
    }

    public static function levelFour(): self
    {
        return new self(4);
    }

    public static function levelFive(): self
    {
        return new self(5);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(CommissionLevel $other): bool
    {
        return $this->value === $other->value;
    }

    public function isDirectReferral(): bool
    {
        return $this->value === 1;
    }

    public function __toString(): string
    {
        return "Level {$this->value}";
    }
}