<?php

declare(strict_types=1);

namespace App\Domain\MLM\ValueObjects;

class PerformanceBonusType
{
    private function __construct(private string $type)
    {
        $this->validate($type);
    }

    public static function teamVolume(): self
    {
        return new self('TEAM_VOLUME');
    }

    public static function leadership(): self
    {
        return new self('LEADERSHIP');
    }

    public static function profitBoost(): self
    {
        return new self('PROFIT_BOOST');
    }

    public static function fromString(string $type): self
    {
        return new self(strtoupper($type));
    }

    public function value(): string
    {
        return $this->type;
    }

    public function isTeamVolume(): bool
    {
        return $this->type === 'TEAM_VOLUME';
    }

    public function isLeadership(): bool
    {
        return $this->type === 'LEADERSHIP';
    }

    public function isProfitBoost(): bool
    {
        return $this->type === 'PROFIT_BOOST';
    }

    public function equals(PerformanceBonusType $other): bool
    {
        return $this->type === $other->type;
    }

    private function validate(string $type): void
    {
        $validTypes = ['TEAM_VOLUME', 'LEADERSHIP', 'PROFIT_BOOST'];
        
        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException("Invalid performance bonus type: {$type}");
        }
    }

    public function __toString(): string
    {
        return $this->type;
    }
}