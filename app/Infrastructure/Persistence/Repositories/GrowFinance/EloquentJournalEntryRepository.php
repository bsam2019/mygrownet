<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\JournalEntry;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceJournalEntryModel;
use DateTimeImmutable;

class EloquentJournalEntryRepository implements JournalEntryRepositoryInterface
{
    public function findById(int $id): ?JournalEntry
    {
        $model = GrowFinanceJournalEntryModel::find($id);
        return $model ? JournalEntry::reconstitute($model->toArray()) : null;
    }

    public function findByJournalNumber(int $businessId, string $journalNumber): ?JournalEntry
    {
        $model = GrowFinanceJournalEntryModel::forBusiness($businessId)
            ->where('journal_number', $journalNumber)
            ->first();
        return $model ? JournalEntry::reconstitute($model->toArray()) : null;
    }

    public function save(JournalEntry $entity): JournalEntry
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceJournalEntryModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceJournalEntryModel::create($data);
        return JournalEntry::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceJournalEntryModel::forBusiness($businessId)
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($m) => JournalEntry::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByStatus(int $businessId, string $status): array
    {
        return GrowFinanceJournalEntryModel::forBusiness($businessId)
            ->withStatus($status)
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($m) => JournalEntry::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByDateRange(int $businessId, DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        return GrowFinanceJournalEntryModel::forBusiness($businessId)
            ->inDateRange($start->format('Y-m-d'), $end->format('Y-m-d'))
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($m) => JournalEntry::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByAccount(int $businessId, int $accountId): array
    {
        return GrowFinanceJournalEntryModel::forBusiness($businessId)
            ->whereHas('lines', fn($q) => $q->where('account_id', $accountId))
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($m) => JournalEntry::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByEventId(string $sourceEventId): ?JournalEntry
    {
        $model = GrowFinanceJournalEntryModel::where('source_event_id', $sourceEventId)->first();
        return $model ? JournalEntry::reconstitute($model->toArray()) : null;
    }

    public function findReversalsOf(int $journalEntryId): array
    {
        return GrowFinanceJournalEntryModel::where('reversal_of_id', $journalEntryId)
            ->get()
            ->map(fn($m) => JournalEntry::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByPeriod(int $businessId, int $periodId): array
    {
        return GrowFinanceJournalEntryModel::forBusiness($businessId)
            ->where('period_id', $periodId)
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($m) => JournalEntry::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByReference(int $businessId, string $reference): ?JournalEntry
    {
        $model = GrowFinanceJournalEntryModel::forBusiness($businessId)
            ->where('reference', $reference)
            ->first();
        return $model ? JournalEntry::reconstitute($model->toArray()) : null;
    }
}
