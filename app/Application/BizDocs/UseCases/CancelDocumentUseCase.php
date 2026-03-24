<?php

namespace App\Application\BizDocs\UseCases;

use App\Application\BizDocs\DTOs\CancelDocumentDTO;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentRepositoryInterface;

class CancelDocumentUseCase
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

        // Cancel with reason
        $document->cancelWithReason($dto->reason);

        // Save document
        $this->documentRepository->save($document);

        return [
            'success' => true,
            'document_id' => $document->id(),
            'status' => $document->status()->value(),
            'cancelled_at' => $document->cancelledAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
