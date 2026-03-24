<?php

namespace App\Application\BizDocs\UseCases;

use App\Application\BizDocs\DTOs\RecordPaymentDTO;
use App\Domain\BizDocs\DocumentManagement\Entities\DocumentPayment;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\Money;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\PaymentMethod;
use DateTimeImmutable;

class RecordPaymentUseCase
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documentRepository,
        private readonly GenerateReceiptUseCase $generateReceiptUseCase
    ) {
    }

    public function execute(RecordPaymentDTO $dto): array
    {
        // Load the document
        $document = $this->documentRepository->findById($dto->documentId);

        if (!$document) {
            throw new \DomainException('Document not found');
        }

        // Create payment entity
        $payment = DocumentPayment::create(
            documentId: $document->id(),
            paymentDate: new DateTimeImmutable($dto->paymentDate),
            amount: Money::fromAmount((int)($dto->amount * 100), $document->currency()),
            paymentMethod: PaymentMethod::fromString($dto->paymentMethod),
            referenceNumber: $dto->referenceNumber,
            notes: $dto->notes
        );

        // Record payment on document (updates status automatically)
        $document->recordPayment($payment);

        // Save document with payment
        $this->documentRepository->save($document);

        $result = [
            'success' => true,
            'payment_id' => null, // Will be set after persistence
            'new_status' => $document->status()->value(),
            'total_paid' => $document->calculateTotalPaid()->amount() / 100,
            'remaining_balance' => $document->calculateRemainingBalance()->amount() / 100,
            'receipt_id' => null,
            'receipt_generated' => false,
        ];

        // Auto-generate receipt if requested (don't fail payment if receipt fails)
        if ($dto->generateReceipt) {
            try {
                $receipt = $this->generateReceiptUseCase->execute(
                    $document,
                    $payment
                );
                
                // Receipt ID should be available after save
                if ($receipt->id()) {
                    // Link the receipt to the payment
                    $payment->setReceiptId($receipt->id());
                    
                    // Save document again to persist the receipt ID
                    $this->documentRepository->save($document);
                    
                    $result['receipt_id'] = $receipt->id();
                    $result['receipt_generated'] = true;
                } else {
                    \Log::warning('Receipt was generated but has no ID', [
                        'document_id' => $document->id(),
                    ]);
                }
            } catch (\Exception $e) {
                // Log the error but don't fail the payment
                \Log::warning('Receipt generation failed after payment', [
                    'document_id' => $document->id(),
                    'error' => $e->getMessage(),
                ]);
                $result['receipt_error'] = $e->getMessage();
            }
        }

        return $result;
    }
}
