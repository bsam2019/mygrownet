<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\ValueObjects;

use InvalidArgumentException;

final class EmployeeStatus
{
    private const ACTIVE = 'active';
    private const INACTIVE = 'inactive';
    private const ON_LEAVE = 'on_leave';
    private const TERMINATED = 'terminated';

    private const VALID_STATUSES = [
        self::ACTIVE,
        self::INACTIVE,
        self::ON_LEAVE,
        self::TERMINATED,
    ];

    private function __construct(private string $value)
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid employee status: {$value}");
        }
    }

    public static function active(): self { return new self(self::ACTIVE); }
    public static function inactive(): self { return new self(self::INACTIVE); }
    public static function onLeave(): self { return new self(self::ON_LEAVE); }
    public static function terminated(): self { return new self(self::TERMINATED); }
    public static function fromString(string $status): self { return new self($status); }

    public function getValue(): string { return $this->value; }
    public function value(): string { return $this->value; }
    public function isActive(): bool { return $this->value === self::ACTIVE; }
    public function isInactive(): bool { return $this->value === self::INACTIVE; }
    public function isOnLeave(): bool { return $this->value === self::ON_LEAVE; }
    public function isTerminated(): bool { return $this->value === self::TERMINATED; }
    public function equals(self $other): bool { return $this->value === $other->value; }

    public static function all(): array
    {
        return [
            ['value' => self::ACTIVE, 'label' => 'Active', 'color' => 'green'],
            ['value' => self::INACTIVE, 'label' => 'Inactive', 'color' => 'gray'],
            ['value' => self::ON_LEAVE, 'label' => 'On Leave', 'color' => 'yellow'],
            ['value' => self::TERMINATED, 'label' => 'Terminated', 'color' => 'red'],
        ];
    }
}
