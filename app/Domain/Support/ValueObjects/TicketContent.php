<?php

namespace App\Domain\Support\ValueObjects;

class TicketContent
{
    private function __construct(private string $value)
    {
        $trimmed = trim($value);
        
        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Ticket description cannot be empty');
        }

        if (strlen($trimmed) < 10) {
            throw new \InvalidArgumentException('Ticket description must be at least 10 characters');
        }

        if (strlen($trimmed) > 5000) {
            throw new \InvalidArgumentException('Ticket description cannot exceed 5000 characters');
        }

        $this->value = $trimmed;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
