<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\ValueObjects;

class TodoPriority
{
    private const LOW = 'low';
    private const MEDIUM = 'medium';
    private const HIGH = 'high';

    private function __construct(private string $value) {}

    public static function low(): self
    {
        return new self(self::LOW);
    }

    public static function medium(): self
    {
        return new self(self::MEDIUM);
    }

    public static function high(): self
    {
        return new self(self::HIGH);
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::LOW => self::low(),
            self::MEDIUM => self::medium(),
            self::HIGH => self::high(),
            default => throw new \InvalidArgumentException("Invalid todo priority: {$value}"),
        };
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isLow(): bool
    {
        return $this->value === self::LOW;
    }

    public function isMedium(): bool
    {
        return $this->value === self::MEDIUM;
    }

    public function isHigh(): bool
    {
        return $this->value === self::HIGH;
    }

    public static function all(): array
    {
        return [
            ['value' => self::LOW, 'label' => 'Low'],
            ['value' => self::MEDIUM, 'label' => 'Medium'],
            ['value' => self::HIGH, 'label' => 'High'],
        ];
    }
}
