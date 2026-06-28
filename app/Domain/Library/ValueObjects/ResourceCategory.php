<?php

namespace App\Domain\Library\ValueObjects;

use InvalidArgumentException;

final class ResourceCategory
{
    private const VALID_CATEGORIES = [
        'business',
        'marketing',
        'finance',
        'leadership',
        'personal_development',
        'network_building'
    ];

    private function __construct(
        private readonly string $value
    ) {
        if (!in_array($value, self::VALID_CATEGORIES)) {
            throw new InvalidArgumentException("Invalid category: {$value}");
        }
    }

    public static function fromString(string $value): self
    {
        return new self(strtolower($value));
    }

    public static function business(): self
    {
        return new self('business');
    }

    public static function marketing(): self
    {
        return new self('marketing');
    }

    public static function finance(): self
    {
        return new self('finance');
    }

    public static function leadership(): self
    {
        return new self('leadership');
    }

    public static function personalDevelopment(): self
    {
        return new self('personal_development');
    }

    public static function networkBuilding(): self
    {
        return new self('network_building');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match($this->value) {
            'business' => 'Business Fundamentals',
            'marketing' => 'Marketing & Sales',
            'finance' => 'Financial Management',
            'leadership' => 'Leadership',
            'personal_development' => 'Personal Development',
            'network_building' => 'Network Building',
        };
    }

    public function equals(ResourceCategory $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
