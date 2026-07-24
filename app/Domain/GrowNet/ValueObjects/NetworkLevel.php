<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\ValueObjects;

class NetworkLevel
{
    public function __construct(private int $level)
    {
        if ($level < 1 || $level > 7) {
            throw new \InvalidArgumentException('Network level must be between 1 and 7');
        }
    }

    public function value(): int
    {
        return $this->level;
    }

    public function equals(self $other): bool
    {
        return $this->level === $other->level;
    }

    public function __toString(): string
    {
        return "Level {$this->level}";
    }
}
