<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\JournalLine;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceJournalLineModel;

class EloquentJournalLineRepository implements JournalLineRepositoryInterface
{
    public function findById(int $id): ?JournalLine
    {
        $model = GrowFinanceJournalLineModel::find($id);
        return $model ? JournalLine::reconstitute($model->toArray()) : null;
    }

    public function save(JournalLine $entity): JournalLine
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceJournalLineModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceJournalLineModel::create($data);
        return JournalLine::reconstitute($model->toArray());
    }

    public function findByJournalEntry(int $journalEntryId): array
    {
        return GrowFinanceJournalLineModel::where('journal_entry_id', $journalEntryId)->get()->map(fn($m) => JournalLine::reconstitute($m->toArray()))->toArray();
    }

    public function findByAccount(int $accountId): array
    {
        return GrowFinanceJournalLineModel::where('account_id', $accountId)->get()->map(fn($m) => JournalLine::reconstitute($m->toArray()))->toArray();
    }

}
