<?php

namespace App\Application\BizDocs\UseCases;

use App\Domain\BizDocs\DocumentManagement\Entities\Document;
use App\Domain\BizDocs\DocumentManagement\Entities\DocumentItem;
use App\Domain\BizDocs\DocumentManagement\Entities\DocumentPayment;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\Services\DocumentNumberingService;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentType;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\Money;
use DateTimeImmutable;

class GenerateReceiptUseCase
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documentRepository,
        private readonly DocumentNumberingService $numberingService
    ) {
    }

    public function execute(Document $invoice, DocumentPayment $payment): Document
    {
        // Generate receipt number
        $receiptNumber = $this->numberingService->generateNextNumber(
            $invoice->businessId(),
            DocumentType::receipt()
        );

        // Create receipt document
        $receipt = Document::create(
            businessId: $invoice->businessId(),
            customerId: $invoice->customerId(),
            type: DocumentType::receipt(),
            number: $receiptNumber,
            issueDate: new DateTimeImmutable(),
            currency: $invoice->currency(),
            templateId: null,
            notes: "Payment received for Invoice {$invoice->number()->value()}\n" .
                   "Payment Method: {$payment->paymentMethod()->label()}\n" .
                   ($payment->referenceNumber() ? "Reference: {$payment->referenceNumber()}" : '')
        );

        // Add single line item for the payment amount
        $receiptItem = DocumentItem::create(
            description: "Payment for Invoice {$invoice->number()->value()}",
            quantity: 1,
            unitPrice: $payment->amount(),
            taxRate: 0,
            discountAmount: Money::fromAmount(0, $invoice->currency())
        );

        $receipt->addItem($receiptItem);

        // Issue the receipt (changes status from draft to issued)
        $receipt->issue();

        // Save receipt
        $this->documentRepository->save($receipt);

        return $receipt;
    }
}
