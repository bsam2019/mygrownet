<?php

namespace App\Domain\Investor\ValueObjects;

use InvalidArgumentException;

class CertificateNumber
{
    private function __construct(private readonly string $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('Certificate number cannot be empty');
        }

        if (!preg_match('/^[A-Z0-9-]+$/', $value)) {
            throw new InvalidArgumentException('Certificate number must contain only uppercase letters, numbers, and hyphens');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function generate(string $projectCode, int $sequence): self
    {
        $paddedSequence = str_pad($sequence, 6, '0', STR_PAD_LEFT);
        return new self("{$projectCode}-{$paddedSequence}");
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(CertificateNumber $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
