<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\ValueObjects;

use InvalidArgumentException;
use Illuminate\Support\Str;

final class DocumentId
{
    private function __construct(
        private readonly string $value
    ) {
        if (empty($value)) {
            throw new InvalidArgumentException('Document ID cannot be empty');
        }
    }

    public static function generate(): self
    {
        return new self(Str::uuid()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(DocumentId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
