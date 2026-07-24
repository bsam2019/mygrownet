<?php

namespace App\Domain\GrowFinance\Entities;

class BankStatementLine
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $statementId,
        public readonly ?\DateTimeImmutable $transactionDate = null,
        public readonly ?string $description = null,
        public readonly ?string $reference = null,
        public readonly ?float $debitAmount = null,
        public readonly ?float $creditAmount = null,
        public readonly ?float $runningBalance = null,
        public readonly ?string $status = null,
        public readonly ?string $notes = null,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public function getAmount(): float
    {
        if ($this->creditAmount !== null && $this->creditAmount > 0) {
            return $this->creditAmount;
        }

        return $this->debitAmount !== null ? -$this->debitAmount : 0.0;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            statementId: (int) $data['statement_id'],
            transactionDate: isset($data['transaction_date']) ? new \DateTimeImmutable($data['transaction_date']) : null,
            description: $data['description'] ?? null,
            reference: $data['reference'] ?? null,
            debitAmount: array_key_exists('debit_amount', $data) ? (float) $data['debit_amount'] : null,
            creditAmount: array_key_exists('credit_amount', $data) ? (float) $data['credit_amount'] : null,
            runningBalance: array_key_exists('running_balance', $data) ? (float) $data['running_balance'] : null,
            status: $data['status'] ?? null,
            notes: $data['notes'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'statement_id' => $this->statementId,
            'transaction_date' => $this->transactionDate?->format('Y-m-d H:i:s'),
            'description' => $this->description,
            'reference' => $this->reference,
            'debit_amount' => $this->debitAmount,
            'credit_amount' => $this->creditAmount,
            'running_balance' => $this->runningBalance,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}