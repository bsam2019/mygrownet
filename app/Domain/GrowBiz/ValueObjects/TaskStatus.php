<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\ValueObjects;

use InvalidArgumentException;

final class TaskStatus
{
    private const PENDING = 'pending';
    private const IN_PROGRESS = 'in_progress';
    private const ON_HOLD = 'on_hold';
    private const COMPLETED = 'completed';
    private const CANCELLED = 'cancelled';
    private const DONE = 'done';

    private const VALID_STATUSES = [
        self::PENDING,
        self::IN_PROGRESS,
        self::ON_HOLD,
        self::COMPLETED,
        self::CANCELLED,
        self::DONE,
    ];

    private function __construct(private string $value)
    {
        if ($value === self::DONE) {
            $value = self::COMPLETED;
        }
        
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid task status: {$value}");
        }
        
        $this->value = $value;
    }

    public static function pending(): self { return new self(self::PENDING); }
    public static function inProgress(): self { return new self(self::IN_PROGRESS); }
    public static function onHold(): self { return new self(self::ON_HOLD); }
    public static function completed(): self { return new self(self::COMPLETED); }
    public static function cancelled(): self { return new self(self::CANCELLED); }
    public static function done(): self { return new self(self::COMPLETED); }
    public static function fromString(string $status): self { return new self($status); }

    public function getValue(): string { return $this->value; }
    public function value(): string { return $this->value; }
    public function isPending(): bool { return $this->value === self::PENDING; }
    public function isInProgress(): bool { return $this->value === self::IN_PROGRESS; }
    public function isOnHold(): bool { return $this->value === self::ON_HOLD; }
    public function isCompleted(): bool { return $this->value === self::COMPLETED; }
    public function isCancelled(): bool { return $this->value === self::CANCELLED; }
    public function isDone(): bool { return $this->value === self::COMPLETED; }
    public function equals(self $other): bool { return $this->value === $other->value; }

    public static function validStatuses(): array
    {
        return [self::PENDING, self::IN_PROGRESS, self::ON_HOLD, self::COMPLETED, self::CANCELLED];
    }

    public static function all(): array
    {
        return [
            ['value' => self::PENDING, 'label' => 'Pending'],
            ['value' => self::IN_PROGRESS, 'label' => 'In Progress'],
            ['value' => self::ON_HOLD, 'label' => 'On Hold'],
            ['value' => self::COMPLETED, 'label' => 'Completed'],
            ['value' => self::CANCELLED, 'label' => 'Cancelled'],
        ];
    }
}
