<?php

namespace App\Domain\Investor\ValueObjects;

use InvalidArgumentException;

/**
 * Investment Round Status Value Object
 */
class InvestmentRoundStatus
{
    private const DRAFT = 'draft';
    private const ACTIVE = 'active';
    private const CLOSED = 'closed';
    private const COMPLETED = 'completed';

    private const VALID_STATUSES = [
        self::DRAFT,
        self::ACTIVE,
        self::CLOSED,
        self::COMPLETED,
    ];

    private function __construct(
        private readonly string $value
    ) {
        if (!in_array($value, self::VALID_STATUSES)) {
            throw new InvalidArgumentException("Invalid investment round status: {$value}");
        }
    }

    public static function draft(): self
    {
        return new self(self::DRAFT);
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function closed(): self
    {
        return new self(self::CLOSED);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public static function from(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function getDisplayName(): string
    {
        return match($this->value) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Active',
            self::CLOSED => 'Closed',
            self::COMPLETED => 'Completed',
        };
    }

    /**
     * Alias for getDisplayName() for consistency with other value objects
     */
    public function label(): string
    {
        return $this->getDisplayName();
    }

    public function getBadgeColor(): string
    {
        return match($this->value) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'green',
            self::CLOSED => 'yellow',
            self::COMPLETED => 'blue',
        };
    }
}
