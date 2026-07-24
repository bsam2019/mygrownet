<?php

namespace App\Domain\BizBoost\Entities;

class ClientWallet
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly float $balance,
        public readonly float $lockedBalance,
        public readonly string $currency,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) $data['user_id'],
            balance: (float) ($data['balance'] ?? 0),
            lockedBalance: (float) ($data['locked_balance'] ?? 0),
            currency: $data['currency'] ?? 'ZMW',
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'balance' => $this->balance,
            'locked_balance' => $this->lockedBalance,
            'currency' => $this->currency,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}