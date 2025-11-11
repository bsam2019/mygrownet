<?php

namespace App\Domain\Announcement\ValueObjects;

/**
 * Target Audience Value Object
 * 
 * Supports flexible targeting:
 * - 'all' - All users
 * - 'starter_kit_owners' - Users with starter kit
 * - 'tier:Associate' - Specific tier
 * - 'tier:Manager,Director' - Multiple tiers
 */
class TargetAudience
{
    private const ALL = 'all';

    private function __construct(private string $value)
    {
        // Allow any non-empty string for flexibility
        if (empty(trim($value))) {
            throw new \InvalidArgumentException("Target audience cannot be empty");
        }
    }

    public static function all(): self
    {
        return new self(self::ALL);
    }

    public static function starterKitOwners(): self
    {
        return new self('starter_kit_owners');
    }

    public static function tier(string $tierName): self
    {
        return new self("tier:{$tierName}");
    }

    public static function tiers(array $tierNames): self
    {
        return new self('tier:' . implode(',', $tierNames));
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function isAll(): bool
    {
        return $this->value === self::ALL;
    }

    public function isStarterKitOwners(): bool
    {
        return $this->value === 'starter_kit_owners';
    }

    public function isTierSpecific(): bool
    {
        return str_starts_with($this->value, 'tier:');
    }

    public function getTiers(): array
    {
        if (!$this->isTierSpecific()) {
            return [];
        }
        
        $tierPart = substr($this->value, 5); // Remove 'tier:' prefix
        return array_map('trim', explode(',', $tierPart));
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
