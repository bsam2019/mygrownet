<?php

namespace App\Domain\Marketplace\ValueObjects;

use InvalidArgumentException;

final class TrustLevel
{
    private const NEW = 'new';
    private const VERIFIED = 'verified';
    private const TRUSTED = 'trusted';
    private const TOP = 'top';

    private const VALID_LEVELS = [
        self::NEW,
        self::VERIFIED,
        self::TRUSTED,
        self::TOP,
    ];

    private function __construct(private readonly string $level)
    {
        if (!in_array($level, self::VALID_LEVELS, true)) {
            throw new InvalidArgumentException("Invalid trust level: {$level}");
        }
    }

    public static function new(): self { return new self(self::NEW); }
    public static function verified(): self { return new self(self::VERIFIED); }
    public static function trusted(): self { return new self(self::TRUSTED); }
    public static function top(): self { return new self(self::TOP); }

    public static function fromString(string $level): self
    {
        return new self($level);
    }

    public function value(): string { return $this->level; }
    public function isNew(): bool { return $this->level === self::NEW; }
    public function isVerified(): bool { return $this->level === self::VERIFIED; }
    public function isTrusted(): bool { return $this->level === self::TRUSTED; }
    public function isTop(): bool { return $this->level === self::TOP; }

    public function label(): string
    {
        return match ($this->level) {
            self::NEW => 'New Seller',
            self::VERIFIED => 'Verified Seller',
            self::TRUSTED => 'Trusted Seller',
            self::TOP => 'Top Seller',
        };
    }

    public function badge(): string
    {
        return match ($this->level) {
            self::NEW => '🆕',
            self::VERIFIED => '✓',
            self::TRUSTED => '⭐',
            self::TOP => '👑',
        };
    }

    public function color(): string
    {
        return match ($this->level) {
            self::NEW => 'gray',
            self::VERIFIED => 'blue',
            self::TRUSTED => 'green',
            self::TOP => 'amber',
        };
    }
}
