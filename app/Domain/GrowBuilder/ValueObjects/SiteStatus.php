<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\ValueObjects;

final class SiteStatus
{
    private const DRAFT = 'draft';
    private const PUBLISHED = 'published';
    private const SUSPENDED = 'suspended';
    private const MAINTENANCE = 'maintenance';
    private const DELETED = 'deleted';

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

    public static function maintenance(): self
    {
        return new self(self::MAINTENANCE);
    }

    public static function deleted(): self
    {
        return new self(self::DELETED);
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::DRAFT => self::draft(),
            self::PUBLISHED => self::published(),
            self::SUSPENDED => self::suspended(),
            self::MAINTENANCE => self::maintenance(),
            self::DELETED => self::deleted(),
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

    public function isMaintenance(): bool
    {
        return $this->value === self::MAINTENANCE;
    }

    public function isDeleted(): bool
    {
        return $this->value === self::DELETED;
    }

    public function isAccessible(): bool
    {
        return $this->value === self::PUBLISHED;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
