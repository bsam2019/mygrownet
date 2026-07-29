<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\ValueObjects;

final readonly class VideoId
{
    private function __construct(private int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('VideoId must be a positive integer');
        }
        return new self($value);
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
