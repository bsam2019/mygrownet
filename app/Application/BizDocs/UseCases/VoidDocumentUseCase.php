<?php

namespace App\Application\BizDocs\UseCases;

use App\Application\BizDocs\DTOs\CancelDocumentDTO;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentRepositoryInterface;

class VoidDocumentUseCase
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documentRepository
    ) {
    }

    public function execute(CancelDocumentDTO $dto): array
    {
        $document = $this->documentRepository->findById($dto->documentId);

        if (!$document) {
            throw new \DomainException('Document not found');
        }

        if ($document->isCancelled()) {
            throw new \DomainException('Document is already cancelled or voided');
        }

        // Void with reason (only for receipts)
        $document->voidWithReason($dto->reason);

        // Save document
        $this->documentRepository->save($document);

        return [
            'success' => true,
            'document_id' => $document->id(),
            'status' => $document->status()->value(),
            'voided_at' => $document->cancelledAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
