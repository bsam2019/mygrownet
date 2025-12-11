<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\ValueObjects;

use InvalidArgumentException;

final class StageSlug
{
    private const IDEA = 'idea';
    private const VALIDATION = 'validation';
    private const PLANNING = 'planning';
    private const REGISTRATION = 'registration';
    private const LAUNCH = 'launch';
    private const ACCOUNTING = 'accounting';
    private const MARKETING = 'marketing';
    private const GROWTH = 'growth';

    private const VALID_SLUGS = [
        self::IDEA,
        self::VALIDATION,
        self::PLANNING,
        self::REGISTRATION,
        self::LAUNCH,
        self::ACCOUNTING,
        self::MARKETING,
        self::GROWTH,
    ];

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, self::VALID_SLUGS, true)) {
            throw new InvalidArgumentException("Invalid stage slug: {$value}");
        }
    }

    public static function idea(): self { return new self(self::IDEA); }
    public static function validation(): self { return new self(self::VALIDATION); }
    public static function planning(): self { return new self(self::PLANNING); }
    public static function registration(): self { return new self(self::REGISTRATION); }
    public static function launch(): self { return new self(self::LAUNCH); }
    public static function accounting(): self { return new self(self::ACCOUNTING); }
    public static function marketing(): self { return new self(self::MARKETING); }
    public static function growth(): self { return new self(self::GROWTH); }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function all(): array
    {
        return self::VALID_SLUGS;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function order(): int
    {
        return array_search($this->value, self::VALID_SLUGS, true) + 1;
    }

    public function next(): ?self
    {
        $currentIndex = array_search($this->value, self::VALID_SLUGS, true);
        if ($currentIndex === false || $currentIndex >= count(self::VALID_SLUGS) - 1) {
            return null;
        }
        return new self(self::VALID_SLUGS[$currentIndex + 1]);
    }

    public function previous(): ?self
    {
        $currentIndex = array_search($this->value, self::VALID_SLUGS, true);
        if ($currentIndex === false || $currentIndex <= 0) {
            return null;
        }
        return new self(self::VALID_SLUGS[$currentIndex - 1]);
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
