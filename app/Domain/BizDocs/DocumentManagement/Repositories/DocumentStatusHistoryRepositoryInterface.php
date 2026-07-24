<?php

namespace App\Domain\BizDocs\DocumentManagement\Repositories;

interface DocumentStatusHistoryRepositoryInterface
{
    public function record(int $documentId, ?string $fromStatus, string $toStatus, ?string $notes, ?int $changedBy): void;

    public function getByDocument(int $documentId): array;
}