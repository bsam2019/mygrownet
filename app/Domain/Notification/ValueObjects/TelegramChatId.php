<?php

namespace App\Domain\Notification\ValueObjects;

class TelegramChatId
{
    private function __construct(
        private readonly string $value
    ) {
        if (empty($value)) {
            throw new \InvalidArgumentException('Telegram chat ID cannot be empty');
        }
    }

    public static function fromString(string $chatId): self
    {
        return new self($chatId);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(TelegramChatId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
