<?php

declare(strict_types=1);

namespace App\Domain\Reward\ValueObjects;

use InvalidArgumentException;

final class AssetType
{
    private const SMARTPHONE = 'SMARTPHONE';
    private const TABLET = 'TABLET';
    private const MOTORBIKE = 'MOTORBIKE';
    private const CAR = 'CAR';
    private const PROPERTY = 'PROPERTY';
    private const OFFICE_EQUIPMENT = 'OFFICE_EQUIPMENT';
    private const STARTER_KIT = 'STARTER_KIT';

    private const VALID_TYPES = [
        self::SMARTPHONE,
        self::TABLET,
        self::MOTORBIKE,
        self::CAR,
        self::PROPERTY,
        self::OFFICE_EQUIPMENT,
        self::STARTER_KIT,
    ];

    private function __construct(private string $value)
    {
        if (!in_array($value, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid asset type: %s. Valid types are: %s', 
                    $value, 
                    implode(', ', self::VALID_TYPES)
                )
            );
        }
    }

    public static function smartphone(): self
    {
        return new self(self::SMARTPHONE);
    }

    public static function tablet(): self
    {
        return new self(self::TABLET);
    }

    public static function motorbike(): self
    {
        return new self(self::MOTORBIKE);
    }

    public static function car(): self
    {
        return new self(self::CAR);
    }

    public static function property(): self
    {
        return new self(self::PROPERTY);
    }

    public static function officeEquipment(): self
    {
        return new self(self::OFFICE_EQUIPMENT);
    }

    public static function starterKit(): self
    {
        return new self(self::STARTER_KIT);
    }

    public static function fromString(string $value): self
    {
        return new self(strtoupper($value));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(AssetType $other): bool
    {
        return $this->value === $other->value;
    }

    public function isSmartphone(): bool
    {
        return $this->value === self::SMARTPHONE;
    }

    public function isTablet(): bool
    {
        return $this->value === self::TABLET;
    }

    public function isMotorbike(): bool
    {
        return $this->value === self::MOTORBIKE;
    }

    public function isCar(): bool
    {
        return $this->value === self::CAR;
    }

    public function isProperty(): bool
    {
        return $this->value === self::PROPERTY;
    }

    public function isOfficeEquipment(): bool
    {
        return $this->value === self::OFFICE_EQUIPMENT;
    }

    public function isStarterKit(): bool
    {
        return $this->value === self::STARTER_KIT;
    }

    public function isIncomeGenerating(): bool
    {
        return in_array($this->value, [self::PROPERTY, self::MOTORBIKE, self::CAR]);
    }

    public function getEstimatedValueRange(): array
    {
        return match($this->value) {
            self::STARTER_KIT => ['min' => 100, 'max' => 500],
            self::SMARTPHONE, self::TABLET => ['min' => 2000, 'max' => 4000],
            self::OFFICE_EQUIPMENT => ['min' => 5000, 'max' => 15000],
            self::MOTORBIKE => ['min' => 8000, 'max' => 15000],
            self::CAR => ['min' => 25000, 'max' => 50000],
            self::PROPERTY => ['min' => 75000, 'max' => 150000],
            default => ['min' => 0, 'max' => 0]
        };
    }

    public function __toString(): string
    {
        return $this->value;
    }
}