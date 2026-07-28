<?php

namespace App\Domain\GrowFinance\Events;

class JournalPosted
{
    public const NAME = 'growfinance.journal.posted.v1';

    public function __construct(
        public readonly int $journalId,
        public readonly int $companyId,
        public readonly float $totalDebit,
        public readonly float $totalCredit,
        public readonly string $currency,
        public readonly string $description,
        public readonly \DateTimeImmutable $postedAt,
    ) {}

    public function toPayload(): array
    {
        return [
            'journal_id' => $this->journalId,
            'company_id' => $this->companyId,
            'total_debit' => $this->totalDebit,
            'total_credit' => $this->totalCredit,
            'currency' => $this->currency,
            'description' => $this->description,
            'posted_at' => $this->postedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
