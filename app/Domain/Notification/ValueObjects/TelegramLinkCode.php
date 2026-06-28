<?php

namespace App\Domain\Notification\ValueObjects;

class TelegramLinkCode
{
    private function __construct(
        private readonly string $value
    ) {
        if (!preg_match('/^[A-Z0-9]{8}$/', $value)) {
            throw new \InvalidArgumentException('Invalid Telegram link code format');
        }
    }

    public static function generate(): self
    {
        $code = strtoupper(substr(md5(uniqid() . time()), 0, 8));
        return new self($code);
    }

    public static function fromString(string $code): self
    {
        return new self(strtoupper($code));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
