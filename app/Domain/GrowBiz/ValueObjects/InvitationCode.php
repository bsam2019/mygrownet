<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\ValueObjects;

use InvalidArgumentException;

final class InvitationCode
{
    private function __construct(private string $code) {}

    public static function generate(): self
    {
        // Generate 6-character alphanumeric code (uppercase, easy to type)
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Removed confusing chars: I, O, 0, 1
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return new self($code);
    }

    public static function fromString(string $code): self
    {
        $code = strtoupper(trim($code));
        if (strlen($code) !== 6) {
            throw new InvalidArgumentException('Invitation code must be 6 characters');
        }
        return new self($code);
    }

    public function value(): string
    {
        return $this->code;
    }

    public function equals(InvitationCode $other): bool
    {
        return $this->code === $other->code;
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
