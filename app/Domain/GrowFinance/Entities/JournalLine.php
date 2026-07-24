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
    public readonly ?string $description;
    public readonly ?DateTimeImmutable $createdAt;
    public readonly ?DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        int $journalEntryId,
        int $accountId,
        float $debitAmount,
        float $creditAmount,
        ?string $description,
        ?DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ) {
        $this->id = $id;
        $this->journalEntryId = $journalEntryId;
        $this->accountId = $accountId;
        $this->debitAmount = $debitAmount;
        $this->creditAmount = $creditAmount;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            journalEntryId: $data['journal_entry_id'],
            accountId: $data['account_id'],
            debitAmount: (float) ($data['debit_amount'] ?? 0),
            creditAmount: (float) ($data['credit_amount'] ?? 0),
            description: $data['description'] ?? null,
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
            'description' => $this->description,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
