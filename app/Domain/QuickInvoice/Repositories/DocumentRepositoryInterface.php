<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Repositories;

use App\Domain\QuickInvoice\Entities\Document;
use App\Domain\QuickInvoice\ValueObjects\DocumentId;

interface DocumentRepositoryInterface
{
    public function findById(DocumentId $id): ?Document;
    
    public function findBySessionId(string $sessionId): array;
    
    public function findByUserId(int $userId): array;
    
    public function save(Document $document): Document;
    
    public function delete(DocumentId $id): bool;
    
    public function exists(DocumentId $id): bool;
    
    public function canAccess(DocumentId $id, ?int $userId, ?string $sessionId): bool;
}
