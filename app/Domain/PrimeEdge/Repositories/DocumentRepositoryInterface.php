<?php

namespace App\Domain\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\Document;
use App\Domain\PrimeEdge\ValueObjects\DocumentId;
use App\Domain\PrimeEdge\ValueObjects\ClientId;

interface DocumentRepositoryInterface
{
    public function save(Document $document): void;
    public function findById(DocumentId $id): ?Document;
    public function findByClientId(ClientId $clientId): array;
    public function findByEngagementId(string $engagementId): array;
    public function findAll(): array;
    public function nextId(): DocumentId;
}
