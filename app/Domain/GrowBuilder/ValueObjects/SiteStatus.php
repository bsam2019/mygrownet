<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\ValueObjects;

final class SiteStatus
{
    private const DRAFT = 'draft';
    private const PUBLISHED = 'published';
    private const SUSPENDED = 'suspended';

    private function __construct(private string $value) {}

    public static function draft(): self
    {
        return new self(self::DRAFT);
    }

    public static function published(): self
    {
        return new self(self::PUBLISHED);
    }

    public static function suspended(): self
    {
        return new self(self::SUSPENDED);
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::DRAFT => self::draft(),
            self::PUBLISHED => self::published(),
            self::SUSPENDED => self::suspended(),
            default => throw new \InvalidArgumentException("Invalid site status: {$value}"),
        };
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->value === self::PUBLISHED;
    }

    public function isSuspended(): bool
    {
        return $this->value === self::SUSPENDED;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
