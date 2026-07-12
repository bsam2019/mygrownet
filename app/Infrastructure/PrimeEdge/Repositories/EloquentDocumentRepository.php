<?php

namespace App\Infrastructure\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\Document;
use App\Domain\PrimeEdge\Repositories\DocumentRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\DocumentId;
use App\Domain\PrimeEdge\ValueObjects\DocumentType;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Infrastructure\PrimeEdge\Persistence\DocumentModel;
use DateTimeImmutable;

class EloquentDocumentRepository implements DocumentRepositoryInterface
{
    public function save(Document $document): void
    {
        DocumentModel::updateOrCreate(
            ['id' => $document->id()->toString()],
            [
                'client_id' => $document->clientId()->toString(),
                'name' => $document->name(),
                'type' => $document->type()->value,
                'file_path' => $document->filePath(),
                'mime_type' => $document->mimeType(),
                'file_size' => $document->fileSize(),
                'engagement_id' => $document->engagementId(),
            ]
        );
    }

    public function findById(DocumentId $id): ?Document
    {
        $model = DocumentModel::find($id->toString());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByClientId(ClientId $clientId): array
    {
        return DocumentModel::where('client_id', $clientId->toString())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByEngagementId(string $engagementId): array
    {
        return DocumentModel::where('engagement_id', $engagementId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findAll(): array
    {
        return DocumentModel::orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function nextId(): DocumentId
    {
        return DocumentId::generate();
    }

    private function toDomainEntity(DocumentModel $model): Document
    {
        return Document::reconstitute(
            id: DocumentId::fromString($model->id),
            clientId: ClientId::fromString($model->client_id),
            name: $model->name,
            type: DocumentType::from($model->type),
            filePath: $model->file_path,
            mimeType: $model->mime_type,
            fileSize: $model->file_size,
            engagementId: $model->engagement_id,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
