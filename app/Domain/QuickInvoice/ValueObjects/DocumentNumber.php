<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\ValueObjects;

use Illuminate\Support\Str;

final class DocumentNumber
{
    private function __construct(
        private readonly string $value
    ) {}

    public static function generate(DocumentType $type): self
    {
        $prefix = $type->prefix();
        $timestamp = now()->format('ymd');
        $random = strtoupper(Str::random(4));
        return new self("{$prefix}-{$timestamp}-{$random}");
    }

    public static function fromString(string $value): self
    {
        return new self(trim($value));
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
