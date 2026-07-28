<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\ValueObjects\JournalStatus;
use DateTimeImmutable;

class JournalEntry
{
    public readonly ?int $id;
    public readonly int $businessId;
    public readonly ?string $journalNumber;
    public readonly ?DateTimeImmutable $date;
    public readonly ?string $description;
    public readonly ?string $reference;
    public readonly JournalStatus $status;
    public readonly ?int $reversalOfId;
    public readonly ?string $reversalReason;
    public readonly ?string $sourceEventId;
    public readonly ?int $periodId;
    public readonly string $currencyCode;
    public readonly float $exchangeRate;
    public readonly ?float $functionalAmount;
    public readonly ?int $createdBy;
    public readonly ?DateTimeImmutable $postedAt;
    public readonly ?array $dimensions;
    public readonly ?DateTimeImmutable $createdAt;
    public readonly ?DateTimeImmutable $updatedAt;

    /** @var JournalLine[]|null */
    private ?array $lines = null;

    public function __construct(
        ?int $id,
        int $businessId,
        ?string $journalNumber,
        ?DateTimeImmutable $date,
        ?string $description,
        ?string $reference,
        JournalStatus $status = JournalStatus::DRAFT,
        ?int $reversalOfId = null,
        ?string $reversalReason = null,
        ?string $sourceEventId = null,
        ?int $periodId = null,
        string $currencyCode = 'ZMW',
        float $exchangeRate = 1.0,
        ?float $functionalAmount = null,
        ?int $createdBy = null,
        ?DateTimeImmutable $postedAt = null,
        ?array $dimensions = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->id = $id;
        $this->businessId = $businessId;
        $this->journalNumber = $journalNumber;
        $this->date = $date;
        $this->description = $description;
        $this->reference = $reference;
        $this->status = $status;
        $this->reversalOfId = $reversalOfId;
        $this->reversalReason = $reversalReason;
        $this->sourceEventId = $sourceEventId;
        $this->periodId = $periodId;
        $this->currencyCode = $currencyCode;
        $this->exchangeRate = $exchangeRate;
        $this->functionalAmount = $functionalAmount;
        $this->createdBy = $createdBy;
        $this->postedAt = $postedAt;
        $this->dimensions = $dimensions;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: (int) $data['business_id'],
            journalNumber: $data['journal_number'] ?? null,
            date: isset($data['date']) ? new DateTimeImmutable($data['date']) : null,
            description: $data['description'] ?? null,
            reference: $data['reference'] ?? null,
            status: JournalStatus::from($data['status'] ?? 'draft'),
            reversalOfId: isset($data['reversal_of_id']) ? (int) $data['reversal_of_id'] : null,
            reversalReason: $data['reversal_reason'] ?? null,
            sourceEventId: $data['source_event_id'] ?? null,
            periodId: isset($data['period_id']) ? (int) $data['period_id'] : null,
            currencyCode: $data['currency_code'] ?? 'ZMW',
            exchangeRate: (float) ($data['exchange_rate'] ?? 1.0),
            functionalAmount: isset($data['functional_amount']) ? (float) $data['functional_amount'] : null,
            createdBy: $data['created_by'] ?? null,
            postedAt: isset($data['posted_at']) ? new DateTimeImmutable($data['posted_at']) : null,
            dimensions: isset($data['dimensions_json']) ? (is_string($data['dimensions_json']) ? json_decode($data['dimensions_json'], true) : $data['dimensions_json']) : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function isBalanced(): bool
    {
        if ($this->lines === null) {
            return true;
        }

        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($this->lines as $line) {
            $totalDebit += $line->debitAmount;
            $totalCredit += $line->creditAmount;
        }

        return abs($totalDebit - $totalCredit) < 0.01;
    }

    public function post(DateTimeImmutable $postedAt): self
    {
        if (!$this->status->isPostable()) {
            throw new \DomainException('Only draft entries can be posted');
        }

        if (!$this->isBalanced()) {
            throw new \DomainException('Cannot post an unbalanced journal entry');
        }

        return new self(
            id: $this->id,
            businessId: $this->businessId,
            journalNumber: $this->journalNumber,
            date: $this->date,
            description: $this->description,
            reference: $this->reference,
            status: JournalStatus::POSTED,
            reversalOfId: $this->reversalOfId,
            reversalReason: $this->reversalReason,
            sourceEventId: $this->sourceEventId,
            periodId: $this->periodId,
            currencyCode: $this->currencyCode,
            exchangeRate: $this->exchangeRate,
            functionalAmount: $this->functionalAmount,
            createdBy: $this->createdBy,
            postedAt: $postedAt,
            dimensions: $this->dimensions,
            createdAt: $this->createdAt,
            updatedAt: null,
        );
    }

    public function reverse(string $reason, DateTimeImmutable $now): self
    {
        if (!$this->status->isReversible()) {
            throw new \DomainException('Only posted entries can be reversed');
        }

        return new self(
            id: $this->id,
            businessId: $this->businessId,
            journalNumber: $this->journalNumber,
            date: $this->date,
            description: $this->description,
            reference: $this->reference,
            status: JournalStatus::POSTED,
            reversalOfId: $this->reversalOfId,
            reversalReason: $reason,
            currencyCode: $this->currencyCode,
            exchangeRate: $this->exchangeRate,
            functionalAmount: $this->functionalAmount,
            sourceEventId: $this->sourceEventId,
            periodId: $this->periodId,
            createdBy: $this->createdBy,
            postedAt: $this->postedAt,
            dimensions: $this->dimensions,
            createdAt: $this->createdAt,
            updatedAt: $now,
        );
    }

    public function setLines(array $lines): void
    {
        $this->lines = $lines;
    }

    public function getLines(): ?array
    {
        return $this->lines;
    }

    public function totalDebit(): float
    {
        if ($this->lines === null) {
            return 0.0;
        }
        return array_sum(array_map(fn(JournalLine $l) => $l->debitAmount, $this->lines));
    }

    public function totalCredit(): float
    {
        if ($this->lines === null) {
            return 0.0;
        }
        return array_sum(array_map(fn(JournalLine $l) => $l->creditAmount, $this->lines));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'journal_number' => $this->journalNumber,
            'date' => $this->date?->format('Y-m-d'),
            'description' => $this->description,
            'reference' => $this->reference,
            'status' => $this->status->value,
            'reversal_of_id' => $this->reversalOfId,
            'reversal_reason' => $this->reversalReason,
            'source_event_id' => $this->sourceEventId,
            'period_id' => $this->periodId,
            'currency_code' => $this->currencyCode,
            'exchange_rate' => $this->exchangeRate,
            'functional_amount' => $this->functionalAmount,
            'created_by' => $this->createdBy,
            'posted_at' => $this->postedAt?->format('Y-m-d H:i:s'),
            'dimensions_json' => $this->dimensions ? json_encode($this->dimensions) : null,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
