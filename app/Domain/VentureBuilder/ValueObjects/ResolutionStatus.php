<?php

namespace App\Domain\VentureBuilder\ValueObjects;

use InvalidArgumentException;

final class ResolutionStatus
{
    private const DRAFT = 'draft';
    private const VOTING = 'voting';
    private const PASSED = 'passed';
    private const REJECTED = 'rejected';

    private const VALID = [self::DRAFT, self::VOTING, self::PASSED, self::REJECTED];

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, self::VALID, true)) {
            throw new InvalidArgumentException("Invalid resolution status: {$value}");
        }
    }

    public static function draft(): self { return new self(self::DRAFT); }
    public static function voting(): self { return new self(self::VOTING); }
    public static function passed(): self { return new self(self::PASSED); }
    public static function rejected(): self { return new self(self::REJECTED); }
    public static function fromString(string $value): self { return new self($value); }
    public function value(): string { return $this->value; }
    public function isDraft(): bool { return $this->value === self::DRAFT; }
    public function isVoting(): bool { return $this->value === self::VOTING; }
}
