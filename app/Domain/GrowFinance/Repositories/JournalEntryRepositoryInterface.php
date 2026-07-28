<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\JournalEntry;

interface JournalEntryRepositoryInterface
{
    public function findById(int $id): ?JournalEntry;

    public function findByJournalNumber(int $businessId, string $journalNumber): ?JournalEntry;

    public function save(JournalEntry $journalEntry): JournalEntry;

    public function findByBusiness(int $businessId): array;

    public function findByStatus(int $businessId, string $status): array;

    public function findByDateRange(int $businessId, \DateTimeImmutable $start, \DateTimeImmutable $end): array;

    public function findByAccount(int $businessId, int $accountId): array;

    public function findByEventId(string $sourceEventId): ?JournalEntry;

    public function findReversalsOf(int $journalEntryId): array;

    public function findByPeriod(int $businessId, int $periodId): array;

    public function findByReference(int $businessId, string $reference): ?JournalEntry;
}
