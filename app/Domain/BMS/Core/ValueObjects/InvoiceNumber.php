<?php

namespace App\Domain\CMS\Core\ValueObjects;

class InvoiceNumber
{
    private function __construct(
        private readonly string $value
    ) {}

    public static function generate(int $year, int $sequence): self
    {
        $paddedSequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        return new self("INV-{$year}-{$paddedSequence}");
    }

    public static function fromString(string $value): self
    {
        if (!preg_match('/^INV-\d{4}-\d{4}$/', $value)) {
            throw new \InvalidArgumentException('Invalid invoice number format');
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
