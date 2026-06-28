<?php

namespace App\Application\BizDocs\UseCases\Document;

use App\Application\BizDocs\Services\DocumentStatusHistoryService;
use App\Domain\BizDocs\DocumentManagement\Entities\Document;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentRepositoryInterface;

class FinalizeDocumentUseCase
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documentRepository,
        private readonly DocumentStatusHistoryService $statusHistoryService
    ) {
    }

    public function execute(int $documentId, ?int $userId = null): Document
    {
        $document = $this->documentRepository->findById($documentId);

        if (!$document) {
            throw new \DomainException('Document not found');
        }

        $oldStatus = $document->status()->value();
        $document->finalize();
        $newStatus = $document->status()->value();

        $savedDocument = $this->documentRepository->save($document);

        // Record status change
        $this->statusHistoryService->recordStatusChange(
            $savedDocument->id(),
            $oldStatus,
            $newStatus,
            'Document finalized',
            $userId
        );

        return $savedDocument;
    }
}
