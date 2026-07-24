<?php

namespace App\Domain\VentureBuilder\ValueObjects;

use InvalidArgumentException;

final class VentureStatus
{
    private const DRAFT = 'draft';
    private const REVIEW = 'review';
    private const APPROVED = 'approved';
    private const FUNDING = 'funding';
    private const FUNDED = 'funded';
    private const ACTIVE = 'active';
    private const COMPLETED = 'completed';
    private const CANCELLED = 'cancelled';

    private const VALID = [self::DRAFT, self::REVIEW, self::APPROVED, self::FUNDING, self::FUNDED, self::ACTIVE, self::COMPLETED, self::CANCELLED];

    private const TRANSITIONS = [
        self::DRAFT => [self::REVIEW, self::CANCELLED],
        self::REVIEW => [self::APPROVED, self::DRAFT, self::CANCELLED],
        self::APPROVED => [self::FUNDING, self::CANCELLED],
        self::FUNDING => [self::FUNDED, self::CANCELLED],
        self::FUNDED => [self::ACTIVE, self::CANCELLED],
        self::ACTIVE => [self::COMPLETED],
        self::COMPLETED => [],
        self::CANCELLED => [],
    ];

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, self::VALID, true)) {
            throw new InvalidArgumentException("Invalid venture status: {$value}");
        }
    }

    public static function draft(): self { return new self(self::DRAFT); }
    public static function review(): self { return new self(self::REVIEW); }
    public static function approved(): self { return new self(self::APPROVED); }
    public static function funding(): self { return new self(self::FUNDING); }
    public static function funded(): self { return new self(self::FUNDED); }
    public static function active(): self { return new self(self::ACTIVE); }
    public static function completed(): self { return new self(self::COMPLETED); }
    public static function cancelled(): self { return new self(self::CANCELLED); }
    public static function fromString(string $value): self { return new self($value); }
    public function value(): string { return $this->value; }
    public function canTransitionTo(string $newStatus): bool { return in_array($newStatus, self::TRANSITIONS[$this->value] ?? [], true); }
    public function allowedTransitions(): array { return self::TRANSITIONS[$this->value] ?? []; }
    public function isFunding(): bool { return $this->value === self::FUNDING; }
    public function isFunded(): bool { return $this->value === self::FUNDED; }
    public function isActive(): bool { return $this->value === self::ACTIVE; }
    public function isDraft(): bool { return $this->value === self::DRAFT; }
    public function canAcceptInvestments(): bool { return $this->value === self::FUNDING; }
}
