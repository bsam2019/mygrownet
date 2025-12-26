<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\ValueObjects;

final class SitePlan
{
    private const STARTER = 'starter';
    private const BUSINESS = 'business';
    private const PRO = 'pro';

    private const LIMITS = [
        self::STARTER => [
            'pages' => 5,
            'products' => 10,
            'storage_mb' => 100,
            'custom_domain' => false,
            'remove_branding' => false,
            'analytics' => false,
        ],
        self::BUSINESS => [
            'pages' => 20,
            'products' => 50,
            'storage_mb' => 500,
            'custom_domain' => true,
            'remove_branding' => true,
            'analytics' => true,
        ],
        self::PRO => [
            'pages' => -1, // unlimited
            'products' => -1,
            'storage_mb' => 2000,
            'custom_domain' => true,
            'remove_branding' => true,
            'analytics' => true,
        ],
    ];

    private function __construct(private string $value) {}

    public static function starter(): self
    {
        return new self(self::STARTER);
    }

    public static function business(): self
    {
        return new self(self::BUSINESS);
    }

    public static function pro(): self
    {
        return new self(self::PRO);
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::STARTER => self::starter(),
            self::BUSINESS => self::business(),
            self::PRO => self::pro(),
            default => throw new \InvalidArgumentException("Invalid site plan: {$value}"),
        };
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isStarter(): bool
    {
        return $this->value === self::STARTER;
    }

    public function isBusiness(): bool
    {
        return $this->value === self::BUSINESS;
    }

    public function isPro(): bool
    {
        return $this->value === self::PRO;
    }

    public function getLimits(): array
    {
        return self::LIMITS[$this->value];
    }

    public function getPageLimit(): int
    {
        return self::LIMITS[$this->value]['pages'];
    }

    public function getProductLimit(): int
    {
        return self::LIMITS[$this->value]['products'];
    }

    public function getStorageLimitMb(): int
    {
        return self::LIMITS[$this->value]['storage_mb'];
    }

    public function canUseCustomDomain(): bool
    {
        return self::LIMITS[$this->value]['custom_domain'];
    }

    public function canRemoveBranding(): bool
    {
        return self::LIMITS[$this->value]['remove_branding'];
    }

    public function hasAnalytics(): bool
    {
        return self::LIMITS[$this->value]['analytics'];
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
