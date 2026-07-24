<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

class ReconciliationMatch
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $reconciliationPeriodId,
        public readonly ?int $statementLineId,
        public readonly ?int $journalLineId,
        public readonly ?float $statementAmount,
        public readonly ?float $journalAmount,
        public readonly ?string $matchType,
        public readonly ?\DateTimeImmutable $createdAt,
        public readonly ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            reconciliationPeriodId: (int) $data['reconciliation_period_id'],
            statementLineId: isset($data['statement_line_id']) ? (int) $data['statement_line_id'] : null,
            journalLineId: isset($data['journal_line_id']) ? (int) $data['journal_line_id'] : null,
            statementAmount: isset($data['statement_amount']) ? (float) $data['statement_amount'] : null,
            journalAmount: isset($data['journal_amount']) ? (float) $data['journal_amount'] : null,
            matchType: $data['match_type'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'reconciliation_period_id' => $this->reconciliationPeriodId,
            'statement_line_id' => $this->statementLineId,
            'journal_line_id' => $this->journalLineId,
            'statement_amount' => $this->statementAmount,
            'journal_amount' => $this->journalAmount,
            'match_type' => $this->matchType,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
