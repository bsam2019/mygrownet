<?php

namespace App\Application\BizDocs\UseCases;

use App\Application\BizDocs\DTOs\ConvertQuotationDTO;
use App\Application\BizDocs\DTOs\CreateDocumentDTO;
use App\Application\BizDocs\UseCases\Document\CreateDocumentUseCase;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentStatus;

class ConvertQuotationToInvoiceUseCase
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documentRepository,
        private readonly CreateDocumentUseCase $createDocumentUseCase
    ) {
    }

    public function execute(ConvertQuotationDTO $dto): array
    {
        // Load the quotation
        $quotation = $this->documentRepository->findById($dto->quotationId);

        if (!$quotation) {
            throw new \RuntimeException('Quotation not found');
        }

        if (!$quotation->type()->isQuotation()) {
            throw new \DomainException('Document is not a quotation');
        }

        // Check if quotation is in valid state for conversion
        $validStatuses = ['sent', 'accepted'];
        if (!in_array($quotation->status()->value(), $validStatuses)) {
            throw new \DomainException('Quotation must be sent or accepted to convert to invoice');
        }

        // Prepare invoice data from quotation
        $items = array_map(function ($item) {
            return [
                'description' => $item->description(),
                'quantity' => $item->quantity(),
                'unit_price' => $item->unitPrice()->amount() / 100,
                'tax_rate' => $item->taxRate(),
                'discount_amount' => $item->discountAmount()->amount() / 100,
            ];
        }, $quotation->items());

        // Create invoice DTO
        $invoiceDTO = CreateDocumentDTO::fromArray([
            'business_id' => $quotation->businessId(),
            'customer_id' => $quotation->customerId(),
            'document_type' => 'invoice',
            'issue_date' => $dto->issueDate ?? date('Y-m-d'),
            'due_date' => $dto->dueDate,
            'notes' => $dto->notes ?? $quotation->notes(),
            'terms' => $quotation->terms(),
            'payment_instructions' => $dto->paymentInstructions ?? $quotation->paymentInstructions(),
            'currency' => $quotation->currency(),
            'items' => $items,
        ]);

        // Create the invoice
        $invoice = $this->createDocumentUseCase->execute($invoiceDTO);

        // Update quotation status to 'converted'
        $quotation->changeStatus(DocumentStatus::fromString('accepted'));
        $this->documentRepository->save($quotation);

        return [
            'invoice' => $invoice->toArray(),
            'quotation' => $quotation->toArray(),
        ];
    }
}
