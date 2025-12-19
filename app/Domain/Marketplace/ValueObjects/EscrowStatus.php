<?php

namespace App\Domain\Marketplace\ValueObjects;

use InvalidArgumentException;

final class EscrowStatus
{
    private const HELD = 'held';
    private const RELEASED = 'released';
    private const REFUNDED = 'refunded';
    private const DISPUTED = 'disputed';

    private const VALID_STATUSES = [
        self::HELD,
        self::RELEASED,
        self::REFUNDED,
        self::DISPUTED,
    ];

    private function __construct(private readonly string $status)
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid escrow status: {$status}");
        }
    }

    public static function held(): self { return new self(self::HELD); }
    public static function released(): self { return new self(self::RELEASED); }
    public static function refunded(): self { return new self(self::REFUNDED); }
    public static function disputed(): self { return new self(self::DISPUTED); }

    public static function fromString(string $status): self
    {
        return new self($status);
    }

    public function value(): string { return $this->status; }
    public function isHeld(): bool { return $this->status === self::HELD; }
    public function isReleased(): bool { return $this->status === self::RELEASED; }
    public function isRefunded(): bool { return $this->status === self::REFUNDED; }
    public function isDisputed(): bool { return $this->status === self::DISPUTED; }

    public function label(): string
    {
        return match ($this->status) {
            self::HELD => 'Funds Held',
            self::RELEASED => 'Funds Released',
            self::REFUNDED => 'Refunded',
            self::DISPUTED => 'Under Dispute',
        };
    }

    public function color(): string
    {
        return match ($this->status) {
            self::HELD => 'blue',
            self::RELEASED => 'green',
            self::REFUNDED => 'orange',
            self::DISPUTED => 'red',
        };
    }
}
