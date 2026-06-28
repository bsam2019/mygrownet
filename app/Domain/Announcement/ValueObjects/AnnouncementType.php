<?php

namespace App\Domain\Announcement\ValueObjects;

/**
 * Announcement Type Value Object
 */
class AnnouncementType
{
    private const INFO = 'info';
    private const WARNING = 'warning';
    private const SUCCESS = 'success';
    private const URGENT = 'urgent';

    private function __construct(private string $value)
    {
        if (!in_array($value, [self::INFO, self::WARNING, self::SUCCESS, self::URGENT])) {
            throw new \InvalidArgumentException("Invalid announcement type: {$value}");
        }
    }

    public static function info(): self
    {
        return new self(self::INFO);
    }

    public static function warning(): self
    {
        return new self(self::WARNING);
    }

    public static function success(): self
    {
        return new self(self::SUCCESS);
    }

    public static function urgent(): self
    {
        return new self(self::URGENT);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function isUrgent(): bool
    {
        return $this->value === self::URGENT;
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
