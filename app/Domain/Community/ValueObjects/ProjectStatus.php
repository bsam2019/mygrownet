<?php

declare(strict_types=1);

namespace App\Domain\Community\ValueObjects;

use InvalidArgumentException;

final class ProjectStatus
{
    private const PLANNING = 'planning';
    private const FUNDING = 'funding';
    private const ACTIVE = 'active';
    private const COMPLETED = 'completed';
    private const CANCELLED = 'cancelled';
    private const SUSPENDED = 'suspended';

    private const VALID_STATUSES = [
        self::PLANNING,
        self::FUNDING,
        self::ACTIVE,
        self::COMPLETED,
        self::CANCELLED,
        self::SUSPENDED,
    ];

    private function __construct(private string $value)
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid project status: %s. Valid statuses are: %s', 
                    $value, 
                    implode(', ', self::VALID_STATUSES)
                )
            );
        }
    }

    public static function planning(): self
    {
        return new self(self::PLANNING);
    }

    public static function funding(): self
    {
        return new self(self::FUNDING);
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function suspended(): self
    {
        return new self(self::SUSPENDED);
    }

    public static function fromString(string $value): self
    {
        return new self(strtolower($value));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(ProjectStatus $other): bool
    {
        return $this->value === $other->value;
    }

    public function isPlanning(): bool
    {
        return $this->value === self::PLANNING;
    }

    public function isFunding(): bool
    {
        return $this->value === self::FUNDING;
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this->value === self::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }

    public function isSuspended(): bool
    {
        return $this->value === self::SUSPENDED;
    }

    public function canAcceptContributions(): bool
    {
        return $this->value === self::FUNDING;
    }

    public function canGenerateReturns(): bool
    {
        return in_array($this->value, [self::ACTIVE, self::COMPLETED]);
    }

    public function isOperational(): bool
    {
        return in_array($this->value, [self::FUNDING, self::ACTIVE]);
    }

    public function __toString(): string
    {
        return ucfirst($this->value);
    }
}