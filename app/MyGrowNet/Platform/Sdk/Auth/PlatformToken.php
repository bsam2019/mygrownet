<?php

namespace MyGrowNet\Platform\Sdk\Auth;

class PlatformToken
{
    public function __construct(
        public readonly string $token,
        public readonly string $type = 'Bearer',
        public readonly ?\DateTimeImmutable $expiresAt = null,
    ) {}

    public function isValid(): bool
    {
        return $this->expiresAt === null || $this->expiresAt > new \DateTimeImmutable();
    }

    public function headerValue(): string
    {
        return "{$this->type} {$this->token}";
    }
}
