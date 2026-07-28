<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

class IntercompanyTransaction
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $fromOrgId,
        public readonly int $toOrgId,
        public readonly string $transactionType,
        public readonly ?string $reference,
        public readonly ?string $description,
        public readonly float $amount,
        public readonly string $currency = 'ZMW',
        public readonly float $exchangeRate = 1.0,
        public readonly array $mapping = [],
        public readonly string $status = 'pending',
        public readonly ?int $matchedTransactionId = null,
        public readonly string $transactionDate,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public static function create(
        int $fromOrgId,
        int $toOrgId,
        string $transactionType,
        float $amount,
        string $currency = 'ZMW',
        ?string $reference = null,
        ?string $description = null,
        array $mapping = [],
        ?string $transactionDate = null,
    ): self {
        return new self(
            id: null,
            fromOrgId: $fromOrgId,
            toOrgId: $toOrgId,
            transactionType: $transactionType,
            reference: $reference,
            description: $description,
            amount: $amount,
            currency: $currency,
            mapping: $mapping,
            transactionDate: $transactionDate ?? date('Y-m-d'),
        );
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            fromOrgId: $data['from_org_id'],
            toOrgId: $data['to_org_id'],
            transactionType: $data['transaction_type'],
            reference: $data['reference'] ?? null,
            description: $data['description'] ?? null,
            amount: (float)$data['amount'],
            currency: $data['currency'] ?? 'ZMW',
            exchangeRate: (float)($data['exchange_rate'] ?? 1.0),
            mapping: json_decode($data['mapping'] ?? '[]', true),
            status: $data['status'] ?? 'pending',
            matchedTransactionId: $data['matched_transaction_id'] ?? null,
            transactionDate: $data['transaction_date'],
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'from_org_id' => $this->fromOrgId,
            'to_org_id' => $this->toOrgId,
            'transaction_type' => $this->transactionType,
            'reference' => $this->reference,
            'description' => $this->description,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'exchange_rate' => $this->exchangeRate,
            'mapping' => json_encode($this->mapping),
            'status' => $this->status,
            'matched_transaction_id' => $this->matchedTransactionId,
            'transaction_date' => $this->transactionDate,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function match(int $matchingTransactionId): self
    {
        return new self(
            id: $this->id,
            fromOrgId: $this->fromOrgId,
            toOrgId: $this->toOrgId,
            transactionType: $this->transactionType,
            reference: $this->reference,
            description: $this->description,
            amount: $this->amount,
            currency: $this->currency,
            exchangeRate: $this->exchangeRate,
            mapping: $this->mapping,
            status: 'matched',
            matchedTransactionId: $matchingTransactionId,
            transactionDate: $this->transactionDate,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function eliminate(): self
    {
        return new self(
            id: $this->id,
            fromOrgId: $this->fromOrgId,
            toOrgId: $this->toOrgId,
            transactionType: $this->transactionType,
            reference: $this->reference,
            description: $this->description,
            amount: $this->amount,
            currency: $this->currency,
            exchangeRate: $this->exchangeRate,
            mapping: $this->mapping,
            status: 'eliminated',
            matchedTransactionId: $this->matchedTransactionId,
            transactionDate: $this->transactionDate,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }
}
