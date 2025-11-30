<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObjects;

final class TimeOffType
{
    private const ANNUAL = 'annual';
    private const SICK = 'sick';
    private const PERSONAL = 'personal';
    private const MATERNITY = 'maternity';
    private const PATERNITY = 'paternity';
    private const BEREAVEMENT = 'bereavement';
    private const UNPAID = 'unpaid';
    private const STUDY = 'study';

    private const VALID_TYPES = [
        self::ANNUAL,
        self::SICK,
        self::PERSONAL,
        self::MATERNITY,
        self::PATERNITY,
        self::BEREAVEMENT,
        self::UNPAID,
        self::STUDY,
    ];

    private const DEFAULT_ALLOWANCES = [
        self::ANNUAL => 21,
        self::SICK => 10,
        self::PERSONAL => 3,
        self::MATERNITY => 90,
        self::PATERNITY => 5,
        self::BEREAVEMENT => 5,
        self::UNPAID => 30,
        self::STUDY => 10,
    ];

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, self::VALID_TYPES, true)) {
            throw new \InvalidArgumentException("Invalid time off type: {$value}");
        }
    }

    public static function annual(): self
    {
        return new self(self::ANNUAL);
    }

    public static function sick(): self
    {
        return new self(self::SICK);
    }

    public static function personal(): self
    {
        return new self(self::PERSONAL);
    }

    public static function maternity(): self
    {
        return new self(self::MATERNITY);
    }

    public static function paternity(): self
    {
        return new self(self::PATERNITY);
    }

    public static function bereavement(): self
    {
        return new self(self::BEREAVEMENT);
    }

    public static function unpaid(): self
    {
        return new self(self::UNPAID);
    }

    public static function study(): self
    {
        return new self(self::STUDY);
    }

    public static function fromString(string $value): self
    {
        return new self(strtolower($value));
    }

    public static function all(): array
    {
        return array_map(fn($type) => new self($type), self::VALID_TYPES);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->value)) . ' Leave';
    }

    public function getDefaultAllowance(): int
    {
        return self::DEFAULT_ALLOWANCES[$this->value];
    }

    public function isPaid(): bool
    {
        return $this->value !== self::UNPAID;
    }

    public function requiresDocumentation(): bool
    {
        return in_array($this->value, [self::SICK, self::MATERNITY, self::PATERNITY, self::BEREAVEMENT], true);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
