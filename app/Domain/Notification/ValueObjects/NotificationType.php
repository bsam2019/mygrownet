<?php

namespace App\Domain\Notification\ValueObjects;

class NotificationType
{
    private function __construct(
        private readonly string $value
    ) {
        $this->validate();
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function category(): string
    {
        // Extract category from type (e.g., 'wallet.topup' -> 'wallet')
        return explode('.', $this->value)[0];
    }

    private function validate(): void
    {
        if (empty($this->value)) {
            throw new \InvalidArgumentException('Notification type cannot be empty');
        }

        if (!str_contains($this->value, '.')) {
            throw new \InvalidArgumentException('Notification type must be in format: category.action');
        }
    }

    public function equals(NotificationType $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
