<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\ValueObjects;

use InvalidArgumentException;

final class InvitationToken
{
    private function __construct(private string $token) {}

    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(32)));
    }

    public static function fromString(string $token): self
    {
        $token = trim($token);
        if (strlen($token) !== 64) {
            throw new InvalidArgumentException('Invalid invitation token format');
        }
        return new self($token);
    }

    public function value(): string
    {
        return $this->token;
    }

    public function equals(InvitationToken $other): bool
    {
        return hash_equals($this->token, $other->token);
    }

    public function __toString(): string
    {
        return $this->token;
    }
}
