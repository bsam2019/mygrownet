<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\ValueObjects;

use InvalidArgumentException;

final class TaskPriority
{
    private const LOW = 'low';
    private const MEDIUM = 'medium';
    private const HIGH = 'high';
    private const URGENT = 'urgent';

    private const VALID_PRIORITIES = [
        self::LOW,
        self::MEDIUM,
        self::HIGH,
        self::URGENT,
    ];

    private function __construct(private string $value)
    {
        if (!in_array($value, self::VALID_PRIORITIES, true)) {
            throw new InvalidArgumentException("Invalid task priority: {$value}");
        }
    }

    public static function low(): self { return new self(self::LOW); }
    public static function medium(): self { return new self(self::MEDIUM); }
    public static function high(): self { return new self(self::HIGH); }
    public static function urgent(): self { return new self(self::URGENT); }
    public static function fromString(string $priority): self { return new self($priority); }

    public function getValue(): string { return $this->value; }
    public function value(): string { return $this->value; }
    public function isLow(): bool { return $this->value === self::LOW; }
    public function isMedium(): bool { return $this->value === self::MEDIUM; }
    public function isHigh(): bool { return $this->value === self::HIGH; }
    public function isUrgent(): bool { return $this->value === self::URGENT; }
    public function equals(self $other): bool { return $this->value === $other->value; }

    public static function all(): array
    {
        return [
            ['value' => self::LOW, 'label' => 'Low', 'color' => 'gray'],
            ['value' => self::MEDIUM, 'label' => 'Medium', 'color' => 'blue'],
            ['value' => self::HIGH, 'label' => 'High', 'color' => 'orange'],
            ['value' => self::URGENT, 'label' => 'Urgent', 'color' => 'red'],
        ];
    }
}
