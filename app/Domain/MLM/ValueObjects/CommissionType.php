<?php

declare(strict_types=1);

namespace App\Domain\MLM\ValueObjects;

use InvalidArgumentException;

final class CommissionType
{
    private const REFERRAL = 'REFERRAL';
    private const TEAM_VOLUME = 'TEAM_VOLUME';
    private const PERFORMANCE = 'PERFORMANCE';

    private const VALID_TYPES = [
        self::REFERRAL,
        self::TEAM_VOLUME,
        self::PERFORMANCE,
    ];

    private function __construct(private string $value)
    {
        if (!in_array($value, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid commission type: %s. Valid types are: %s', 
                    $value, 
                    implode(', ', self::VALID_TYPES)
                )
            );
        }
    }

    public static function referral(): self
    {
        return new self(self::REFERRAL);
    }

    public static function teamVolume(): self
    {
        return new self(self::TEAM_VOLUME);
    }

    public static function performance(): self
    {
        return new self(self::PERFORMANCE);
    }

    public static function fromString(string $value): self
    {
        return new self(strtoupper($value));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(CommissionType $other): bool
    {
        return $this->value === $other->value;
    }

    public function isReferral(): bool
    {
        return $this->value === self::REFERRAL;
    }

    public function isTeamVolume(): bool
    {
        return $this->value === self::TEAM_VOLUME;
    }

    public function isPerformance(): bool
    {
        return $this->value === self::PERFORMANCE;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}