<?php

namespace App\Application\BizDocs\UseCases;

use App\Application\BizDocs\DTOs\CreateDocumentDTO;
use App\Application\BizDocs\UseCases\Document\CreateDocumentUseCase;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentRepositoryInterface;

class DuplicateDocumentUseCase
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documentRepository,
        private readonly CreateDocumentUseCase $createDocumentUseCase
    ) {
    }

    public function execute(int $documentId): array
    {
        // Load the source document
        $sourceDocument = $this->documentRepository->findById($documentId);

        if (!$sourceDocument) {
            throw new \RuntimeException('Document not found');
        }

        // Prepare items from source document
        $items = array_map(function ($item) {
            return [
                'description' => $item->description(),
                'quantity' => $item->quantity(),
                'unit_price' => $item->unitPrice()->amount() / 100,
                'tax_rate' => $item->taxRate(),
                'discount_amount' => $item->discountAmount()->amount() / 100,
            ];
        }, $sourceDocument->items());

        // Create new document DTO with source data
        $dto = CreateDocumentDTO::fromArray([
            'business_id' => $sourceDocument->businessId(),
            'customer_id' => $sourceDocument->customerId(),
            'document_type' => $sourceDocument->type()->value(),
            'issue_date' => date('Y-m-d'), // Use today's date
            'due_date' => null, // Clear due date
            'validity_date' => null, // Clear validity date
            'notes' => $sourceDocument->notes(),
            'terms' => $sourceDocument->terms(),
            'payment_instructions' => $sourceDocument->paymentInstructions(),
            'currency' => $sourceDocument->currency(),
            'items' => $items,
        ]);

        // Create the duplicate document
        $newDocument = $this->createDocumentUseCase->execute($dto);

        return [
            'original' => $sourceDocument->toArray(),
            'duplicate' => $newDocument->toArray(),
        ];
    }
}
