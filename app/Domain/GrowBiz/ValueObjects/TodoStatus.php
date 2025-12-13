<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\ValueObjects;

class TodoStatus
{
    private const PENDING = 'pending';
    private const IN_PROGRESS = 'in_progress';
    private const COMPLETED = 'completed';

    private function __construct(private string $value) {}

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function inProgress(): self
    {
        return new self(self::IN_PROGRESS);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::PENDING => self::pending(),
            self::IN_PROGRESS => self::inProgress(),
            self::COMPLETED => self::completed(),
            default => throw new \InvalidArgumentException("Invalid todo status: {$value}"),
        };
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isInProgress(): bool
    {
        return $this->value === self::IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->value === self::COMPLETED;
    }

    public static function all(): array
    {
        return [
            ['value' => self::PENDING, 'label' => 'Pending'],
            ['value' => self::IN_PROGRESS, 'label' => 'In Progress'],
            ['value' => self::COMPLETED, 'label' => 'Completed'],
        ];
    }
}
