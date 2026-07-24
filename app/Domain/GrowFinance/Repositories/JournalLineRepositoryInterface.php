<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\JournalLine;

interface JournalLineRepositoryInterface
{
    public function findById(int $id): ?JournalLine;

    public function save(JournalLine $journalLine): JournalLine;

    public function findByJournalEntry(int $journalEntryId): array;

    public function findByAccount(int $accountId): array;
}
