<?php

namespace App\Domain\CMS\Core\ValueObjects;

use InvalidArgumentException;

final class JobNumber
{
    private function __construct(
        private readonly string $value
    ) {
        if (empty($value)) {
            throw new InvalidArgumentException('Job number cannot be empty');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function generate(int $year, int $sequence): self
    {
        return new self(sprintf('JOB-%d-%04d', $year, $sequence));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(JobNumber $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
