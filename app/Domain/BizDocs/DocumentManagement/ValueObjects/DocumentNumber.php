<?php

namespace App\Domain\BizDocs\DocumentManagement\ValueObjects;

use InvalidArgumentException;

final class DocumentNumber
{
    private function __construct(
        private readonly string $value,
        private readonly string $prefix,
        private readonly int $year,
        private readonly int $number
    ) {
        if (empty($value)) {
            throw new InvalidArgumentException('Document number cannot be empty');
        }
    }

    public static function generate(string $prefix, ?int $year, int $number, int $padding = 4): self
    {
        $paddedNumber = str_pad((string)$number, $padding, '0', STR_PAD_LEFT);
        
        if ($year !== null) {
            $value = "{$prefix}-{$year}-{$paddedNumber}";
        } else {
            $value = "{$prefix}{$paddedNumber}";
        }

        return new self($value, $prefix, $year ?? 0, $number);
    }

    public static function fromString(string $value): self
    {
        // Try to parse format: PREFIX-YEAR-NUMBER
        if (str_contains($value, '-')) {
            $parts = explode('-', $value);

            if (count($parts) >= 3) {
                $prefix = $parts[0];
                $year = (int)$parts[1];
                $number = (int)$parts[2];

                return new self($value, $prefix, $year, $number);
            }
        }

        // Try to parse format: PREFIXNUMBER (e.g., INV70)
        if (preg_match('/^([A-Z]+)(\d+)$/', $value, $matches)) {
            $prefix = $matches[1];
            $number = (int)$matches[2];

            return new self($value, $prefix, 0, $number);
        }

        throw new InvalidArgumentException("Invalid document number format: {$value}");
    }

    public function value(): string
    {
        return $this->value;
    }

    public function prefix(): string
    {
        return $this->prefix;
    }

    public function year(): int
    {
        return $this->year;
    }

    public function number(): int
    {
        return $this->number;
    }

    public function equals(DocumentNumber $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
