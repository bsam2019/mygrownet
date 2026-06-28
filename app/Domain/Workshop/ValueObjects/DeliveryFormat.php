<?php

namespace App\Domain\Workshop\ValueObjects;

class DeliveryFormat
{
    private function __construct(
        private readonly string $value
    ) {
        if (!in_array($value, ['online', 'physical', 'hybrid'])) {
            throw new \InvalidArgumentException("Invalid delivery format: {$value}");
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match($this->value) {
            'online' => 'Online',
            'physical' => 'Physical',
            'hybrid' => 'Hybrid',
        };
    }

    public function isOnline(): bool
    {
        return $this->value === 'online' || $this->value === 'hybrid';
    }

    public function isPhysical(): bool
    {
        return $this->value === 'physical' || $this->value === 'hybrid';
    }
}
