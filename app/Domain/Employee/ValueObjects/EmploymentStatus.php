<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObjects;

use App\Domain\Employee\Exceptions\InvalidEmploymentStatusException;
use DateTimeImmutable;

final class EmploymentStatus
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';
    public const TERMINATED = 'terminated';
    public const SUSPENDED = 'suspended';

    private const VALID_STATUSES = [
        self::ACTIVE,
        self::INACTIVE,
        self::TERMINATED,
        self::SUSPENDED,
    ];

    private const VALID_TRANSITIONS = [
        self::ACTIVE => [self::INACTIVE, self::TERMINATED, self::SUSPENDED],
        self::INACTIVE => [self::ACTIVE, self::TERMINATED],
        self::SUSPENDED => [self::ACTIVE, self::TERMINATED],
        self::TERMINATED => [], // Terminal state - no transitions allowed
    ];

    private string $status;
    private ?string $reason;
    private ?DateTimeImmutable $effectiveDate;

    private function __construct(string $status, ?string $reason = null, ?DateTimeImmutable $effectiveDate = null)
    {
        $this->validateStatus($status);
        $this->status = $status;
        $this->reason = $reason;
        $this->effectiveDate = $effectiveDate ?? new DateTimeImmutable();
    }

    public static function active(?string $reason = null, ?DateTimeImmutable $effectiveDate = null): self
    {
        return new self(self::ACTIVE, $reason, $effectiveDate);
    }

    public static function inactive(string $reason, ?DateTimeImmutable $effectiveDate = null): self
    {
        return new self(self::INACTIVE, $reason, $effectiveDate);
    }

    public static function terminated(string $reason, ?DateTimeImmutable $effectiveDate = null): self
    {
        return new self(self::TERMINATED, $reason, $effectiveDate);
    }

    public static function suspended(string $reason, ?DateTimeImmutable $effectiveDate = null): self
    {
        return new self(self::SUSPENDED, $reason, $effectiveDate);
    }

    public static function fromString(string $status, ?string $reason = null, ?DateTimeImmutable $effectiveDate = null): self
    {
        return new self($status, $reason, $effectiveDate);
    }

    public function transitionTo(string $newStatus, string $reason, ?DateTimeImmutable $effectiveDate = null): self
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw InvalidEmploymentStatusException::invalidTransition($this->status, $newStatus);
        }

        return new self($newStatus, $reason, $effectiveDate ?? new DateTimeImmutable());
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $this->validateStatus($newStatus);
        
        if (!isset(self::VALID_TRANSITIONS[$this->status])) {
            return false;
        }

        return in_array($newStatus, self::VALID_TRANSITIONS[$this->status], true);
    }

    public function isActive(): bool
    {
        return $this->status === self::ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->status === self::INACTIVE;
    }

    public function isTerminated(): bool
    {
        return $this->status === self::TERMINATED;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::SUSPENDED;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getEffectiveDate(): ?DateTimeImmutable
    {
        return $this->effectiveDate;
    }

    public function equals(EmploymentStatus $other): bool
    {
        return $this->status === $other->status
            && $this->reason === $other->reason
            && $this->effectiveDate?->format('Y-m-d H:i:s') === $other->effectiveDate?->format('Y-m-d H:i:s');
    }

    public function toString(): string
    {
        return $this->status;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    private function validateStatus(string $status): void
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw InvalidEmploymentStatusException::invalidStatus($status);
        }
    }

    public static function getValidStatuses(): array
    {
        return self::VALID_STATUSES;
    }

    public function getValidTransitions(): array
    {
        return self::VALID_TRANSITIONS[$this->status] ?? [];
    }
}