<?php

namespace App\Domain\GrowFinance\Entities;

class ApiToken
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $name,
        public readonly string $token,
        public readonly array $abilities = [],
        public readonly ?\DateTimeImmutable $lastUsedAt = null,
        public readonly ?\DateTimeImmutable $expiresAt = null,
        public readonly bool $isActive = true,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public function hasAbility(string $ability): bool
    {
        return in_array($ability, $this->abilities, true);
    }

    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        return $this->expiresAt < new \DateTimeImmutable();
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            token: $data['token'],
            abilities: isset($data['abilities']) ? (is_array($data['abilities']) ? $data['abilities'] : json_decode($data['abilities'], true)) : [],
            lastUsedAt: isset($data['last_used_at']) ? new \DateTimeImmutable($data['last_used_at']) : null,
            expiresAt: isset($data['expires_at']) ? new \DateTimeImmutable($data['expires_at']) : null,
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'name' => $this->name,
            'token' => $this->token,
            'abilities' => $this->abilities,
            'last_used_at' => $this->lastUsedAt?->format('Y-m-d H:i:s'),
            'expires_at' => $this->expiresAt?->format('Y-m-d H:i:s'),
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}