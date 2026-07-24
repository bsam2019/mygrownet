<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\VentureBuilder;

use App\Domain\VentureBuilder\Entities\Document;
use App\Domain\VentureBuilder\Repositories\DocumentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureDocumentModel;

class EloquentDocumentRepository implements DocumentRepositoryInterface
{
    public function findById(int $id): ?Document
    {
        $model = VentureDocumentModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByVenture(int $ventureId, ?string $visibility = null): array
    {
        $query = VentureDocumentModel::where('venture_id', $ventureId);

        if ($visibility !== null) {
            $query->where('visibility', $visibility);
        }

        return $query->orderByDesc('created_at')
            ->get()
            ->map(fn(VentureDocumentModel $m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function save(Document $document): Document
    {
        $data = $document->toArray();
        $id = $data['id'] ?? null;
        unset($data['id']);

        if ($id) {
            VentureDocumentModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = VentureDocumentModel::create($data);
        return $this->toDomainEntity($model);
    }

    public function delete(int $id): void
    {
        VentureDocumentModel::destroy($id);
    }

    private function toDomainEntity(VentureDocumentModel $model): Document
    {
        return Document::reconstitute($model->toArray());
    }
}
