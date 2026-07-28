<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use DateTimeImmutable;

class DepreciationEntry
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $assetId,
        public readonly DateTimeImmutable $periodDate,
        public readonly float $depreciationAmount,
        public readonly float $accumulatedDepreciation,
        public readonly float $netBookValue,
        public readonly ?int $journalEntryId = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            assetId: (int) $data['asset_id'],
            periodDate: new DateTimeImmutable($data['period_date']),
            depreciationAmount: (float) ($data['depreciation_amount'] ?? 0),
            accumulatedDepreciation: (float) ($data['accumulated_depreciation'] ?? 0),
            netBookValue: (float) ($data['net_book_value'] ?? 0),
            journalEntryId: isset($data['journal_entry_id']) ? (int) $data['journal_entry_id'] : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'asset_id' => $this->assetId,
            'period_date' => $this->periodDate->format('Y-m-d'),
            'depreciation_amount' => $this->depreciationAmount,
            'accumulated_depreciation' => $this->accumulatedDepreciation,
            'net_book_value' => $this->netBookValue,
            'journal_entry_id' => $this->journalEntryId,
        ];
    }
}
