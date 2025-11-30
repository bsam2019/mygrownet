<?php

namespace App\Domain\Support\ValueObjects;

class TicketContent
{
    private function __construct(private string $value, bool $skipValidation = false)
    {
        $trimmed = trim($value);
        
        // Skip validation for data loaded from database (already validated on creation)
        if ($skipValidation) {
            $this->value = $trimmed ?: '';
            return;
        }
        
        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Message cannot be empty');
        }

        // Minimum 1 character for all content (chat messages and tickets)
        // The frontend can enforce stricter validation if needed
        if (strlen($trimmed) < 1) {
            throw new \InvalidArgumentException('Message must be at least 1 character');
        }

        if (strlen($trimmed) > 5000) {
            throw new \InvalidArgumentException('Message cannot exceed 5000 characters');
        }

        $this->value = $trimmed;
    }

    /**
     * Create from user input (with validation)
     */
    public static function fromString(string $value): self
    {
        return new self($value, false);
    }

    /**
     * Create from database (skip validation - data already validated on creation)
     */
    public static function fromDatabase(string $value): self
    {
        return new self($value, true);
    }

    /**
     * Create from a chat message (allows shorter content)
     */
    public static function fromChatMessage(string $value): self
    {
        return new self($value, false);
    }

    public function value(): string
    {
        return $this->value;
    }
}
