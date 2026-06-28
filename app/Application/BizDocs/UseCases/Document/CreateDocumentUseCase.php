<?php

namespace App\Application\BizDocs\UseCases\Document;

use App\Application\BizDocs\DTOs\CreateDocumentDTO;
use App\Application\BizDocs\DTOs\DocumentItemDTO;
use App\Domain\BizDocs\DocumentManagement\Entities\Document;
use App\Domain\BizDocs\DocumentManagement\Entities\DocumentItem;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\Services\DocumentNumberingService;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentType;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\Money;

class CreateDocumentUseCase
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documentRepository,
        private readonly DocumentNumberingService $numberingService
    ) {
    }

    public function execute(CreateDocumentDTO $dto): Document
    {
        $documentType = DocumentType::fromString($dto->documentType);
        
        // Generate document number
        $documentNumber = $this->numberingService->generateNextNumber(
            $dto->businessId,
            $documentType
        );

        // Create document
        $document = Document::create(
            businessId: $dto->businessId,
            customerId: $dto->customerId,
            type: $documentType,
            number: $documentNumber,
            issueDate: new \DateTimeImmutable($dto->issueDate),
            currency: $dto->currency,
            templateId: $dto->templateId,
            dueDate: $dto->dueDate ? new \DateTimeImmutable($dto->dueDate) : null,
            validityDate: $dto->validityDate ? new \DateTimeImmutable($dto->validityDate) : null,
            notes: $dto->notes,
            terms: $dto->terms,
            paymentInstructions: $dto->paymentInstructions,
            discountType: $dto->discountType,
            discountValue: $dto->discountValue,
            collectTax: $dto->collectTax
        );

        // Add items
        foreach ($dto->items as $itemData) {
            $itemDTO = is_array($itemData) ? DocumentItemDTO::fromArray($itemData) : $itemData;
            
            $item = DocumentItem::create(
                description: $itemDTO->description,
                quantity: $itemDTO->quantity,
                unitPrice: Money::fromAmount($itemDTO->unitPrice, $dto->currency),
                taxRate: $itemDTO->taxRate,
                discountAmount: Money::fromAmount($itemDTO->discountAmount, $dto->currency),
                sortOrder: $itemDTO->sortOrder,
                dimensions: $itemDTO->dimensions ?? null,
                dimensionsValue: $itemDTO->dimensionsValue ?? 1.0
            );

            $document->addItem($item);
        }

        // Save document
        return $this->documentRepository->save($document);
    }
}
