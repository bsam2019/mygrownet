<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use DateTimeImmutable;

class BankConnection
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $bankName,
        public readonly string $accountName,
        public readonly string $accountNumber,
        public readonly string $connectionType,
        public readonly string $status,
        public readonly ?string $lastSyncAt = null,
        public readonly ?array $credentials = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: (int) $data['business_id'],
            bankName: $data['bank_name'],
            accountName: $data['account_name'],
            accountNumber: $data['account_number'],
            connectionType: $data['connection_type'],
            status: $data['status'] ?? 'active',
            lastSyncAt: $data['last_sync_at'] ?? null,
            credentials: isset($data['credentials']) ? (array) json_decode($data['credentials'], true) : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'bank_name' => $this->bankName,
            'account_name' => $this->accountName,
            'account_number' => $this->accountNumber,
            'connection_type' => $this->connectionType,
            'status' => $this->status,
            'last_sync_at' => $this->lastSyncAt,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
