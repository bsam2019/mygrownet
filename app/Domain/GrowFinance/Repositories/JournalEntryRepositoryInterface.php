<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\JournalEntry;

interface JournalEntryRepositoryInterface
{
    public function findById(int $id): ?JournalEntry;

    public function save(JournalEntry $journalEntry): JournalEntry;

    public function findByBusiness(int $businessId): array;

    public function findPosted(int $businessId): array;
}
