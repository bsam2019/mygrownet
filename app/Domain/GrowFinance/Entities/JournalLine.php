<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use DateTimeImmutable;

class JournalLine
{
    public readonly ?int $id;
    public readonly int $journalEntryId;
    public readonly int $accountId;
    public readonly float $debitAmount;
    public readonly float $creditAmount;
    public readonly ?float $functionalDebitAmount;
    public readonly ?float $functionalCreditAmount;
    public readonly ?string $description;
    public readonly ?array $dimensions;
    public readonly ?DateTimeImmutable $createdAt;
    public readonly ?DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        int $journalEntryId,
        int $accountId,
        float $debitAmount,
        float $creditAmount,
        ?float $functionalDebitAmount = null,
        ?float $functionalCreditAmount = null,
        ?string $description = null,
        ?array $dimensions = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->id = $id;
        $this->journalEntryId = $journalEntryId;
        $this->accountId = $accountId;
        $this->debitAmount = $debitAmount;
        $this->creditAmount = $creditAmount;
        $this->functionalDebitAmount = $functionalDebitAmount;
        $this->functionalCreditAmount = $functionalCreditAmount;
        $this->description = $description;
        $this->dimensions = $dimensions;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            journalEntryId: (int) $data['journal_entry_id'],
            accountId: (int) $data['account_id'],
            debitAmount: (float) ($data['debit_amount'] ?? 0),
            creditAmount: (float) ($data['credit_amount'] ?? 0),
            functionalDebitAmount: isset($data['functional_debit_amount']) ? (float) $data['functional_debit_amount'] : null,
            functionalCreditAmount: isset($data['functional_credit_amount']) ? (float) $data['functional_credit_amount'] : null,
            description: $data['description'] ?? null,
            dimensions: isset($data['dimensions_json']) ? (is_string($data['dimensions_json']) ? json_decode($data['dimensions_json'], true) : $data['dimensions_json']) : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'journal_entry_id' => $this->journalEntryId,
            'account_id' => $this->accountId,
            'debit_amount' => $this->debitAmount,
            'credit_amount' => $this->creditAmount,
            'functional_debit_amount' => $this->functionalDebitAmount,
            'functional_credit_amount' => $this->functionalCreditAmount,
            'description' => $this->description,
            'dimensions_json' => $this->dimensions ? json_encode($this->dimensions) : null,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
