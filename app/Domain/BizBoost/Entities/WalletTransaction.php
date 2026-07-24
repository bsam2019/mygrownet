<?php

namespace App\Domain\BizBoost\Entities;

class WalletTransaction
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $walletId,
        public readonly string $type,
        public readonly float $amount,
        public readonly float $balanceBefore,
        public readonly float $balanceAfter,
        public readonly ?string $currency,
        public readonly ?string $reference,
        public readonly ?string $description,
        public readonly ?string $payableType,
        public readonly ?int $payableId,
        public readonly string $status,
        public readonly ?array $meta,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            walletId: (int) $data['wallet_id'],
            type: $data['type'],
            amount: (float) ($data['amount'] ?? 0),
            balanceBefore: (float) ($data['balance_before'] ?? 0),
            balanceAfter: (float) ($data['balance_after'] ?? 0),
            currency: $data['currency'] ?? null,
            reference: $data['reference'] ?? null,
            description: $data['description'] ?? null,
            payableType: $data['payable_type'] ?? null,
            payableId: isset($data['payable_id']) ? (int) $data['payable_id'] : null,
            status: $data['status'] ?? 'completed',
            meta: $data['meta'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->walletId,
            'type' => $this->type,
            'amount' => $this->amount,
            'balance_before' => $this->balanceBefore,
            'balance_after' => $this->balanceAfter,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'description' => $this->description,
            'payable_type' => $this->payableType,
            'payable_id' => $this->payableId,
            'status' => $this->status,
            'meta' => $this->meta,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}