<?php

namespace App\Application\BizDocs\Services;

use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentStatusHistoryRepositoryInterface;

class DocumentStatusHistoryService
{
    public function __construct(
        private readonly DocumentStatusHistoryRepositoryInterface $statusHistoryRepository
    ) {
    }

    public function recordStatusChange(
        int $documentId,
        ?string $fromStatus,
        string $toStatus,
        ?string $notes = null,
        ?int $changedBy = null
    ): void {
        $this->statusHistoryRepository->record($documentId, $fromStatus, $toStatus, $notes, $changedBy);
    }

    public function getHistory(int $documentId): array
    {
        return $this->statusHistoryRepository->getByDocument($documentId);
    }
}