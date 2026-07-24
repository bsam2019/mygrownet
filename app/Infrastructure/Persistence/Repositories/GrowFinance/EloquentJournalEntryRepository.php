<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\JournalEntry;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceJournalEntryModel;

class EloquentJournalEntryRepository implements JournalEntryRepositoryInterface
{
    public function findById(int $id): ?JournalEntry
    {
        $model = GrowFinanceJournalEntryModel::find($id);
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
        return GrowFinanceJournalEntryModel::forBusiness($businessId)->get()->map(fn($m) => JournalEntry::reconstitute($m->toArray()))->toArray();
    }

    public function findPosted(int $businessId): array
    {
        return GrowFinanceJournalEntryModel::forBusiness($businessId)->posted()->get()->map(fn($m) => JournalEntry::reconstitute($m->toArray()))->toArray();
    }

}
